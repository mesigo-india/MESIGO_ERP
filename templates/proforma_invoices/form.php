<?php
$quotation = $invoice ?? null;
$action = $action ?? '/proforma-invoices';
$title = $title ?? 'Proforma Invoice';
?>
<?php ob_start(); require APP_ROOT . '/templates/quotations/form.php'; $content = ob_get_clean(); echo str_replace(['/quotations', 'Quotation Number', 'Quotation Header', 'Save Quotation', 'Quotation Date'], ['/proforma-invoices', 'PI Number', 'PI Header', 'Save Proforma Invoice', 'PI Date'], $content); ?>