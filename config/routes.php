<?php
declare(strict_types=1);

/**
 * MESIGO ERP - Routes Configuration
 */

// Web Routes
$router->get('/', 'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// Authentication Routes
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password/{token}', 'AuthController@showResetPassword');
$router->post('/reset-password', 'AuthController@resetPassword');

// User Management Routes
$router->get('/profile', 'UserController@showProfile');
$router->post('/profile', 'UserController@updateProfile');
$router->get('/users', 'UserController@index');
$router->get('/users/create', 'UserController@create');
$router->post('/users', 'UserController@store');
$router->get('/users/{id}', 'UserController@show');
$router->get('/users/{id}/edit', 'UserController@edit');
$router->post('/users/{id}', 'UserController@update');
$router->post('/users/{id}/delete', 'UserController@delete');

// Role & Permission Routes
$router->get('/roles', 'RoleController@index');
$router->get('/roles/create', 'RoleController@create');
$router->post('/roles', 'RoleController@store');
$router->get('/roles/{id}/edit', 'RoleController@edit');
$router->post('/roles/{id}', 'RoleController@update');
$router->post('/roles/{id}/delete', 'RoleController@delete');
$router->get('/permissions', 'PermissionController@index');
$router->get('/permissions/create', 'PermissionController@create');
$router->post('/permissions', 'PermissionController@store');
$router->get('/permissions/{id}/edit', 'PermissionController@edit');
$router->post('/permissions/{id}', 'PermissionController@update');
$router->post('/permissions/{id}/delete', 'PermissionController@delete');

// Buyer CRM Routes
$router->get('/buyers', 'BuyerController@index');
$router->get('/buyers/create', 'BuyerController@create');
$router->post('/buyers', 'BuyerController@store');
$router->get('/buyers/{id}/edit', 'BuyerController@edit');
$router->post('/buyers/{id}', 'BuyerController@update');
$router->post('/buyers/{id}/delete', 'BuyerController@delete');
$router->get('/buyers/{id}/details', 'BuyerController@details');

// Supplier CRM Routes
$router->get('/suppliers', 'SupplierController@index');
$router->get('/suppliers/create', 'SupplierController@create');
$router->post('/suppliers', 'SupplierController@store');
$router->get('/suppliers/{id}/edit', 'SupplierController@edit');
$router->post('/suppliers/{id}', 'SupplierController@update');
$router->post('/suppliers/{id}/delete', 'SupplierController@delete');
$router->get('/suppliers/{id}/details', 'SupplierController@details');

// Product Master Routes
$router->get('/products', 'ProductController@index');
$router->get('/products/create', 'ProductController@create');
$router->post('/products', 'ProductController@store');
$router->get('/products/{id}/edit', 'ProductController@edit');
$router->post('/products/{id}', 'ProductController@update');
$router->post('/products/{id}/delete', 'ProductController@delete');
$router->get('/products/{id}/details', 'ProductController@details');

// Quotation Engine Routes
$router->get('/quotations', 'QuotationController@index');
$router->get('/quotations/create', 'QuotationController@create');
$router->post('/quotations', 'QuotationController@store');
$router->get('/quotations/{id}', 'QuotationController@show');
$router->get('/quotations/{id}/edit', 'QuotationController@edit');
$router->post('/quotations/{id}', 'QuotationController@update');
$router->post('/quotations/{id}/status', 'QuotationController@status');
$router->post('/quotations/{id}/revise', 'QuotationController@revise');
$router->get('/quotations/{id}/print', 'QuotationController@print');
$router->post('/quotations/{id}/email', 'QuotationController@email');
$router->post('/quotations/{id}/convert', 'QuotationController@convert');
$router->post('/quotations/{id}/delete', 'QuotationController@delete');
$router->get('/settings/cost-templates/{id}/items', 'QuotationController@costTemplateItems');

// Proforma Invoice Routes
$router->get('/proforma-invoices', 'ProformaInvoiceController@index');
$router->get('/proforma-invoices/create', 'ProformaInvoiceController@create');
$router->post('/proforma-invoices', 'ProformaInvoiceController@store');
$router->get('/proforma-invoices/{id}', 'ProformaInvoiceController@show');
$router->get('/proforma-invoices/{id}/edit', 'ProformaInvoiceController@edit');
$router->post('/proforma-invoices/{id}', 'ProformaInvoiceController@update');
$router->post('/proforma-invoices/{id}/status', 'ProformaInvoiceController@status');
$router->post('/proforma-invoices/{id}/revise', 'ProformaInvoiceController@revise');
$router->get('/proforma-invoices/{id}/print', 'ProformaInvoiceController@print');
$router->post('/proforma-invoices/{id}/email', 'ProformaInvoiceController@email');
$router->post('/proforma-invoices/{id}/convert', 'ProformaInvoiceController@convert');
$router->post('/proforma-invoices/{id}/delete', 'ProformaInvoiceController@delete');

// Commercial Invoice Routes
$router->get('/commercial-invoices', 'CommercialInvoiceController@index');
$router->get('/commercial-invoices/create', 'CommercialInvoiceController@create');
$router->post('/commercial-invoices', 'CommercialInvoiceController@store');
$router->get('/commercial-invoices/{id}', 'CommercialInvoiceController@show');
$router->get('/commercial-invoices/{id}/edit', 'CommercialInvoiceController@edit');
$router->post('/commercial-invoices/{id}', 'CommercialInvoiceController@update');
$router->post('/commercial-invoices/{id}/status', 'CommercialInvoiceController@status');
$router->post('/commercial-invoices/{id}/revise', 'CommercialInvoiceController@revise');
$router->get('/commercial-invoices/{id}/print', 'CommercialInvoiceController@print');
$router->post('/commercial-invoices/{id}/email', 'CommercialInvoiceController@email');
$router->post('/commercial-invoices/{id}/convert', 'CommercialInvoiceController@convert');
$router->post('/commercial-invoices/{id}/delete', 'CommercialInvoiceController@delete');

// Packing List Routes
$router->get('/packing-lists', 'PackingListController@index');
$router->get('/packing-lists/create', 'PackingListController@create');
$router->post('/packing-lists', 'PackingListController@store');
$router->get('/packing-lists/{id}', 'PackingListController@show');
$router->get('/packing-lists/{id}/edit', 'PackingListController@edit');
$router->post('/packing-lists/{id}', 'PackingListController@update');
$router->post('/packing-lists/{id}/status', 'PackingListController@status');
$router->post('/packing-lists/{id}/revise', 'PackingListController@revise');
$router->get('/packing-lists/{id}/print', 'PackingListController@print');
$router->post('/packing-lists/{id}/email', 'PackingListController@email');
$router->post('/packing-lists/{id}/delete', 'PackingListController@delete');

// Shipping Bill Routes
$router->get('/shipping-bills', 'ShippingBillController@index');
$router->get('/shipping-bills/create', 'ShippingBillController@create');
$router->post('/shipping-bills', 'ShippingBillController@store');
$router->get('/shipping-bills/{id}', 'ShippingBillController@show');
$router->get('/shipping-bills/{id}/edit', 'ShippingBillController@edit');
$router->post('/shipping-bills/{id}', 'ShippingBillController@update');
$router->post('/shipping-bills/{id}/status', 'ShippingBillController@status');
$router->post('/shipping-bills/{id}/revise', 'ShippingBillController@revise');
$router->get('/shipping-bills/{id}/print', 'ShippingBillController@print');
$router->post('/shipping-bills/{id}/email', 'ShippingBillController@email');
$router->post('/shipping-bills/{id}/convert', 'ShippingBillController@convert');
$router->post('/shipping-bills/{id}/convert-co', 'ShippingBillController@convertCo');
$router->post('/shipping-bills/{id}/convert-phyto', 'ShippingBillController@convertPhyto');
$router->post('/shipping-bills/{id}/delete', 'ShippingBillController@delete');

// Bill of Lading Routes
$router->get('/bill-of-ladings', 'BillOfLadingController@index');
$router->get('/bill-of-ladings/create', 'BillOfLadingController@create');
$router->post('/bill-of-ladings', 'BillOfLadingController@store');
$router->get('/bill-of-ladings/{id}', 'BillOfLadingController@show');
$router->get('/bill-of-ladings/{id}/edit', 'BillOfLadingController@edit');
$router->post('/bill-of-ladings/{id}', 'BillOfLadingController@update');
$router->post('/bill-of-ladings/{id}/status', 'BillOfLadingController@status');
$router->post('/bill-of-ladings/{id}/revise', 'BillOfLadingController@revise');
$router->get('/bill-of-ladings/{id}/print', 'BillOfLadingController@print');
$router->post('/bill-of-ladings/{id}/convert', 'BillOfLadingController@convert');
$router->post('/bill-of-ladings/{id}/delete', 'BillOfLadingController@delete');

// Certificate of Origin Routes
$router->get('/certificate-of-origins', 'CertificateOfOriginController@index');
$router->get('/certificate-of-origins/create', 'CertificateOfOriginController@create');
$router->post('/certificate-of-origins', 'CertificateOfOriginController@store');
$router->get('/certificate-of-origins/{id}', 'CertificateOfOriginController@show');
$router->get('/certificate-of-origins/{id}/edit', 'CertificateOfOriginController@edit');
$router->post('/certificate-of-origins/{id}', 'CertificateOfOriginController@update');
$router->post('/certificate-of-origins/{id}/status', 'CertificateOfOriginController@status');
$router->post('/certificate-of-origins/{id}/revise', 'CertificateOfOriginController@revise');
$router->get('/certificate-of-origins/{id}/print', 'CertificateOfOriginController@print');
$router->post('/certificate-of-origins/{id}/delete', 'CertificateOfOriginController@delete');

// Non-Hazardous Certificate Routes
$router->get('/non-hazardous-certs', 'NonHazardousCertController@index');
$router->get('/non-hazardous-certs/{id}', 'NonHazardousCertController@show');
$router->post('/non-hazardous-certs/{id}/delete', 'NonHazardousCertController@delete');

// Phytosanitary Certificate Routes
$router->get('/phytosanitary', 'PhytosanitaryController@index');
$router->get('/phytosanitary/create', 'PhytosanitaryController@create');
$router->post('/phytosanitary', 'PhytosanitaryController@store');
$router->get('/phytosanitary/{id}', 'PhytosanitaryController@show');
$router->get('/phytosanitary/{id}/edit', 'PhytosanitaryController@edit');
$router->post('/phytosanitary/{id}', 'PhytosanitaryController@update');
$router->post('/phytosanitary/{id}/status', 'PhytosanitaryController@status');
$router->post('/phytosanitary/{id}/revise', 'PhytosanitaryController@revise');
$router->get('/phytosanitary/{id}/print', 'PhytosanitaryController@print');
$router->post('/phytosanitary/{id}/delete', 'PhytosanitaryController@delete');

// Export Documentation Center Routes
$router->get('/export-documents', 'ExportDocumentController@index');
$router->get('/export-documents/{id}', 'ExportDocumentController@show');
$router->post('/export-documents/{id}/upload', 'ExportDocumentController@upload');
$router->post('/export-documents/{id}/status', 'ExportDocumentController@status');
$router->get('/export-documents/attachment/{id}/download', 'ExportDocumentController@download');
$router->get('/export-documents/attachment/{id}/preview', 'ExportDocumentController@preview');

// Reports Routes
$router->get('/reports', 'ReportController@index');

// Settings Routes
$router->get('/company', 'CompanyController@index');
$router->get('/company/create', 'CompanyController@create');
$router->post('/company', 'CompanyController@store');
$router->get('/company/{id}/edit', 'CompanyController@edit');
$router->post('/company/{id}', 'CompanyController@update');
$router->post('/company/{id}/delete', 'CompanyController@delete');

// Product Master Data Management Routes
$router->get('/settings/master-data/{key}', 'ProductMasterDataController@index');
$router->get('/settings/master-data/{key}/create', 'ProductMasterDataController@create');
$router->post('/settings/master-data/{key}', 'ProductMasterDataController@store');
$router->get('/settings/master-data/{key}/options', 'ProductMasterDataController@options');
$router->get('/settings/master-data/{key}/{id}/details', 'ProductMasterDataController@details');
$router->post('/settings/master-data/{key}/quick-store', 'ProductMasterDataController@quickStore');
$router->get('/settings/master-data/{key}/{id}/edit', 'ProductMasterDataController@edit');
$router->post('/settings/master-data/{key}/{id}', 'ProductMasterDataController@update');
$router->post('/settings/master-data/{key}/{id}/delete', 'ProductMasterDataController@delete');

// Advanced AI Layer & Import/Export Routes
$router->get('/administration/ai-settings', 'ProductMasterDataController@aiSettingsIndex');
$router->post('/administration/ai-settings', 'ProductMasterDataController@aiSettingsUpdate');
$router->get('/api/v1/ai/suggest', 'ProductMasterDataController@suggestAi');
$router->post('/settings/master-data/{key}/import', 'ProductMasterDataController@import');
$router->get('/settings/master-data/{key}/export', 'ProductMasterDataController@export');
$router->get('/settings/product-categories', 'ProductMasterDataController@categoriesIndex');
$router->get('/settings/product-categories/create', 'ProductMasterDataController@categoriesCreate');
$router->post('/settings/product-categories', 'ProductMasterDataController@categoriesStore');
$router->get('/settings/product-categories/{id}/edit', 'ProductMasterDataController@categoriesEdit');
$router->post('/settings/product-categories/{id}', 'ProductMasterDataController@categoriesUpdate');
$router->post('/settings/product-categories/{id}/delete', 'ProductMasterDataController@categoriesDelete');
$router->get('/settings/product-grades', 'ProductMasterDataController@gradesIndex');
$router->get('/settings/product-grades/create', 'ProductMasterDataController@gradesCreate');
$router->post('/settings/product-grades', 'ProductMasterDataController@gradesStore');
$router->get('/settings/product-grades/{id}/edit', 'ProductMasterDataController@gradesEdit');
$router->post('/settings/product-grades/{id}', 'ProductMasterDataController@gradesUpdate');
$router->post('/settings/product-grades/{id}/delete', 'ProductMasterDataController@gradesDelete');
$router->get('/settings/product-origins', 'ProductMasterDataController@originsIndex');
$router->get('/settings/product-origins/create', 'ProductMasterDataController@originsCreate');
$router->post('/settings/product-origins', 'ProductMasterDataController@originsStore');
$router->get('/settings/product-origins/{id}/edit', 'ProductMasterDataController@originsEdit');
$router->post('/settings/product-origins/{id}', 'ProductMasterDataController@originsUpdate');
$router->post('/settings/product-origins/{id}/delete', 'ProductMasterDataController@originsDelete');
$router->get('/settings/hs-codes', 'ProductMasterDataController@hsCodesIndex');
$router->get('/settings/hs-codes/create', 'ProductMasterDataController@hsCodesCreate');
$router->post('/settings/hs-codes', 'ProductMasterDataController@hsCodesStore');
$router->get('/settings/hs-codes/{id}/edit', 'ProductMasterDataController@hsCodesEdit');
$router->post('/settings/hs-codes/{id}', 'ProductMasterDataController@hsCodesUpdate');
$router->post('/settings/hs-codes/{id}/delete', 'ProductMasterDataController@hsCodesDelete');
$router->get('/settings/units', 'ProductMasterDataController@unitsIndex');
$router->get('/settings/units/create', 'ProductMasterDataController@unitsCreate');
$router->post('/settings/units', 'ProductMasterDataController@unitsStore');
$router->get('/settings/units/{id}/edit', 'ProductMasterDataController@unitsEdit');
$router->post('/settings/units/{id}', 'ProductMasterDataController@unitsUpdate');
$router->post('/settings/units/{id}/delete', 'ProductMasterDataController@unitsDelete');
$router->get('/settings/packing-types', 'ProductMasterDataController@packingTypesIndex');
$router->get('/settings/packing-types/create', 'ProductMasterDataController@packingTypesCreate');
$router->post('/settings/packing-types', 'ProductMasterDataController@packingTypesStore');
$router->get('/settings/packing-types/{id}/edit', 'ProductMasterDataController@packingTypesEdit');
$router->post('/settings/packing-types/{id}', 'ProductMasterDataController@packingTypesUpdate');
$router->post('/settings/packing-types/{id}/delete', 'ProductMasterDataController@packingTypesDelete');

$router->get('/settings', 'SettingsController@index');
$router->post('/settings', 'SettingsController@update');

// Print Studio & Company Asset Manager Routes
$router->get('/administration/print-studio', 'PrintTemplateController@index');
$router->get('/administration/print-studio/{id}/edit', 'PrintTemplateController@edit');
$router->post('/administration/print-studio/{id}', 'PrintTemplateController@update');
$router->get('/administration/print-studio/{id}/preview', 'PrintTemplateController@preview');
$router->post('/administration/print-studio/{id}/preview', 'PrintTemplateController@preview');

$router->get('/administration/assets', 'PrintAssetController@index');
$router->post('/administration/assets', 'PrintAssetController@store');
$router->post('/administration/assets/{id}', 'PrintAssetController@update');
$router->post('/administration/assets/{id}/delete', 'PrintAssetController@delete');

// API Routes
$router->get('/api/v1/users', 'Api\UserController@index');
$router->get('/api/v1/users/{id}', 'Api\UserController@show');
$router->post('/api/v1/users', 'Api\UserController@store');
$router->put('/api/v1/users/{id}', 'Api\UserController@update');
$router->delete('/api/v1/users/{id}', 'Api\UserController@delete');