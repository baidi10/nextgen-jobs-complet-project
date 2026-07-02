<?php
// includes/pagination.php
function renderPagination($currentPage, $totalPages, $baseUrl, $totalItems = null) {
    // Convert parameters to integers and ensure valid values
    $currentPage = max(1, (int)$currentPage); // Ensure page is at least 1
    $totalPages = max(1, (int)$totalPages);  // Ensure total pages is at least 1
    
    if ($totalPages <= 1) return '';
    
    // Properly handle URL parameters
    $parsedUrl = parse_url($baseUrl);
    $baseUrl = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
    
    // Get existing query parameters
    $queryParams = [];
    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $queryParams);
    }
    
    // Remove any existing page parameter to avoid duplication
    if (isset($queryParams['page'])) {
        unset($queryParams['page']);
    }
    
    // Debug current page
    echo "<!-- Pagination Debug: Current page from parameter: $currentPage -->";
    
    // Build URL function for cleaner code
    $buildPageUrl = function($pageNum) use ($baseUrl, $queryParams) {
        $params = array_merge($queryParams, ['page' => $pageNum]);
        return $baseUrl . '?' . http_build_query($params);
    };
    
    echo '<div class="pagination-container">';
    
    // Show total items if provided
    if ($totalItems !== null) {
        echo '<div class="text-center mb-3">';
        echo '<div class="pagination-info">';
        echo '<span class="fw-medium">Showing page ' . $currentPage . ' of ' . $totalPages . '</span>';
        echo ' <span class="text-muted">(' . number_format($totalItems) . ' total results)</span>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '<div class="d-flex justify-content-center">';
    echo '<ul class="pagination">';
    
    // Build the page links array
    $links = [];
    
    // Add middle pages
    for ($i = 1; $i <= $totalPages; $i++) {
        $links[] = $i;
    }

    // Sort and unique the links
    $links = array_unique($links);
    sort($links);
    
    // Show the page numbers/ellipses
    foreach ($links as $link) {
        if ($link === '...') {
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        } else {
            $active = ($link == $currentPage) ? ' active' : '';
            $pageUrl = htmlspecialchars($buildPageUrl($link));
            echo '<li class="page-item' . $active . '">';
            echo '<a class="page-link" href="' . $pageUrl . '" onclick="window.location.href=\'' . $pageUrl . '\'; return false;"' . ($link == $currentPage ? ' aria-current="page"' : '') . '>' . $link . '</a>';
            echo '</li>';
        }
    }
    
    echo '</ul>';
    echo '</div>';
    echo '</div>';
    
    // No additional JavaScript here - we'll manage event handlers in the page JS
}

?>

<!-- Add pagination styles -->
<style>
  /* Styles for the pagination container */
  .pagination-container {
      margin: 2rem 0;
      padding: 1.5rem 1rem;
      background-color: #f8f9fa; /* Light background */
      border-radius: 12px; /* Rounded corners */
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      border: 1px solid #e9ecef;
  }
  .pagination {
    gap: 0.5rem; /* Increased gap */
  }
  .page-item .page-link {
    border-radius: 8px !important; /* More rounded corners */
    padding: 0.75rem 1.25rem; /* Increased padding */
    color: #333; /* Dark text color */
    font-weight: 500;
    border: 1px solid #ced4da; /* Light border */
    transition: all 0.2s ease;
    min-width: 40px;
    text-align: center;
    background-color: #fff; /* White background */
    box-shadow: none !important;
  }
  .page-item.active .page-link {
    background-color: #0d6efd; /* Blue active background */
    border-color: #0d6efd;
    color: white; /* White text */
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(13,110,253,0.35);
  }
   .page-item.active .page-link:focus {
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.25) !important;
   }
  .page-item .page-link:focus {
    box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.25);
    z-index: 3;
  }
  .page-item .page-link:hover {
    background-color: #e9ecef; /* Light grey on hover */
    color: #212529; /* Dark text on hover */
    border-color: #ced4da;
    transform: translateY(-2px);
  }
   .page-item.disabled .page-link {
     color: #6c757d; /* Muted text for disabled links */
     background-color: #fff; /* White background */
     border-color: #ced4da;
     transform: none; /* No transform on disabled hover */
   }
   .pagination-info {
     text-align: center;
     font-size: 0.9rem;
     margin-bottom: 1rem;
     background-color: #f8f9fa; /* Light background */
     padding: 0.5rem 1rem;
     border-radius: 50px;
     display: inline-block;
     box-shadow: 0 1px 3px rgba(0,0,0,0.05);
     color: #555; /* Darker text */
   }
   /* Mobile responsiveness */
   @media (max-width: 576px) {
     .pagination {
       gap: 0.25rem;
     }
     .page-item .page-link {
       padding: 0.5rem 0.7rem;
       font-size: 0.875rem;
     }
     .pagination-info {
       font-size: 0.8rem;
       width: 100%;
     }
     .page-item:nth-child(1), 
     .page-item:nth-child(2),
     .page-item:nth-last-child(1), 
     .page-item:nth-last-child(2) {
      display: flex;
     }
   }
   

   .delete-application-btn:hover {
       background-color: #dc3545; /* Red background on hover */
       color: white; /* White icon/text on hover */
       border-color: #dc3545; /* Red border on hover */
   }
</style>