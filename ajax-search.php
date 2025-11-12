<?php
/**
 * AJAX Search Endpoint
 * Handles live search requests and returns JSON results
 */

// Include WordPress 
require_once('../../../wp-load.php');

// Set content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Check if query parameter exists
if (!isset($_GET['q'])) {
    echo json_encode(['results' => []]);
    exit;
}

// Get the search query
$query = sanitize_text_field($_GET['q'] ?? '');

// Return empty results if query is too short
if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

// Perform the search
$search_args = [
    's' => $query,
    'post_type' => ['post', 'page', 'product'],
    'post_status' => 'publish',
    'posts_per_page' => 8,
    'orderby' => 'relevance'
];

$search_query = new WP_Query($search_args);
$results = [];

if ($search_query->have_posts()) {
    while ($search_query->have_posts()) {
        $search_query->the_post();
        
        $post_type = get_post_type();
        $type_label = '';
        
        switch ($post_type) {
            case 'product':
                $type_label = 'Product';
                $price = get_post_meta(get_the_ID(), '_price', true);
                $formatted_price = $price ? wc_price($price) : '';
                break;
            case 'post':
                $type_label = 'Article';
                break;
            case 'page':
                $type_label = 'Page';
                break;
            default:
                $type_label = ucfirst($post_type);
        }
        
        // Get product image
        $thumbnail_url = '';
        if ($post_type === 'product') {
            // Try to get WooCommerce product image
            $product = wc_get_product(get_the_ID());
            if ($product) {
                $thumbnail_id = $product->get_image_id();
                if ($thumbnail_id) {
                    $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'woocommerce_gallery_thumbnail');
                }
            }
        }
        
        // Fallback to featured image
        if (!$thumbnail_url) {
            $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium');
        }
        
        $results[] = [
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'url' => get_permalink(),
            'excerpt' => wp_trim_words(get_the_excerpt() ?: get_the_content(), 15),
            'type' => $type_label,
            'price' => $formatted_price ?? '',
            'thumbnail' => $thumbnail_url,
            'is_product' => $post_type === 'product'
        ];
    }
}

wp_reset_postdata();

// Return JSON results
echo json_encode([
    'results' => $results,
    'total' => $search_query->found_posts,
    'query' => $query
]);
?>
