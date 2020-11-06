<?php
/**
 * @version    1.0
 * @package    besa
 * @author     Thembay Team <support@thembay.com>
 * @copyright  Copyright (C) 2019 Thembay.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: https://thembay.com
 */

require_once get_stylesheet_directory() . '/functions-api.php';

add_action('wp_enqueue_scripts', 'besa_child_enqueue_styles', 10000);
function besa_child_enqueue_styles()
{
  $parent_style = 'besa-style';
  wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
  wp_enqueue_style(
    'besa-child-style',
    get_stylesheet_directory_uri() . '/style.css',
    [$parent_style],
    wp_get_theme()->get('Version')
  );

  wp_enqueue_script(
    'custom_js',
    get_stylesheet_directory_uri() . '/js/custom.js'
  );
}

add_action('wp_enqueue_scripts', function () {
  if (class_exists('woocommerce')) {
    wp_dequeue_style('selectWoo');
    wp_deregister_style('selectWoo');
    wp_dequeue_script('selectWoo');
    wp_deregister_script('selectWoo');
  }
});

/**
 * Auto Complete all WooCommerce orders.
 */
add_action('woocommerce_thankyou', 'custom_woocommerce_auto_complete_order');
function custom_woocommerce_auto_complete_order($order_id)
{
  if (!$order_id) {
    return;
  }

  $order = wc_get_order($order_id);
  $order->update_status('completed');
}

// overwrite function in parent theme.
function besa_tbay_autocomplete_suggestions()
{
  check_ajax_referer('search_nonce', 'security');

  $args = [
    'post_status' => 'publish',
    'orderby' => 'relevance',
    'posts_per_page' => -1,
    'ignore_sticky_posts' => 1,
    'suppress_filters' => false,
  ];

  if (!empty($_REQUEST['query'])) {
    $search_keyword = $_REQUEST['query'];
    $args['s'] = sanitize_text_field($search_keyword);
  }

  if (!empty($_REQUEST['search_in'])) {
    $options = $_REQUEST['search_in'];
  }

  // overwrite here
  if ($options == 'undefined') {
    $options = 'all';
  }

  if ($options !== 'post' && class_exists('WooCommerce')) {
    $args['meta_query'] = WC()->query->get_meta_query();
    $args['tax_query'] = WC()->query->get_tax_query();
  }

  if ($options === 'all') {
    $args_sku = [
      'post_type' => 'product',
      'posts_per_page' => -1,
      'meta_query' => [
        [
          'key' => '_sku',
          'value' => trim($search_keyword),
          'compare' => 'like',
        ],
      ],
      'suppress_filters' => 0,
    ];

    $args_variation_sku = [
      'post_type' => 'product_variation',
      'posts_per_page' => -1,
      'meta_query' => [
        [
          'key' => '_sku',
          'value' => trim($search_keyword),
          'compare' => 'like',
        ],
      ],
      'suppress_filters' => 0,
    ];
  }

  if (!empty($_REQUEST['post_type'])) {
    $post_type = strip_tags($_REQUEST['post_type']);
  }

  if (!empty($_REQUEST['number'])) {
    $number = (int) $_REQUEST['number'];
  }

  if (isset($_REQUEST['post_type']) && $_REQUEST['post_type'] != 'all') {
    $args['post_type'] = $_REQUEST['post_type'];
  }

  if (isset($_REQUEST['product_cat']) && !empty($_REQUEST['product_cat'])) {
    if ($args['post_type'] == 'product') {
      $args['tax_query'] = [
        'relation' => 'AND',
        [
          'taxonomy' => 'product_cat',
          'field' => 'slug',
          'terms' => $_REQUEST['product_cat'],
        ],
      ];

      if (version_compare(WC()->version, '2.7.0', '<')) {
        $args['meta_query'] = [
          [
            'key' => '_visibility',
            'value' => ['search', 'visible'],
            'compare' => 'IN',
          ],
        ];
      } else {
        $product_visibility_term_ids = wc_get_product_visibility_term_ids();
        $args['tax_query'][] = [
          'taxonomy' => 'product_visibility',
          'field' => 'term_taxonomy_id',
          'terms' => $product_visibility_term_ids['exclude-from-search'],
          'operator' => 'NOT IN',
        ];
      }
    } else {
      $args['tax_query'] = [
        'relation' => 'AND',
        [
          'taxonomy' => 'category',
          'field' => 'id',
          'terms' => $_REQUEST['product_cat'],
        ],
      ];
    }
  }

  $results = new WP_Query($args);

  if ($options === 'all') {
    $products_sku = get_posts($args_sku);
    $products_variation_sku = get_posts($args_variation_sku);

    $post_ids = $sku_ids = $variation_sku_ids = [];

    if ($results->have_posts()):
      while ($results->have_posts()):
        $results->the_post();

        $post_ids[] = get_the_ID();
      endwhile;
    endif;
    wp_reset_query();

    $variation_sku_ids = wp_list_pluck($products_variation_sku, 'ID');
    $sku_ids = wp_list_pluck($products_sku, 'ID');

    $post_ids = array_merge($post_ids, $sku_ids, $variation_sku_ids);

    $results = new WP_Query([
      'post_type' => 'product',
      'post__in' => $post_ids,
    ]);
  }

  $suggestions = [];

  global $post;

  $count = $results->post_count;

  $view_all = $count - $number > 0 ? true : false;
  $index = 0;
  if ($results->have_posts()) {
    if ($post_type == 'product') {
      $factory = new WC_Product_Factory();
    }

    while ($results->have_posts()) {
      if ($index == $number) {
        break;
      }

      $results->the_post();

      if ($count == 1) {
        $result_text = esc_html__('result found with', 'besa');
      } else {
        $result_text = esc_html__('results found with', 'besa');
      }

      if ($post_type == 'product') {
        $product = $factory->get_product(get_the_ID());
        $suggestions[] = [
          'value' => get_the_title(),
          'link' => get_the_permalink(),
          'price' => $product->get_price_html(),
          'image' => $product->get_image(),
          'result' =>
            '<span class="count">' .
            $count .
            ' </span> ' .
            $result_text .
            ' <span class="keywork">"' .
            $search_keyword .
            '"</span>',
          'view_all' => $view_all,
        ];
      } else {
        $suggestions[] = [
          'value' => get_the_title(),
          'link' => get_the_permalink(),
          'image' => get_the_post_thumbnail(null, 'medium', ''),
          'result' =>
            '<span class="count">' .
            $count .
            ' </span> ' .
            $result_text .
            ' <span class="keywork">"' .
            $search_keyword .
            '"</span>',
          'view_all' => $view_all,
        ];
      }

      $index++;
    }

    wp_reset_postdata();
  } else {
    $suggestions[] = [
      'value' =>
        $post_type == 'product'
          ? esc_html__('No products found.', 'besa')
          : esc_html__('No posts...', 'besa'),
      'no_found' => true,
      'link' => '',
      'view_all' => $view_all,
    ];
  }

  echo json_encode([
    'suggestions' => $suggestions,
  ]);

  die();
}

add_filter('yith_wcwl_remove_from_wishlist_label', 'd_yith_wcwl_remove_from_wishlist_label', 500,1);
function d_yith_wcwl_remove_from_wishlist_label(){
  return '';
}