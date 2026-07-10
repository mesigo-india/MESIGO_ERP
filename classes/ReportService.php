<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

class ReportService
{
    public function __construct(private PDO $db)
    {
    }

    public function filters(array $input): array
    {
        return [
            'date_from' => trim((string) ($input['date_from'] ?? '')),
            'date_to' => trim((string) ($input['date_to'] ?? '')),
            'buyer_id' => (int) ($input['buyer_id'] ?? 0),
            'country' => trim((string) ($input['country'] ?? '')),
            'product_id' => (int) ($input['product_id'] ?? 0),
            'status' => trim((string) ($input['status'] ?? '')),
            'currency_id' => (int) ($input['currency_id'] ?? 0),
        ];
    }

    public function buyers(): array
    {
        return $this->rows("SELECT id, company_name FROM buyers WHERE deleted_at IS NULL ORDER BY company_name ASC");
    }

    public function products(): array
    {
        return $this->rows("SELECT id, name FROM products WHERE deleted_at IS NULL ORDER BY name ASC");
    }

    public function currencies(): array
    {
        return $this->rows("SELECT id, code FROM currencies WHERE status != 0 ORDER BY code ASC");
    }

    public function sales(array $filters): array
    {
        return $this->documentReport(['commercial_invoice'], $filters);
    }

    public function quotations(array $filters): array
    {
        return $this->documentReport(['quotation'], $filters);
    }

    public function buyersReport(array $filters): array
    {
        $where = ['b.deleted_at IS NULL'];
        $params = [];
        if ($filters['buyer_id'] > 0) {
            $where[] = 'b.id = :buyer_id';
            $params['buyer_id'] = $filters['buyer_id'];
        }
        if ($filters['status'] !== '') {
            $where[] = 'b.status = :status';
            $params['status'] = (int) $filters['status'];
        }
        if ($filters['country'] !== '') {
            $where[] = 'b.address LIKE :country';
            $params['country'] = '%' . $filters['country'] . '%';
        }

        return $this->rows(
            "SELECT b.buyer_code AS reference_no, b.company_name AS title, b.email, b.phone, b.status, b.created_at AS report_date,
                    COUNT(dh.id) AS document_count, 0 AS amount
             FROM buyers b
             LEFT JOIN document_headers dh ON dh.buyer_id = b.id AND dh.deleted_at IS NULL
             WHERE " . implode(' AND ', $where) . "
             GROUP BY b.id
             ORDER BY b.company_name ASC",
            $params
        );
    }

    public function productsReport(array $filters): array
    {
        $where = ['p.deleted_at IS NULL'];
        $params = [];
        if ($filters['product_id'] > 0) {
            $where[] = 'p.id = :product_id';
            $params['product_id'] = $filters['product_id'];
        }
        if ($filters['status'] !== '') {
            $where[] = 'p.status = :status';
            $params['status'] = (int) $filters['status'];
        }

        return $this->rows(
            "SELECT p.product_code AS reference_no, p.name AS title, pc.name AS category, p.hsn_code, p.status, p.created_at AS report_date,
                    COUNT(di.id) AS document_count, COALESCE(SUM(di.quantity), 0) AS quantity
             FROM products p
             LEFT JOIN product_categories pc ON pc.id = p.category_id
             LEFT JOIN document_items di ON di.product_id = p.id
             WHERE " . implode(' AND ', $where) . "
             GROUP BY p.id
             ORDER BY p.name ASC",
            $params
        );
    }

    public function shipment(array $filters): array
    {
        return $this->documentReport(['shipping_bill', 'bill_of_lading'], $filters);
    }

    public function export(array $filters): array
    {
        return $this->documentReport(['quotation', 'proforma_invoice', 'commercial_invoice', 'packing_list', 'shipping_bill', 'bill_of_lading', 'certificate_of_origin'], $filters);
    }

    public function commercialInvoices(array $filters): array
    {
        return $this->documentReport(['commercial_invoice'], $filters);
    }

    public function packing(array $filters): array
    {
        return $this->documentReport(['packing_list'], $filters);
    }

    public function shippingBills(array $filters): array
    {
        return $this->documentReport(['shipping_bill'], $filters);
    }

    public function documentStatus(array $filters): array
    {
        return $this->documentReport([], $filters);
    }

    public function summary(array $filters): array
    {
        $reports = [
            'Sales Reports' => $this->sales($filters),
            'Quotation Reports' => $this->quotations($filters),
            'Buyer Reports' => $this->buyersReport($filters),
            'Product Reports' => $this->productsReport($filters),
            'Shipment Reports' => $this->shipment($filters),
            'Export Reports' => $this->export($filters),
            'Commercial Invoice Reports' => $this->commercialInvoices($filters),
            'Packing Reports' => $this->packing($filters),
            'Shipping Bill Reports' => $this->shippingBills($filters),
            'Document Status Reports' => $this->documentStatus($filters),
        ];

        $summary = [];
        foreach ($reports as $name => $rows) {
            $summary[$name] = ['count' => count($rows), 'rows' => $rows];
        }
        return $summary;
    }

