<?php
add_action( 'save_post_product', 'wpnotify_woo_product_change', 10, 3 );
add_action( 'untrash_post', 'wpnotify_woo_product_untrash');

add_action( 'delete_post', 'wpnotify_woo_product_delete' );
add_action( 'wp_trash_post', 'wpnotify_woo_product_trash' );

add_action( 'create_product_cat', 'wpnotify_woo_cat_addnew' );
add_action( 'edited_product_cat', 'wpnotify_woo_cat_update' );
add_action( 'delete_product_cat', 'wpnotify_woo_cat_delete' );

function wpnotify_woo_cat_addnew($term_id){
  woo_cat_action($term_id, 'addnew');
}

function wpnotify_woo_cat_update($term_id){
  woo_cat_action($term_id, 'update');
}

function wpnotify_woo_cat_delete($term_id){
  woo_cat_action($term_id, 'delete');
}

function woo_cat_action($term_id, $action){
  global $wpdb;
  $date = date("Y-m-d H:i:s");

  $checkExist = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}notifications_woo_change WHERE object_id = $term_id AND type = 'category'" );
  
  if($checkExist){
    $wpdb->update( 
      $wpdb->prefix.'notifications_woo_change', 
      array( 
        'action' => $action,
        'timestamp' => strtotime($date), 
        'date_time' => $date, 
      ),
      array( 'object_id' => $term_id, 'type' => 'category' )
    );
  }else{
    $wpdb->insert( 
      $wpdb->prefix.'notifications_woo_change', 
      array( 
        'action' => $action, 
        'type' => 'category', 
        'object_id' => $term_id, 
        'timestamp' => strtotime($date), 
        'date_time' => $date, 
      )
    );
  }
}
 
function wpnotify_woo_product_change( $post_id, $post, $update ) {
  if(!empty($_POST) AND $post->post_type == 'product'){
    woo_product_action($post_id, 'addnew');
  }
}

function wpnotify_woo_product_untrash($post_id){
  woo_product_action($post_id, 'publish');
}

function wpnotify_woo_product_delete($post_id){
  woo_product_action($post_id, 'delete');
}

function wpnotify_woo_product_trash($post_id){
  woo_product_action($post_id, 'trash');
}

function woo_product_action($post_id, $action){
  global $wpdb;
  $date = date("Y-m-d H:i:s");
  
  $checkExist = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}notifications_woo_change WHERE object_id = $post_id AND type = 'product'" );

  if($checkExist){
    if($action == 'addnew'){
      $action = 'update';
    }
    $wpdb->update( 
      $wpdb->prefix.'notifications_woo_change', 
      array( 
        'action' => $action,
        'timestamp' => strtotime($date), 
        'date_time' => $date, 
      ),
      array( 'object_id' => $post_id, 'type' => 'product' )
    );
  }else{
    $wpdb->insert( 
      $wpdb->prefix.'notifications_woo_change', 
      array( 
        'action' => $action, 
        'type' => 'product', 
        'object_id' => $post_id, 
        'timestamp' => strtotime($date), 
        'date_time' => $date, 
      )
    );
  }
}