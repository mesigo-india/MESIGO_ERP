<?php
declare(strict_types=1);

/**
 * MESIGO ERP - Helper Functions
 */

if (!function_exists('escapeHtml')) {
    /**
     * Escape HTML special characters
     */
    function escapeHtml(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('csrfToken')) {
    /**
     * Generate CSRF token input field
     */
    function csrfToken(): string
    {
        $token = \App\Core\Session::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

if (!function_exists('flashMessage')) {
    /**
     * Get flash message
     */
    function flashMessage(): ?array
    {
        return \App\Core\Response::getFlash();
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format date for display
     */
    function formatDate(string $date, string $format = 'd-m-Y'): string
    {
        return date($format, strtotime($date));
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format currency
     */
    function formatCurrency(float $amount, string $currency = 'INR'): string
    {
        $symbol = match($currency) {
            'INR' => '₹',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => ''
        };
        
        return $symbol . number_format($amount, 2);
    }
}

if (!function_exists('isActive')) {
    /**
     * Check if menu item is active
     */
    function isActive(string $path): bool
    {
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return strpos($currentPath, $path) === 0;
    }
}

if (!function_exists('statusBadge')) {
    /**
     * Get status badge HTML
     */
    function statusBadge(int $status): string
    {
        $badges = [
            0 => '<span class="badge bg-secondary">Inactive</span>',
            1 => '<span class="badge bg-success">Active</span>',
            2 => '<span class="badge bg-warning">Draft</span>',
            3 => '<span class="badge bg-info">Pending</span>',
            4 => '<span class="badge bg-primary">Approved</span>',
            5 => '<span class="badge bg-danger">Rejected</span>',
            6 => '<span class="badge bg-success">Completed</span>',
            7 => '<span class="badge bg-dark">Cancelled</span>',
        ];
        
        return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }
}

if (!function_exists('generateNumber')) {
    /**
     * Generate sequential number
     */
    function generateNumber(string $prefix, int $number, int $padLength = 4): string
    {
        return $prefix . '-' . date('Ymd') . '-' . str_pad((string) $number, $padLength, '0', STR_PAD_LEFT);
    }
}