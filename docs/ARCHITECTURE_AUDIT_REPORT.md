# MESIGO ERP Enterprise Edition - Architecture Audit Report

**Author:** Antigravity AI Coding Assistant

**Date:** 2026-07-09

**Status:** Draft / Pending Review

## Executive Summary

This audit report performs a deep-dive analysis of the MESIGO ERP codebase to prepare for a transition to a professional, production-ready PSR-4 architectural structure. The current codebase has a flat namespace model (`App\Core` for all classes, `App\Middleware` for middleware) and relies heavily on hardcoded `require` and `require_once` statements. Restructuring without mapping these files will break the system due to missing dependencies.

This report documents the current inventory of PHP classes, database tables and their class interactions, and business logic locations. It concludes with a detailed **Gap Analysis** highlighting classes requiring namespace adjustments, broken dependencies under PSR-4 autoloading, and redundant/misplaced logic.

## 1. Index Inventory (PHP Classes)

The table below catalogs all PHP classes discovered in the workspace. Currently, the project contains 54 classes (52 inside `/classes` and 2 inside `/middleware`). It outlines their current namespaces, file paths, and any file dependencies declared through `require` or `include` statements.

| Class / Interface Name | Current Namespace | File Path | Proposed Namespace | Proposed Path | Declared File Dependencies |
| :--- | :--- | :--- | :--- | :--- | :--- |
| [AttachmentManager](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/AttachmentManager.php) | `App\Core` | `classes/AttachmentManager.php` | `App\Services` | `app/Services/AttachmentManager.php` | *(None)* |
| [Auth](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Auth.php) | `App\Core` | `classes/Auth.php` | `App\Models` | `app/Models/Auth.php` | *(None)* |
| [AuthController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/AuthController.php) | `App\Core` | `classes/AuthController.php` | `App\Controllers` | `app/Controllers/AuthController.php` | `require_once APP_ROOT . '/404.php'`<br>`require $viewFile` |
| [AuthMiddleware](file:///c:\Users\DELL\Desktop\MESIGO_ERP/middleware/AuthMiddleware.php) | `App\Middleware` | `middleware/AuthMiddleware.php` | `App\Middleware` | `app/Middleware/AuthMiddleware.php` | *(None)* |
| [BillOfLading](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/BillOfLading.php) | `App\Core` | `classes/BillOfLading.php` | `App\Models` | `app/Models/BillOfLading.php` | *(None)* |
| [BillOfLadingController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/BillOfLadingController.php) | `App\Core` | `classes/BillOfLadingController.php` | `App\Controllers` | `app/Controllers/BillOfLadingController.php` | `require_once APP_ROOT . '/classes/DocumentType.php'`<br>`require_once APP_ROOT . '/classes/NumberGenerator.php'`<br>`require_once APP_ROOT . '/classes/RevisionManager.php'`<br>`require_once APP_ROOT . '/classes/DocumentStatusEngine.php'`<br>`require_once APP_ROOT . '/classes/DocumentConversionEngine.php'`<br>`require_once APP_ROOT . '/classes/DocumentHeader.php'`<br>`require_once APP_ROOT . '/classes/DocumentItem.php'`<br>`require_once APP_ROOT . '/classes/Quotation.php'`<br>`require_once APP_ROOT . '/classes/ProformaInvoice.php'`<br>`require_once APP_ROOT . '/classes/CommercialInvoice.php'`<br>`require_once APP_ROOT . '/classes/PackingList.php'`<br>`require_once APP_ROOT . '/classes/ShippingBill.php'`<br>`require_once APP_ROOT . '/classes/BillOfLading.php'` |
| [Branch](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Branch.php) | `App\Core` | `classes/Branch.php` | `App\Models` | `app/Models/Branch.php` | *(None)* |
| [Buyer](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Buyer.php) | `App\Core` | `classes/Buyer.php` | `App\Models` | `app/Models/Buyer.php` | *(None)* |
| [BuyerController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/BuyerController.php) | `App\Core` | `classes/BuyerController.php` | `App\Controllers` | `app/Controllers/BuyerController.php` | `require_once APP_ROOT . '/classes/Buyer.php'` |
| [CertificateOfOrigin](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/CertificateOfOrigin.php) | `App\Core` | `classes/CertificateOfOrigin.php` | `App\Models` | `app/Models/CertificateOfOrigin.php` | *(None)* |
| [CertificateOfOriginController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/CertificateOfOriginController.php) | `App\Core` | `classes/CertificateOfOriginController.php` | `App\Controllers` | `app/Controllers/CertificateOfOriginController.php` | `require_once APP_ROOT . '/classes/DocumentType.php'`<br>`require_once APP_ROOT . '/classes/NumberGenerator.php'`<br>`require_once APP_ROOT . '/classes/RevisionManager.php'`<br>`require_once APP_ROOT . '/classes/DocumentStatusEngine.php'`<br>`require_once APP_ROOT . '/classes/DocumentConversionEngine.php'`<br>`require_once APP_ROOT . '/classes/DocumentHeader.php'`<br>`require_once APP_ROOT . '/classes/DocumentItem.php'`<br>`require_once APP_ROOT . '/classes/Quotation.php'`<br>`require_once APP_ROOT . '/classes/ProformaInvoice.php'`<br>`require_once APP_ROOT . '/classes/CommercialInvoice.php'`<br>`require_once APP_ROOT . '/classes/PackingList.php'`<br>`require_once APP_ROOT . '/classes/ShippingBill.php'`<br>`require_once APP_ROOT . '/classes/BillOfLading.php'`<br>`require_once APP_ROOT . '/classes/CertificateOfOrigin.php'` |
| [CommercialInvoice](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/CommercialInvoice.php) | `App\Core` | `classes/CommercialInvoice.php` | `App\Models` | `app/Models/CommercialInvoice.php` | *(None)* |
| [CommercialInvoiceController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/CommercialInvoiceController.php) | `App\Core` | `classes/CommercialInvoiceController.php` | `App\Controllers` | `app/Controllers/CommercialInvoiceController.php` | `require_once APP_ROOT . '/classes/DocumentType.php'`<br>`require_once APP_ROOT . '/classes/NumberGenerator.php'`<br>`require_once APP_ROOT . '/classes/RevisionManager.php'`<br>`require_once APP_ROOT . '/classes/DocumentStatusEngine.php'`<br>`require_once APP_ROOT . '/classes/DocumentConversionEngine.php'`<br>`require_once APP_ROOT . '/classes/DocumentHeader.php'`<br>`require_once APP_ROOT . '/classes/DocumentItem.php'`<br>`require_once APP_ROOT . '/classes/Quotation.php'`<br>`require_once APP_ROOT . '/classes/ProformaInvoice.php'`<br>`require_once APP_ROOT . '/classes/CommercialInvoice.php'` |
| [Company](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Company.php) | `App\Core` | `classes/Company.php` | `App\Models` | `app/Models/Company.php` | *(None)* |
| [CompanyController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/CompanyController.php) | `App\Core` | `classes/CompanyController.php` | `App\Controllers` | `app/Controllers/CompanyController.php` | `require_once APP_ROOT . '/classes/Company.php'` |
| [Controller](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Controller.php) | `App\Core` | `classes/Controller.php` | `App\Core` | `app/Core/Controller.php` | `require_once APP_ROOT . '/404.php'`<br>`require_once APP_ROOT . '/includes/header.php'`<br>`require_once $viewFile`<br>`require_once APP_ROOT . '/includes/footer.php'`<br>`require_once APP_ROOT . '/403.php'` |
| [DashboardController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/DashboardController.php) | `App\Core` | `classes/DashboardController.php` | `App\Controllers` | `app/Controllers/DashboardController.php` | *(None)* |
| [Database](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Database.php) | `App\Core` | `classes/Database.php` | `App\Core` | `app/Core/Database.php` | *(None)* |
| [DocumentConversionEngine](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/DocumentConversionEngine.php) | `App\Core` | `classes/DocumentConversionEngine.php` | `App\Services` | `app/Services/DocumentConversionEngine.php` | *(None)* |
| [DocumentHeader](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/DocumentHeader.php) | `App\Core` | `classes/DocumentHeader.php` | `App\Models` | `app/Models/DocumentHeader.php` | *(None)* |
| [DocumentItem](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/DocumentItem.php) | `App\Core` | `classes/DocumentItem.php` | `App\Models` | `app/Models/DocumentItem.php` | *(None)* |
| [DocumentStatusEngine](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/DocumentStatusEngine.php) | `App\Core` | `classes/DocumentStatusEngine.php` | `App\Services` | `app/Services/DocumentStatusEngine.php` | *(None)* |
| [DocumentType](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/DocumentType.php) | `App\Core` | `classes/DocumentType.php` | `App\Core` | `app/Core/DocumentType.php` | *(None)* |
| [ExportDocumentController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/ExportDocumentController.php) | `App\Core` | `classes/ExportDocumentController.php` | `App\Controllers` | `app/Controllers/ExportDocumentController.php` | `require_once APP_ROOT . '/classes/AttachmentManager.php'`<br>`require_once APP_ROOT . '/classes/DocumentStatusEngine.php'`<br>`require_once APP_ROOT . '/classes/ExportDocumentManager.php'` |
| [ExportDocumentManager](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/ExportDocumentManager.php) | `App\Core` | `classes/ExportDocumentManager.php` | `App\Services` | `app/Services/ExportDocumentManager.php` | *(None)* |
| [HSCode](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/HSCode.php) | `App\Core` | `classes/HSCode.php` | `App\Models` | `app/Models/HSCode.php` | *(None)* |
| [Logger](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Logger.php) | `App\Core` | `classes/Logger.php` | `App\Core` | `app/Core/Logger.php` | *(None)* |
| [MasterDataModel](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/MasterDataModel.php) | `App\Core` | `classes/MasterDataModel.php` | `App\Core` | `app/Core/MasterDataModel.php` | *(None)* |
| [NumberGenerator](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/NumberGenerator.php) | `App\Core` | `classes/NumberGenerator.php` | `App\Services` | `app/Services/NumberGenerator.php` | *(None)* |
| [PackingList](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/PackingList.php) | `App\Core` | `classes/PackingList.php` | `App\Models` | `app/Models/PackingList.php` | *(None)* |
| [PackingListController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/PackingListController.php) | `App\Core` | `classes/PackingListController.php` | `App\Controllers` | `app/Controllers/PackingListController.php` | `require_once APP_ROOT . '/classes/DocumentType.php'`<br>`require_once APP_ROOT . '/classes/NumberGenerator.php'`<br>`require_once APP_ROOT . '/classes/RevisionManager.php'`<br>`require_once APP_ROOT . '/classes/DocumentStatusEngine.php'`<br>`require_once APP_ROOT . '/classes/DocumentConversionEngine.php'`<br>`require_once APP_ROOT . '/classes/DocumentHeader.php'`<br>`require_once APP_ROOT . '/classes/DocumentItem.php'`<br>`require_once APP_ROOT . '/classes/Quotation.php'`<br>`require_once APP_ROOT . '/classes/ProformaInvoice.php'`<br>`require_once APP_ROOT . '/classes/CommercialInvoice.php'`<br>`require_once APP_ROOT . '/classes/PackingList.php'` |
| [Pagination](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Pagination.php) | `App\Core` | `classes/Pagination.php` | `App\Core` | `app/Core/Pagination.php` | *(None)* |
| [Permission](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Permission.php) | `App\Core` | `classes/Permission.php` | `App\Models` | `app/Models/Permission.php` | *(None)* |
| [PermissionController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/PermissionController.php) | `App\Core` | `classes/PermissionController.php` | `App\Controllers` | `app/Controllers/PermissionController.php` | `require_once APP_ROOT . '/classes/Permission.php'` |
| [PermissionMiddleware](file:///c:\Users\DELL\Desktop\MESIGO_ERP/middleware/PermissionMiddleware.php) | `App\Middleware` | `middleware/PermissionMiddleware.php` | `App\Middleware` | `app/Middleware/PermissionMiddleware.php` | `require_once APP_ROOT . '/403.php'` |
| [Product](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Product.php) | `App\Core` | `classes/Product.php` | `App\Models` | `app/Models/Product.php` | *(None)* |
| [ProductController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/ProductController.php) | `App\Core` | `classes/ProductController.php` | `App\Controllers` | `app/Controllers/ProductController.php` | `require_once APP_ROOT . '/classes/Product.php'` |
| [ProductGrade](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/ProductGrade.php) | `App\Core` | `classes/ProductGrade.php` | `App\Models` | `app/Models/ProductGrade.php` | *(None)* |
| [ProductMasterDataController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/ProductMasterDataController.php) | `App\Core` | `classes/ProductMasterDataController.php` | `App\Controllers` | `app/Controllers/ProductMasterDataController.php` | `require APP_ROOT . '/404.php'` |
| [ProformaInvoice](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/ProformaInvoice.php) | `App\Core` | `classes/ProformaInvoice.php` | `App\Models` | `app/Models/ProformaInvoice.php` | *(None)* |
| [ProformaInvoiceController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/ProformaInvoiceController.php) | `App\Core` | `classes/ProformaInvoiceController.php` | `App\Controllers` | `app/Controllers/ProformaInvoiceController.php` | `require_once APP_ROOT . '/classes/DocumentType.php'`<br>`require_once APP_ROOT . '/classes/NumberGenerator.php'`<br>`require_once APP_ROOT . '/classes/RevisionManager.php'`<br>`require_once APP_ROOT . '/classes/DocumentStatusEngine.php'`<br>`require_once APP_ROOT . '/classes/DocumentConversionEngine.php'`<br>`require_once APP_ROOT . '/classes/DocumentHeader.php'`<br>`require_once APP_ROOT . '/classes/DocumentItem.php'`<br>`require_once APP_ROOT . '/classes/Quotation.php'`<br>`require_once APP_ROOT . '/classes/ProformaInvoice.php'` |
| [Quotation](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Quotation.php) | `App\Core` | `classes/Quotation.php` | `App\Models` | `app/Models/Quotation.php` | *(None)* |
| [QuotationController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/QuotationController.php) | `App\Core` | `classes/QuotationController.php` | `App\Controllers` | `app/Controllers/QuotationController.php` | `require_once APP_ROOT . '/classes/DocumentType.php'`<br>`require_once APP_ROOT . '/classes/NumberGenerator.php'`<br>`require_once APP_ROOT . '/classes/RevisionManager.php'`<br>`require_once APP_ROOT . '/classes/DocumentStatusEngine.php'`<br>`require_once APP_ROOT . '/classes/DocumentConversionEngine.php'`<br>`require_once APP_ROOT . '/classes/DocumentHeader.php'`<br>`require_once APP_ROOT . '/classes/DocumentItem.php'`<br>`require_once APP_ROOT . '/classes/Quotation.php'` |
| [ReportController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/ReportController.php) | `App\Core` | `classes/ReportController.php` | `App\Controllers` | `app/Controllers/ReportController.php` | `require_once APP_ROOT . '/classes/ReportService.php'` |
| [ReportService](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/ReportService.php) | `App\Core` | `classes/ReportService.php` | `App\Services` | `app/Services/ReportService.php` | *(None)* |
| [Response](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Response.php) | `App\Core` | `classes/Response.php` | `App\Core` | `app/Core/Response.php` | *(None)* |
| [RevisionManager](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/RevisionManager.php) | `App\Core` | `classes/RevisionManager.php` | `App\Services` | `app/Services/RevisionManager.php` | *(None)* |
| [Role](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Role.php) | `App\Core` | `classes/Role.php` | `App\Models` | `app/Models/Role.php` | *(None)* |
| [RoleController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/RoleController.php) | `App\Core` | `classes/RoleController.php` | `App\Controllers` | `app/Controllers/RoleController.php` | `require_once APP_ROOT . '/classes/Role.php'`<br>`require_once APP_ROOT . '/classes/Permission.php'` |
| [Router](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Router.php) | `App\Core` | `classes/Router.php` | `App\Core` | `app/Core/Router.php` | `require_once APP_ROOT . '/404.php'`<br>`require_once $controllerFile`<br>`require_once APP_ROOT . '/404.php'`<br>`require_once APP_ROOT . '/404.php'` |
| [Session](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Session.php) | `App\Core` | `classes/Session.php` | `App\Core` | `app/Core/Session.php` | *(None)* |
| [ShippingBill](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/ShippingBill.php) | `App\Core` | `classes/ShippingBill.php` | `App\Models` | `app/Models/ShippingBill.php` | *(None)* |
| [ShippingBillController](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/ShippingBillController.php) | `App\Core` | `classes/ShippingBillController.php` | `App\Controllers` | `app/Controllers/ShippingBillController.php` | `require_once APP_ROOT . '/classes/DocumentType.php'`<br>`require_once APP_ROOT . '/classes/NumberGenerator.php'`<br>`require_once APP_ROOT . '/classes/RevisionManager.php'`<br>`require_once APP_ROOT . '/classes/DocumentStatusEngine.php'`<br>`require_once APP_ROOT . '/classes/DocumentConversionEngine.php'`<br>`require_once APP_ROOT . '/classes/DocumentHeader.php'`<br>`require_once APP_ROOT . '/classes/DocumentItem.php'`<br>`require_once APP_ROOT . '/classes/Quotation.php'`<br>`require_once APP_ROOT . '/classes/ProformaInvoice.php'`<br>`require_once APP_ROOT . '/classes/CommercialInvoice.php'`<br>`require_once APP_ROOT . '/classes/PackingList.php'`<br>`require_once APP_ROOT . '/classes/ShippingBill.php'` |
| [Validator](file:///c:\Users\DELL\Desktop\MESIGO_ERP/classes/Validator.php) | `App\Core` | `classes/Validator.php` | `App\Core` | `app/Core/Validator.php` | *(None)* |

## 2. Database Schema & Class Mappings

We identified **108 tables** from `database/schema/schema.sql` and the SQL files in `database/migrations/`. Below is the complete mapping of these database tables to the PHP classes that query or reference them. This ensures database queries are updated alongside class migrations.

| Table Name | Interacting Classes / Files | Reference Type |
| :--- | :--- | :--- |
| `ai_action_logs` | *(No direct code references found)* | Query / Reference |
| `ai_conversations` | *(No direct code references found)* | Query / Reference |
| `ai_messages` | *(No direct code references found)* | Query / Reference |
| `audit_logs` | *(No direct code references found)* | Query / Reference |
| `banks` | Class `ProductMasterDataController` | Query / Reference |
| `bills_of_lading` | Class `DashboardController` | Query / Reference |
| `branches` | Class `Branch` | Query / Reference |
| `buyer_addresses` | Class `Buyer` | Query / Reference |
| `buyer_contacts` | Class `Buyer`, Class `Quotation` | Query / Reference |
| `buyers` | Class `BillOfLadingController`, Class `Buyer`, Class `BuyerController`, Class `CertificateOfOriginController`, Class `CommercialInvoiceController`, Class `DashboardController`, Class `DocumentHeader`, Class `ExportDocumentManager`, Class `PackingListController`, Class `ProformaInvoiceController`, Class `Quotation`, Class `QuotationController`, Class `ReportController`, Class `ReportService`, Class `ShippingBillController`, File `includes/sidebar.php` | Query / Reference |
| `certificate_items` | *(No direct code references found)* | Query / Reference |
| `certificates` | Class `CertificateOfOriginController`, File `includes/sidebar.php` | Query / Reference |
| `cities` | Class `Buyer` | Query / Reference |
| `commercial_invoice_items` | *(No direct code references found)* | Query / Reference |
| `commercial_invoices` | Class `CommercialInvoiceController`, Class `DashboardController` | Query / Reference |
| `company` | Class `Company`, Class `CompanyController`, File `includes/sidebar.php` | Query / Reference |
| `container_types` | Class `ProductMasterDataController` | Query / Reference |
| `containers` | *(No direct code references found)* | Query / Reference |
| `countries` | Class `Buyer`, Class `DocumentItem`, Class `ProductMasterDataController` | Query / Reference |
| `crm_activities` | *(No direct code references found)* | Query / Reference |
| `crm_communications` | *(No direct code references found)* | Query / Reference |
| `currencies` | Class `BillOfLadingController`, Class `CertificateOfOriginController`, Class `CommercialInvoiceController`, Class `DocumentHeader`, Class `PackingListController`, Class `ProductMasterDataController`, Class `ProformaInvoiceController`, Class `Quotation`, Class `QuotationController`, Class `ReportController`, Class `ReportService`, Class `ShippingBillController` | Query / Reference |
| `dashboard_metrics_cache` | *(No direct code references found)* | Query / Reference |
| `dashboard_widgets` | *(No direct code references found)* | Query / Reference |
| `document_attachments` | Class `AttachmentManager`, Class `ExportDocumentManager` | Query / Reference |
| `document_charges` | Class `Quotation` | Query / Reference |
| `document_headers` | Class `DashboardController`, Class `DocumentConversionEngine`, Class `DocumentHeader`, Class `DocumentStatusEngine`, Class `ExportDocumentManager`, Class `Quotation`, Class `ReportService` | Query / Reference |
| `document_items` | Class `DocumentItem`, Class `PackingList`, Class `Quotation`, Class `ReportService` | Query / Reference |
| `document_revisions` | Class `RevisionManager` | Query / Reference |
| `document_status_history` | Class `DashboardController`, Class `DocumentHeader`, Class `DocumentStatusEngine` | Query / Reference |
| `document_terms` | *(No direct code references found)* | Query / Reference |
| `document_types` | Class `DashboardController`, Class `DocumentHeader`, Class `DocumentType`, Class `ExportDocumentManager`, Class `ReportService` | Query / Reference |
| `email_logs` | *(No direct code references found)* | Query / Reference |
| `email_templates` | *(No direct code references found)* | Query / Reference |
| `expense_categories` | *(No direct code references found)* | Query / Reference |
| `expenses` | *(No direct code references found)* | Query / Reference |
| `financial_years` | *(No direct code references found)* | Query / Reference |
| `freight_forwarders` | *(No direct code references found)* | Query / Reference |
| `goods_receipt_items` | *(No direct code references found)* | Query / Reference |
| `goods_receipts` | *(No direct code references found)* | Query / Reference |
| `hs_codes` | Class `HSCode`, Class `Product`, Class `ProductMasterDataController` | Query / Reference |
| `incoterms` | Class `CommercialInvoiceController`, Class `PackingListController`, Class `ProductMasterDataController`, Class `ProformaInvoiceController`, Class `Quotation`, Class `QuotationController`, Class `ShippingBillController` | Query / Reference |
| `inspection_agencies` | *(No direct code references found)* | Query / Reference |
| `login_logs` | Class `Auth` | Query / Reference |
| `notification_templates` | *(No direct code references found)* | Query / Reference |
| `notifications` | File `includes/footer.php` | Query / Reference |
| `number_series` | Class `NumberGenerator` | Query / Reference |
| `order_costing_items` | *(No direct code references found)* | Query / Reference |
| `order_costings` | *(No direct code references found)* | Query / Reference |
| `packing_list_items` | *(No direct code references found)* | Query / Reference |
| `packing_lists` | Class `DashboardController`, Class `PackingListController` | Query / Reference |
| `packing_types` | Class `BillOfLadingController`, Class `CommercialInvoiceController`, Class `DocumentItem`, Class `PackingListController`, Class `Product`, Class `ProductMasterDataController`, Class `ProformaInvoiceController`, Class `Quotation`, Class `QuotationController`, Class `ShippingBillController` | Query / Reference |
| `password_resets` | *(No direct code references found)* | Query / Reference |
| `payables` | *(No direct code references found)* | Query / Reference |
| `payment_allocations` | *(No direct code references found)* | Query / Reference |
| `payment_receipts` | *(No direct code references found)* | Query / Reference |
| `payment_terms` | Class `BuyerController`, Class `CommercialInvoiceController`, Class `PackingListController`, Class `ProductMasterDataController`, Class `ProformaInvoiceController`, Class `Quotation`, Class `QuotationController`, Class `ShippingBillController` | Query / Reference |
| `payments` | *(No direct code references found)* | Query / Reference |
| `permissions` | Class `Auth`, Class `Permission`, Class `PermissionController`, Class `Role`, Class `RoleController`, File `includes/sidebar.php` | Query / Reference |
| `ports` | Class `BillOfLadingController`, Class `CommercialInvoiceController`, Class `PackingListController`, Class `ProductMasterDataController`, Class `ProformaInvoiceController`, Class `Quotation`, Class `QuotationController`, Class `ShippingBillController` | Query / Reference |
| `product_categories` | Class `Product`, Class `ProductMasterDataController`, Class `ReportService` | Query / Reference |
| `product_grades` | Class `CommercialInvoiceController`, Class `PackingListController`, Class `Product`, Class `ProductGrade`, Class `ProductMasterDataController`, Class `ProformaInvoiceController`, Class `QuotationController` | Query / Reference |
| `product_origins` | Class `CommercialInvoiceController`, Class `PackingListController`, Class `Product`, Class `ProductMasterDataController`, Class `ProformaInvoiceController`, Class `QuotationController` | Query / Reference |
| `product_packaging` | Class `Product` | Query / Reference |
| `product_varieties` | Class `DocumentItem` | Query / Reference |
| `products` | Class `BillOfLadingController`, Class `CertificateOfOriginController`, Class `CommercialInvoiceController`, Class `DashboardController`, Class `DocumentItem`, Class `PackingListController`, Class `Product`, Class `ProductController`, Class `ProformaInvoiceController`, Class `Quotation`, Class `QuotationController`, Class `ReportController`, Class `ReportService`, Class `ShippingBillController`, File `includes/sidebar.php` | Query / Reference |
| `proforma_invoice_items` | *(No direct code references found)* | Query / Reference |
| `proforma_invoices` | Class `DashboardController`, Class `ProformaInvoiceController` | Query / Reference |
| `purchase_order_items` | *(No direct code references found)* | Query / Reference |
| `purchase_orders` | *(No direct code references found)* | Query / Reference |
| `purchase_request_items` | *(No direct code references found)* | Query / Reference |
| `purchase_requests` | *(No direct code references found)* | Query / Reference |
| `quotation_items` | *(No direct code references found)* | Query / Reference |
| `quotations` | Class `DashboardController`, Class `QuotationController`, Class `ReportService`, File `includes/sidebar.php` | Query / Reference |
| `receivables` | *(No direct code references found)* | Query / Reference |
| `report_definitions` | *(No direct code references found)* | Query / Reference |
| `report_exports` | *(No direct code references found)* | Query / Reference |
| `role_permissions` | *(No direct code references found)* | Query / Reference |
| `roles` | Class `Auth`, Class `Role`, Class `RoleController`, File `includes/sidebar.php` | Query / Reference |
| `sales_order_items` | *(No direct code references found)* | Query / Reference |
| `sales_orders` | *(No direct code references found)* | Query / Reference |
| `scheduled_jobs` | *(No direct code references found)* | Query / Reference |
| `settings` | Class `ProductMasterDataController`, File `includes/navbar.php`, File `includes/sidebar.php` | Query / Reference |
| `shipment_containers` | *(No direct code references found)* | Query / Reference |
| `shipment_items` | *(No direct code references found)* | Query / Reference |
| `shipment_milestones` | *(No direct code references found)* | Query / Reference |
| `shipments` | *(No direct code references found)* | Query / Reference |
| `shipping_bills` | Class `DashboardController`, Class `ShippingBillController` | Query / Reference |
| `shipping_lines` | *(No direct code references found)* | Query / Reference |
| `shipping_terms` | Class `ProductMasterDataController` | Query / Reference |
| `states` | Class `Buyer` | Query / Reference |
| `stock_adjustment_items` | *(No direct code references found)* | Query / Reference |
| `stock_adjustments` | *(No direct code references found)* | Query / Reference |
| `stock_batches` | *(No direct code references found)* | Query / Reference |
| `stock_ledgers` | *(No direct code references found)* | Query / Reference |
| `supplier_addresses` | *(No direct code references found)* | Query / Reference |
| `supplier_contacts` | *(No direct code references found)* | Query / Reference |
| `suppliers` | Class `DocumentHeader` | Query / Reference |
| `task_comments` | *(No direct code references found)* | Query / Reference |
| `tasks` | *(No direct code references found)* | Query / Reference |
| `units` | Class `CommercialInvoiceController`, Class `DocumentItem`, Class `PackingListController`, Class `Product`, Class `ProductController`, Class `ProductMasterDataController`, Class `ProformaInvoiceController`, Class `Quotation`, Class `QuotationController`, File `includes/sidebar.php` | Query / Reference |
| `user_roles` | *(No direct code references found)* | Query / Reference |
| `user_sessions` | *(No direct code references found)* | Query / Reference |
| `users` | Class `AttachmentManager`, Class `Auth`, Class `DocumentHeader`, Class `DocumentStatusEngine`, Class `RevisionManager`, File `includes/sidebar.php` | Query / Reference |
| `warehouse_locations` | *(No direct code references found)* | Query / Reference |
| `warehouses` | *(No direct code references found)* | Query / Reference |
| `whatsapp_logs` | *(No direct code references found)* | Query / Reference |
| `whatsapp_templates` | *(No direct code references found)* | Query / Reference |

## 3. Business Logic Locations

Currently, the business logic of MESIGO ERP is distributed across three main areas, often violating standard MVC separation of concerns:

1. **Controllers (Presentation/Application Layer):**

   - **Presentation & Workflow Control:** Controllers handle HTTP request variables (`$_GET`, `$_POST`), direct validation via `Validator`, and output views via `render()` (inherited from `Controller`).

   - **Misplaced Business/Data Logic:** Several controllers bypass the Model layer entirely and execute SQL queries directly using the PDO instance:

     - `DashboardController`: Performs 16 scalar queries (e.g. `SELECT COUNT(*)` on `buyers`, `products`, `document_headers`) directly inside `index()` to populate metrics.

     - `ProductMasterDataController`: Contains private query functions (`rows()`, `countRows()`, `insert()`, `updateRow()`) that issue raw SQL strings to multiple configuration tables. It handles categories, grades, origins, hs codes, units, and packing types in a single class.

     - Document workflow and status conversions are checked inside controllers (e.g. `BillOfLadingController::status()`, `CertificateOfOriginController::convert()`), leading to fat controller actions.

2. **Models (Data Access & Entity Layer):**

   - **Data-Mapping & Persistence:** Models (e.g., `Buyer`, `Product`, `Quotation`) write raw SQL queries, manage transactions, and run `PDOStatement` bindings. They represent the active entity mapping to the database.

   - **Embedded Business Logic:** Models contain transaction synchronization and sub-entity updates. For instance, `Buyer::create()` and `Buyer::update()` run transactions that sync `buyer_contacts` and `buyer_addresses` tables.

3. **Core Engines (Infrastructure Layer):**

   - Classes like `DocumentConversionEngine`, `DocumentStatusEngine`, `RevisionManager`, `NumberGenerator`, and `ExportDocumentManager` are classified under the `App\Core` namespace. However, they manage domain-specific rules (document conversions, revisions, status workflows, sequential document numbering), which belongs in the Service layer.

4. **Helpers (Procedural Utility):**

   - `helpers/functions.php` contains procedural helpers such as HTML escaping (`escapeHtml()`), CSRF token inputs (`csrfToken()`), formatting (`formatDate()`, `formatCurrency()`), and status badges (`statusBadge()`). They are globally loaded and used in view templates.

## 4. Gap Analysis

Before moving any files, we must address these specific gaps to prevent runtime exceptions and code decay:

### 4.1 Namespace Alignment

- **The Issue:** No classes are technically missing a namespace statement. However, **52 out of 54 classes** are currently grouped under the single flat namespace `App\Core`. Only two classes are in `App\Middleware`.

- **The Gap:** Once we adopt PSR-4 autoloading, a class namespace must match its directory structure. Grouping everything under `App\Core` is a bad practice and prevents standard folder structures. We must re-namespace them as follows:

  - Move core infrastructure classes (`Database`, `Router`, `Session`, `Response`, `Validator`, `Logger`, `Pagination`, `Controller`, `MasterDataModel`) to `app/Core/` under namespace `App\Core`.

  - Move controllers to `app/Controllers/` under namespace `App\Controllers`.

  - Move models to `app/Models/` under namespace `App\Models`.

  - Move domain helpers and engines to `app/Services/` under namespace `App\Services`.

- **Risks:** Changing the namespace of these classes means any calls referencing their old namespace (e.g., `\App\Core\Buyer` inside a controller) will throw `Class 'App\Core\Buyer' not found`. We must carefully update all namespace usages.

### 4.2 File Dependencies (Require/Include Chains)

- **The Issue:** There are **19 class files** containing hardcoded `require` or `require_once` statements. For instance, `BuyerController.php` has `require_once APP_ROOT . '/classes/Buyer.php';`.

- **The Gap:** Once PSR-4 autoloading is implemented (by registering `'"App\\": "app/"'` in `composer.json`), the autoloader will locate and load these classes on demand.

- **Broken Links:** If we move the files to the `/app` subdirectory structure (e.g. `app/Models/Buyer.php`) and leave the `require_once APP_ROOT . '/classes/Buyer.php';` statement untouched, **the application will crash with a Fatal Error** because `/classes/Buyer.php` no longer exists. All hardcoded class `require_once` statements in controllers and loaders **must be removed** and replaced with `use` namespace declarations where appropriate.

- **Bootstrap / Entry Point:** `index.php` and `includes/loader.php` currently require all core classes explicitly. These must be replaced with the standard Composer autoloader reference (`require_once APP_ROOT . '/vendor/autoload.php';`).

### 4.3 Redundant and Misplaced Business Logic

To maintain MVC separation of concerns and build a clean architecture, the following logic must be refactored:

- **Controllers with Direct SQL Queries:**

  - **Dashboard Metrics:** `DashboardController` contains direct SQL select/count statements. These should be moved to their respective model classes (e.g., `Buyer::count()`, `Product::count()`, `DocumentHeader::countPending()`) or encapsulated in a `DashboardService` class.

  - **Product Master Data CRUD:** `ProductMasterDataController` has extensive SQL queries for master tables. This logic should be delegated to specialized Models extending `MasterDataModel` (e.g., `ProductCategory`, `ProductGrade`, `ProductOrigin`, `HSCode`, `Unit`, `PackingType`). The controller should only orchestrate request input and model calls.

- **Business Engines in App\Core:**

  - `DocumentConversionEngine`, `DocumentStatusEngine`, `RevisionManager`, `NumberGenerator`, `ExportDocumentManager`, and `AttachmentManager` contain domain workflows and state transitions. They should be relocated to the `app/Services` folder under namespace `App\Services` (or similar domain service classes). This isolates business logic from core infrastructure.

- **Redundant Loading:**

  - In document controllers (`BillOfLadingController`, `CertificateOfOriginController`, `CommercialInvoiceController`, `PackingListController`, `ProformaInvoiceController`, `QuotationController`, `ShippingBillController`), there is a massive block of class loading (10-15 lines of `require_once`) in their constructor. By utilizing Composer autoloader, this entire block is rendered redundant and should be deleted.

---

*End of Report. Proceed to next phase after user review and approval.*