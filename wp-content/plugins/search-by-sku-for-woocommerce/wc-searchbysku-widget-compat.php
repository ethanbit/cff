<?php

/**
 * A drop in replacement of WC_Admin_Post_Types::product_search()
 */
add_filter('posts_search', 'product_search_sku', 600, 2);
function product_search_sku($search, $wp_query)
{
    global $pagenow, $wpdb, $wp;

    if ((is_admin() && 'edit.php' != $pagenow)
        || !is_search()
        || !isset($wp->query_vars['s'])
        //post_types can also be arrays..
        || (isset($wp->query_vars['post_type']) && 'product' != $wp->query_vars['post_type'])
        || (isset($wp->query_vars['post_type']) && is_array($wp->query_vars['post_type']) && !in_array('product', $wp->query_vars['post_type']))
    ) {
        return $search;
    }
    $search_ids = array();
    $terms = explode(',', $wp->query_vars['s']);

    foreach ($terms as $term) {
        $term = trim($term);
        //Include the search by id if admin area.
        if (is_admin() && is_numeric($term)) {
            $search_ids[] = $term;
        }
        // search for variations with a matching sku and return the parent.

        $sku_to_parent_id = $wpdb->get_col($wpdb->prepare("SELECT p.post_parent as post_id FROM {$wpdb->posts} as p join {$wpdb->postmeta} pm on p.ID = pm.post_id and pm.meta_key='_sku' and pm.meta_value LIKE '%%%s%%' where p.post_parent <> 0 group by p.post_parent", wc_clean($term)));

        //Search for a regular product that matches the sku.
        $sku_to_id = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_sku' AND meta_value LIKE '%%%s%%';", wc_clean($term)));

        $search_ids = array_merge($search_ids, $sku_to_id, $sku_to_parent_id);
    }

    $search_ids = array_unique(array_filter(array_map('absint', $search_ids)));

    if (count($search_ids) > 0) {
        //$search = str_replace('))', ") OR ({$wpdb->posts}.ID IN (" . implode(',', $search_ids) . ")))", $search);

        $search .= " OR {$wpdb->posts}.ID IN (".implode(',', $search_ids).")";
    }
    remove_filters_for_anonymous_class('posts_search', 'WC_Admin_Post_Types', 'product_search', 10);
    return $search;
}
