<?php
$quotation = $invoice ?? null;
$action = $action ?? '/commercial-invoices';
$title = $title ?? 'Commercial Invoice';
?>
<?php ob_start(); require APP_ROOT . '/templates/quotations/form.php'; $content = ob_get_clean(); echo str_replace(['/quotations', 'Quotation Number', 'Quotation Header', 'Save Quotation', 'Quotation Date'], ['/commercial-invoices', 'CI Number', 'CI Header', 'Save Commercial Invoice', 'CI Date'], $content); ?>