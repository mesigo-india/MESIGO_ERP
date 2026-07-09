<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

class DashboardController extends Controller
{
    private PDO $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $this->requireLogin();

        $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'widgets' => $this->widgets(),
            'recentQuotations' => $this->recentDocuments('quotation', 5),
            'recentBuyers' => $this->recentBuyers(),
            'pendingFollowUps' => $this->pendingFollowUps(),
            'recentDocuments' => $this->recentDocuments('', 8),
            'recentExportOrders' => $this->recentExportOrders(),
            'statusSummary' => $this->documentStatusSummary(),
            'monthlyQuotations' => $this->monthlyCounts('quotation'),
            'monthlyExportDocuments' => $this->monthlyCounts('', true),
            'latestActivities' => $this->latestActivities(),
        ]);
    }

    private function widgets(): array
    {
        return [
            'total_buyers' => $this->scalar("SELECT COUNT(*) FROM buyers WHERE deleted_at IS NULL"),
            'active_buyers' => $this->scalar("SELECT COUNT(*) FROM buyers WHERE status = 1 AND deleted_at IS NULL"),
            'products' => $this->scalar("SELECT COUNT(*) FROM products WHERE deleted_at IS NULL"),
            'quotations' => $this->countDocuments('quotation'),
            'proforma_invoices' => $this->countDocuments('proforma_invoice'),
            'commercial_invoices' => $this->countDocuments('commercial_invoice'),
            'packing_lists' => $this->countDocuments('packing_list'),
            'shipping_bills' => $this->countDocuments('shipping_bill'),
            'bills_of_lading' => $this->countDocuments('bill_of_lading'),
            'pending_documents' => $this->scalar("SELECT COUNT(*) FROM document_headers WHERE status IN (0, 1, 3) AND deleted_at IS NULL"),
            'this_month_exports' => $this->scalar("SELECT COUNT(*) FROM document_headers WHERE deleted_at IS NULL AND document_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"),
            'recent_activities' => $this->scalar("SELECT COUNT(*) FROM document_status_history WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"),
        ];
    }

    private function countDocuments(string $code): int
    {
        return $this->scalar(
            "SELECT COUNT(*)
             FROM document_headers dh
             INNER JOIN document_types dt ON dt.id = dh.document_type_id
             WHERE dt.code = :code AND dh.deleted_at IS NULL",
            ['code' => $code]
        );
    }

    private function recentDocuments(string $code = '', int $limit = 8): array
    {
        $where = ['dh.deleted_at IS NULL'];
        $params = [];

        if ($code !== '') {
            $where[] = 'dt.code = :code';
            $params['code'] = $code;
        }

        return $this->rows(
            "SELECT dh.id, dh.document_number, dh.document_date, dh.status, dt.name AS document_type, dt.code AS document_type_code, b.company_name AS buyer_name
             FROM document_headers dh
             INNER JOIN document_types dt ON dt.id = dh.document_type_id
             LEFT JOIN buyers b ON b.id = dh.buyer_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY dh.document_date DESC, dh.id DESC
             LIMIT " . (int) $limit,
            $params
        );
    }

    private function recentBuyers(): array
    {
        return $this->rows(
            "SELECT id, buyer_code, company_name, contact_person, email, status, created_at
             FROM buyers
             WHERE deleted_at IS NULL
             ORDER BY created_at DESC, id DESC
             LIMIT 5"
        );
    }

    private function pendingFollowUps(): array
    {
        return $this->rows(
            "SELECT dh.id, dh.document_number, dh.document_date, dh.status, dt.name AS document_type, b.company_name AS buyer_name
             FROM document_headers dh
             INNER JOIN document_types dt ON dt.id = dh.document_type_id
             LEFT JOIN buyers b ON b.id = dh.buyer_id
             WHERE dh.status IN (0, 1, 3) AND dh.deleted_at IS NULL
             ORDER BY dh.updated_at DESC
             LIMIT 6"
        );
    }

    private function recentExportOrders(): array
    {
        return $this->rows(
            "SELECT dh.id, dh.document_number, dh.document_date, dh.status, dt.name AS document_type, b.company_name AS buyer_name
             FROM document_headers dh
             INNER JOIN document_types dt ON dt.id = dh.document_type_id
             LEFT JOIN buyers b ON b.id = dh.buyer_id
             WHERE dt.code IN ('commercial_invoice', 'packing_list', 'shipping_bill', 'bill_of_lading', 'certificate_of_origin')
             AND dh.deleted_at IS NULL
             ORDER BY dh.document_date DESC, dh.id DESC
             LIMIT 6"
        );
    }

    private function documentStatusSummary(): array
    {
        return $this->rows(
            "SELECT status, COUNT(*) AS total
             FROM document_headers
             WHERE deleted_at IS NULL
             GROUP BY status
             ORDER BY status ASC"
        );
    }

    private function monthlyCounts(string $code = '', bool $exportOnly = false): array
    {
        $where = ["dh.document_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)", 'dh.deleted_at IS NULL'];
        $params = [];

        if ($code !== '') {
            $where[] = 'dt.code = :code';
            $params['code'] = $code;
        }

        if ($exportOnly) {
            $where[] = "dt.code IN ('proforma_invoice', 'commercial_invoice', 'packing_list', 'shipping_bill', 'bill_of_lading', 'certificate_of_origin')";
        }

        return $this->rows(
            "SELECT DATE_FORMAT(dh.document_date, '%Y-%m') AS month, COUNT(*) AS total
             FROM document_headers dh
             INNER JOIN document_types dt ON dt.id = dh.document_type_id
             WHERE " . implode(' AND ', $where) . "
             GROUP BY DATE_FORMAT(dh.document_date, '%Y-%m')
             ORDER BY month ASC",
            $params
        );
    }

    private function latestActivities(): array
    {
        return $this->rows(
            "SELECT dsh.created_at, dsh.new_status, dsh.remarks, dh.document_number, dt.name AS document_type
             FROM document_status_history dsh
             INNER JOIN document_headers dh ON dh.id = dsh.document_header_id
             INNER JOIN document_types dt ON dt.id = dh.document_type_id
             ORDER BY dsh.created_at DESC
             LIMIT 8"
        );
    }

    private function scalar(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (Throwable) {
            return 0;
        }
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