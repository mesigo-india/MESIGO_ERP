<?php
declare(strict_types=1);

namespace App\Core;

use Exception;

/**
 * Controller for Print Assets & Company Branding Manager
 */
class PrintAssetController extends Controller
{
    /**
     * List all assets
     */
    public function index(): void
    {
        $this->requireLogin();
        
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM print_company_assets ORDER BY created_at DESC");
        $assets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('print_assets/index', [
            'title' => 'Company Asset Manager',
            'assets' => $assets
        ]);
    }

    /**
     * Upload asset
     */
    public function store(): void
    {
        $this->requireLogin();

        if (empty($_FILES['asset_file']['name'])) {
            $this->redirect('/administration/assets', 'Please select a file to upload.');
        }

        $db = Database::getInstance();
        $uploadDir = APP_ROOT . '/uploads/assets/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $type = (string)($_POST['asset_type'] ?? 'logo');
        $name = (string)($_POST['name'] ?? 'Asset Name');
        $branchId = !empty($_POST['branch_id']) ? (int)$_POST['branch_id'] : null;

        $originalName = basename($_FILES['asset_file']['name']);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $fileName = 'asset_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['asset_file']['tmp_name'], $targetPath)) {
            // Read image dimensions
            $info = @getimagesize($targetPath);
            $meta = [
                'width' => $info ? $info[0] : 0,
                'height' => $info ? $info[1] : 0,
                'crop' => null,
                'rotate' => 0,
                'opacity' => 1.0,
                'brightness' => 0,
                'contrast' => 0,
                'remove_bg' => false
            ];

            $stmt = $db->prepare("
                INSERT INTO print_company_assets (asset_type, name, file_path, metadata_json, branch_id, created_by, created_at)
                VALUES (:type, :name, :file, :meta, :branch, :user_id, NOW())
            ");
            $stmt->execute([
                'type' => $type,
                'name' => $name,
                'file' => 'uploads/assets/' . $fileName,
                'meta' => json_encode($meta),
                'branch' => $branchId,
                'user_id' => $this->currentUserId()
            ]);

            $this->redirect('/administration/assets', 'Asset uploaded successfully.');
        } else {
            $this->redirect('/administration/assets', 'Failed to save uploaded file.');
        }
    }

    /**
     * Update asset metadata (Enhancements, filters, crop, rotate)
     */
    public function update(string $id): void
    {
        $this->requireLogin();
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM print_company_assets WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $asset = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$asset) {
            $this->redirect('/administration/assets', 'Asset not found.');
        }

        $meta = json_decode((string)$asset['metadata_json'], true) ?: [];

        // Dynamic edits
        if (isset($_POST['rotate'])) {
            $meta['rotate'] = (int)$_POST['rotate'];
        }
        if (isset($_POST['opacity'])) {
            $meta['opacity'] = (float)$_POST['opacity'];
        }
        if (isset($_POST['brightness'])) {
            $meta['brightness'] = (int)$_POST['brightness'];
        }
        if (isset($_POST['contrast'])) {
            $meta['contrast'] = (int)$_POST['contrast'];
        }
        if (isset($_POST['remove_bg'])) {
            $meta['remove_bg'] = (bool)$_POST['remove_bg'];
        }
        if (isset($_POST['crop_x'], $_POST['crop_y'], $_POST['crop_w'], $_POST['crop_h'])) {
            $meta['crop'] = [
                'x' => (int)$_POST['crop_x'],
                'y' => (int)$_POST['crop_y'],
                'w' => (int)$_POST['crop_w'],
                'h' => (int)$_POST['crop_h']
            ];
        }

        // Apply enhancements if filters are requested
        if (!empty($meta['remove_bg']) || $meta['rotate'] != 0 || $meta['brightness'] != 0 || $meta['contrast'] != 0 || $meta['crop'] !== null) {
            require_once APP_ROOT . '/classes/AiAssetHelper.php';
            // We keep the original and update the visual path/meta, or save to a processed image
            try {
                $processedPath = AiAssetHelper::processImage(APP_ROOT . '/' . $asset['file_path'], $meta);
                // Extract relative path
                $relPath = str_replace(APP_ROOT . '/', '', $processedPath);
                $meta['processed_path'] = $relPath;
            } catch (Exception $e) {
                // Fail-safe fallback to original
            }
        }

        $stmt = $db->prepare("UPDATE print_company_assets SET metadata_json = :meta WHERE id = :id");
        $stmt->execute([
            'meta' => json_encode($meta),
            'id' => $id
        ]);

        $this->redirect('/administration/assets', 'Asset updated successfully.');
    }

    /**
     * Delete asset
     */
    public function delete(string $id): void
    {
        $this->requireLogin();
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM print_company_assets WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $asset = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($asset) {
            $filePath = APP_ROOT . '/' . $asset['file_path'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            $meta = json_decode((string)$asset['metadata_json'], true) ?: [];
            if (!empty($meta['processed_path'])) {
                @unlink(APP_ROOT . '/' . $meta['processed_path']);
            }

            $delStmt = $db->prepare("DELETE FROM print_company_assets WHERE id = :id");
            $delStmt->execute(['id' => $id]);
        }

        $this->redirect('/administration/assets', 'Asset deleted successfully.');
    }
}