    private function documentReport(array $codes, array $filters): array
    {
        $where = ['dh.deleted_at IS NULL'];
        $params = [];

        if ($codes !== []) {
            $placeholders = [];
            foreach ($codes as $index => $code) {
                $key = 'code' . $index;
                $placeholders[] = ':' . $key;
                $params[$key] = $code;
            }
            $where[] = 'dt.code IN (' . implode(', ', $placeholders) . ')';
        }
        if ($filters['date_from'] !== '') {
            $where[] = 'dh.document_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if ($filters['date_to'] !== '') {
            $where[] = 'dh.document_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }
        if ($filters['buyer_id'] > 0) {
            $where[] = 'dh.buyer_id = :buyer_id';
            $params['buyer_id'] = $filters['buyer_id'];
        }
        if ($filters['status'] !== '') {
            $where[] = 'dh.status = :status';
            $params['status'] = (int) $filters['status'];
        }
        if ($filters['currency_id'] > 0) {
            $where[] = 'dh.currency_id = :currency_id';
            $params['currency_id'] = $filters['currency_id'];
        }
        if ($filters['product_id'] > 0) {
            $where[] = 'EXISTS (SELECT 1 FROM document_items di WHERE di.document_header_id = dh.id AND di.product_id = :product_id)';
            $params['product_id'] = $filters['product_id'];
        }
        if ($filters['country'] !== '') {
            $where[] = 'b.address LIKE :country';
            $params['country'] = '%' . $filters['country'] . '%';
        }

        return $this->rows(
            "SELECT dh.id, dh.document_number AS reference_no, dh.document_date AS report_date, dt.name AS document_type,
                    b.company_name AS buyer_name, c.code AS currency_code, dh.status, dh.remarks,
                    COALESCE(SUM(di.net_amount), 0) AS amount,
                    COALESCE(SUM(di.quantity), 0) AS quantity
             FROM document_headers dh
             INNER JOIN document_types dt ON dt.id = dh.document_type_id
             LEFT JOIN buyers b ON b.id = dh.buyer_id
             LEFT JOIN currencies c ON c.id = dh.currency_id
             LEFT JOIN document_items di ON di.document_header_id = dh.id
             WHERE " . implode(' AND ', $where) . "
             GROUP BY dh.id
             ORDER BY dh.document_date DESC, dh.id DESC
             LIMIT 500",
            $params
        );
    }

    /**
     * Generate Enterprise Profitability Report with cost components and margins
     */
    public function profitabilityReport(array $filters): array
    {
        $where = ['dh.deleted_at IS NULL'];
        $params = [];

        if ($filters['date_from'] !== '') {
            $where[] = 'dh.document_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if ($filters['date_to'] !== '') {
            $where[] = 'dh.document_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }
        if ($filters['buyer_id'] > 0) {
            $where[] = 'dh.buyer_id = :buyer_id';
            $params['buyer_id'] = $filters['buyer_id'];
        }
        if ($filters['product_id'] > 0) {
            $where[] = 'EXISTS (SELECT 1 FROM document_items di2 WHERE di2.document_header_id = dh.id AND di2.product_id = :product_id)';
            $params['product_id'] = $filters['product_id'];
        }

        $sql = "
            SELECT 
                dh.id, 
                dh.document_number AS reference_no, 
                dh.document_date AS report_date, 
                dt.name AS document_type,
                b.company_name AS buyer_name, 
                comp.company_name AS company_entity,
                curr.code AS currency_code, 
                dh.exchange_rate,
                
                -- Invoice Sales base amount
                COALESCE(
                    (SELECT SUM(di.net_amount * dh.exchange_rate) 
                     FROM document_items di 
                     WHERE di.document_header_id = dh.id), 0
                ) AS gross_sales_base,
                
                -- Product production cost base amount
                COALESCE(
                    (SELECT SUM(di.quantity * COALESCE(p.unit_cost_base, 0.00)) 
                     FROM document_items di 
                     INNER JOIN products p ON p.id = di.product_id
                     WHERE di.document_header_id = dh.id), 0
                ) AS production_cost_base,
                
                -- Dynamic Charges base amount
                COALESCE(
                    (SELECT SUM(dc.converted_amount_base) 
                     FROM document_charges dc 
                     WHERE dc.document_header_id = dh.id), 0
                ) AS total_charges_base
                
            FROM document_headers dh
            INNER JOIN document_types dt ON dt.id = dh.document_type_id
            LEFT JOIN buyers b ON b.id = dh.buyer_id
            LEFT JOIN company comp ON comp.id = dh.company_id
            LEFT JOIN currencies curr ON curr.id = dh.currency_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY dh.document_date DESC, dh.id DESC
            LIMIT 500
        ";

        $rows = $this->rows($sql, $params);

        foreach ($rows as &$row) {
            $row['net_profit_base'] = (float) $row['gross_sales_base'] - (float) $row['production_cost_base'] - (float) $row['total_charges_base'];
            $row['margin_percent'] = (float) $row['gross_sales_base'] > 0 
                ? ((float) $row['net_profit_base'] / (float) $row['gross_sales_base']) * 100 
                : 0.0;
        }
        return $rows;
    }

    private function rows(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable) {
            return [];
        }
    }
}