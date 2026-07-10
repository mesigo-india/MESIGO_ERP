<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

/**
 * Master Costing Engine
 * Reusable costing & profitability calculation service for MESIGO ERP
 */
class CostingEngine
{
    private PDO $db;
    private CurrencyConversionEngine $currencyEngine;

    public function __construct(PDO $db, CurrencyConversionEngine $currencyEngine)
    {
        $this->db = $db;
        $this->currencyEngine = $currencyEngine;
    }

    /**
     * Compute total profitability margins for a given document (Quotation, PI, CI, etc.)
     * Returns structured profitability metrics in the base bookkeeping currency (INR)
     */
    public function calculateProfitability(int $documentId): array
    {
        // 1. Fetch document header details
        $stmt = $this->db->prepare("SELECT * FROM document_headers WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $documentId]);
        $header = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$header) {
            return [];
        }

        $docCurrencyId = (int) $header['currency_id'];
        $docLockedRate = (float) $header['exchange_rate'];
        $baseCurrId = $this->currencyEngine->getBaseCurrencyId();

        // 2. Calculate Total Invoice Sales Revenue (Base Currency)
        $stmt = $this->db->prepare("SELECT SUM(amount) FROM document_items WHERE document_header_id = :id");
        $stmt->execute(['id' => $documentId]);
        $revenueTransacted = (float) ($stmt->fetchColumn() ?: 0.0);
        
        // Convert sales revenue to base currency using the header's locked exchange rate
        $revenueBase = $this->currencyEngine->convert($revenueTransacted, $docCurrencyId, $baseCurrId, null, $docLockedRate);

        // 3. Calculate Total Product Procurement Cost (Base Currency)
        $stmt = $this->db->prepare("
            SELECT di.quantity, p.purchase_price, p.default_currency, c.id as currency_id
            FROM document_items di
            JOIN products p ON di.product_id = p.id
            LEFT JOIN currencies c ON UPPER(c.code) = UPPER(p.default_currency)
            WHERE di.document_header_id = :id
        ");
        $stmt->execute(['id' => $documentId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $productCostBase = 0.0;
        foreach ($items as $item) {
            $qty = (float) $item['quantity'];
            $price = (float) ($item['purchase_price'] ?? 0.0);
            $itemCurrId = $item['currency_id'] !== null ? (int) $item['currency_id'] : $baseCurrId;

            // Convert item procurement price to base currency
            $priceBase = $this->currencyEngine->convert($price, $itemCurrId, $baseCurrId);
            $productCostBase += ($qty * $priceBase);
        }

        // 4. Calculate Total Seller-Paid Expenses (Base Currency)
        $stmt = $this->db->prepare("
            SELECT dc.*, cc.category
            FROM document_charges dc
            LEFT JOIN cost_components cc ON dc.cost_component_id = cc.id
            WHERE dc.document_header_id = :id
        ");
        $stmt->execute(['id' => $documentId]);
        $charges = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $expensesBase = 0.0;
        foreach ($charges as $charge) {
            $chargeAmt = (float) $charge['charge_amount'];
            $chargeCurrId = (int) $charge['currency_id'];
            $chargeRate = (float) $charge['exchange_rate'];

            // Convert charge to base currency (INR) using its locked rate
            $chargeBase = $this->currencyEngine->convert($chargeAmt, $chargeCurrId, $baseCurrId, null, $chargeRate);

            // Update the cached value in the database for reporting efficiency
            $updateStmt = $this->db->prepare("
                UPDATE document_charges 
                SET converted_amount_base = :base_amt 
                WHERE id = :id
            ");
            $updateStmt->execute(['base_amt' => $chargeBase, 'id' => $charge['id']]);

            // Retrieve if this component is seller paid (Buyer paid is excluded from internal costs)
            // E.g. Under FOB, international freight is Buyer Paid and does not subtract from seller's profit
            $isSellerPaid = $this->isSellerPaidExpense($charge, (int) ($header['incoterm_id'] ?? 0));
            if ($isSellerPaid) {
                $expensesBase += $chargeBase;
            }
        }

        // 5. Aggregate Profitability Margins
        $grossProfitBase = $revenueBase - $productCostBase - $expensesBase;
        $profitMarginPercent = $revenueBase > 0.0 ? ($grossProfitBase / $revenueBase) * 100.0 : 0.0;

        return [
            'document_id' => $documentId,
            'sales_revenue_transacted' => $revenueTransacted,
            'sales_revenue_base' => $revenueBase,
            'product_cost_base' => $productCostBase,
            'expenses_base' => $expensesBase,
            'gross_profit_base' => $grossProfitBase,
            'profit_margin_percent' => $profitMarginPercent
        ];
    }

    /**
     * Apply a Cost Template to a document's charge ledger
     */
    public function applyTemplate(int $documentId, int $templateId): bool
    {
        $stmt = $this->db->prepare("SELECT * FROM cost_templates WHERE id = :id AND status = 1 LIMIT 1");
        $stmt->execute(['id' => $templateId]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$template) {
            return false;
        }

        // Clear existing charges for the document
        $stmt = $this->db->prepare("DELETE FROM document_charges WHERE document_header_id = :doc_id");
        $stmt->execute(['doc_id' => $documentId]);

        // Load items from template
        $stmt = $this->db->prepare("
            SELECT cost_component_id, amount, currency_id 
            FROM cost_template_items 
            WHERE cost_template_id = :template_id
        ");
        $stmt->execute(['template_id' => $templateId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retrieve default locked rate for each item
        foreach ($items as $item) {
            $compStmt = $this->db->prepare("SELECT name FROM cost_components WHERE id = :id LIMIT 1");
            $compStmt->execute(['id' => $item['cost_component_id']]);
            $compName = (string) ($compStmt->fetchColumn() ?: 'Charge');

            $rate = $this->currencyEngine->getRate((int) $item['currency_id']);

            $insertStmt = $this->db->prepare("
                INSERT INTO document_charges 
                (document_header_id, cost_component_id, charge_name, charge_amount, currency_id, exchange_rate, sort_order)
                VALUES 
                (:doc_id, :comp_id, :name, :amount, :curr_id, :rate, 0)
            ");
            $insertStmt->execute([
                'doc_id' => $documentId,
                'comp_id' => $item['cost_component_id'],
                'name' => $compName,
                'amount' => $item['amount'],
                'curr_id' => $item['currency_id'],
                'rate' => $rate
            ]);
        }

        // Trigger recalculation on the document header to update cached summaries
        $this->calculateProfitability($documentId);

        return true;
    }

    /**
     * Check if a charge component is seller-paid depending on Incoterm rules
     */
    private function isSellerPaidExpense(array $charge, int $incotermId): bool
    {
        if ($incotermId === 0) {
            return true;
        }

        // Fetch incoterm code
        $stmt = $this->db->prepare("SELECT UPPER(code) FROM incoterms WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $incotermId]);
        $code = (string) ($stmt->fetchColumn() ?: '');

        $category = strtoupper((string) ($charge['category'] ?? ''));

        // Under FOB (Free On Board), international transport (freight and marine insurance) is paid by the Buyer.
        if ($code === 'FOB') {
            if ($category === 'LOGISTICS_INTL' || strpos(strtoupper($charge['charge_name']), 'FREIGHT') !== false || strpos(strtoupper($charge['charge_name']), 'INSURANCE') !== false) {
                return false;
            }
        }

        // Under EXW (Ex Works), almost all logistics charges beyond origin warehouse loading are Buyer Paid.
        if ($code === 'EXW') {
            if ($category !== 'PROCUREMENT' && $category !== 'PACKING') {
                return false;
            }
        }

        return true;
    }
}
