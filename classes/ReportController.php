<?php
declare(strict_types=1);

namespace App\Core;

class ReportController extends Controller
{
    private ReportService $reports;

    public function __construct()
    {
        parent::__construct();
        require_once APP_ROOT . '/classes/ReportService.php';
        $this->reports = new ReportService(Database::getInstance());
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('reports.view');
        $filters = $this->reports->filters($_GET);
        $this->render('reports/index', [
            'title' => 'Reports & Analytics',
            'filters' => $filters,
            'buyers' => $this->reports->buyers(),
            'products' => $this->reports->products(),
            'currencies' => $this->reports->currencies(),
            'summary' => $this->reports->summary($filters),
            'profitability' => $this->reports->profitabilityReport($filters),
            'exportMode' => trim((string) ($_GET['export'] ?? '')),
        ]);
    }
}