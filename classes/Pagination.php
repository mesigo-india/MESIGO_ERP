<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Pagination class for handling pagination
 */
class Pagination
{
    private int $total;
    private int $perPage;
    private int $currentPage;
    private int $totalPages;
    
    public function __construct(int $total, int $perPage = 15, int $currentPage = 1)
    {
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = max(1, $currentPage);
        $this->totalPages = (int) ceil($total / $perPage);
    }
    
    /**
     * Get current page
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }
    
    /**
     * Get per page
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }
    
    /**
     * Get total items
     */
    public function getTotal(): int
    {
        return $this->total;
    }
    
    /**
     * Get total pages
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }
    
    /**
     * Get offset
     */
    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }
    
    /**
     * Check if has more pages
     */
    public function hasMore(): bool
    {
        return $this->currentPage < $this->totalPages;
    }
    
    /**
     * Get pagination data
     */
    public function getData(): array
    {
        return [
            'current_page' => $this->currentPage,
            'per_page' => $this->perPage,
            'total' => $this->total,
            'total_pages' => $this->totalPages,
            'has_more' => $this->hasMore(),
            'from' => $this->getOffset() + 1,
            'to' => min($this->currentPage * $this->perPage, $this->total),
        ];
    }
    
    /**
     * Render pagination HTML
     */
    public function render(string $baseUrl): string
    {
        $html = '<nav aria-label="Page navigation"><ul class="pagination">';
        
        // Previous
        if ($this->currentPage > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($this->currentPage - 1) . '">Previous</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
        }
        
        // Pages
        for ($i = 1; $i <= $this->totalPages; $i++) {
            if ($i == $this->currentPage) {
                $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
            }
        }
        
        // Next
        if ($this->currentPage < $this->totalPages) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($this->currentPage + 1) . '">Next</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
        }
        
        $html .= '</ul></nav>';
        
        return $html;
    }
}