<?php
declare(strict_types=1);

namespace App\Core;

use Exception;

/**
 * Buyer Controller
 */
class BuyerController extends Controller
{
    private Buyer $buyerModel;

    public function __construct(Database $db)
    {
        parent::__construct($db);
        $this->buyerModel = new Buyer($this->db->getConnection());
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '1';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $buyers = $this->buyerModel->getAll($search, $status, $limit, $offset);
        $total = $this->buyerModel->count($search, $status);

        $this->render('buyers/index', [
            'buyers' => $buyers,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'status' => $status
        ]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost(null);
        } else {
            $this->render('buyers/form', ['buyer' => null]);
        }
    }

    public function edit(int $id): void
    {
        $buyer = $this->buyerModel->findById($id);
        if (!$buyer) {
            $this->flash('error', 'Buyer not found');
            $this->redirect('/buyers');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost($id);
        } else {
            $this->render('buyers/form', ['buyer' => $buyer]);
        }
    }

    public function delete(int $id): void
    {
        if ($this->buyerModel->delete($id)) {
            $this->flash('success', 'Buyer deleted successfully');
        } else {
            $this->flash('error', 'Failed to delete buyer');
        }
        $this->redirect('/buyers');
    }

    private function handlePost(?int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token');
            $this->redirect($_SERVER['REQUEST_URI']);
        }

        try {
            $data = $_POST;
            
            // Format contacts from POST
            $data['contacts'] = [];
            if (isset($_POST['contact_name'])) {
                foreach ($_POST['contact_name'] as $idx => $name) {
                    if (!empty($name)) {
                        $data['contacts'][] = [
                            'name' => $name,
                            'designation' => $_POST['contact_designation'][$idx] ?? null,
                            'email' => $_POST['contact_email'][$idx] ?? null,
                            'phone' => $_POST['contact_phone'][$idx] ?? null
                        ];
                    }
                }
            }

            // Format addresses from POST
            $data['addresses'] = [];
            if (isset($_POST['addr_type'])) {
                foreach ($_POST['addr_type'] as $idx => $type) {
                    $data['addresses'][] = [
                        'type' => $type,
                        'line1' => $_POST['addr_line1'][$idx] ?? null,
                        'city' => $_POST['addr_city'][$idx] ?? null,
                        'country' => $_POST['addr_country'][$idx] ?? null,
                        'zip' => $_POST['addr_zip'][$idx] ?? null
                    ];
                }
            }

            if ($id) {
                $this->buyerModel->update($id, $data);
                $this->flash('success', 'Buyer updated successfully');
            } else {
                $this->buyerModel->create($data);
                $this->flash('success', 'Buyer created successfully');
            }
            $this->redirect('/buyers');
        } catch (Exception $e) {
            $this->flash('error', 'Error: ' . $e->getMessage());
            $this->redirect($_SERVER['REQUEST_URI']);
        }
    }
}
