<?php
/**
 * Mobile API
 */
include_once ABSPATH . 'wp-admin/includes/plugin.php';
include_once ABSPATH . 'wp-content/plugins/jwt-authentication-for-wp-rest-api/includes/class-jwt-auth.php';
include_once ABSPATH . 'wp-content/plugins/jwt-authentication-for-wp-rest-api/public/class-jwt-auth-public.php';

add_action('rest_api_init', function () {
  register_rest_route('func', '/register', [
    'methods' => 'POST',
    'callback' => 'api_register',
  ]);

  register_rest_route('func', '/reset_password', [
    'methods' => 'POST',
    'callback' => 'api_reset_password',
  ]);

  register_rest_route('func', '/getwishlist', [
    'methods' => 'GET',
    'callback' => 'api_getwishlist',
  ]);

  register_rest_route('func', '/addwishlist', [
    'methods' => 'POST',
    'callback' => 'api_addwishlist',
  ]);

  register_rest_route('func', '/importwishlist', [
    'methods' => 'POST',
    'callback' => 'api_importWishlist',
  ]);

  register_rest_route('func', '/removeproductwishlist', [
    'methods' => 'POST',
    'callback' => 'api_removewishlist',
  ]);

  // get all orders
  register_rest_route('func', '/orders', [
    'methods' => 'GET',
    'callback' => 'api_orders',
  ]);

  // get holiday
  register_rest_route('func', '/holiday', [
    'methods' => 'GET',
    'callback' => 'api_holiday',
  ]);

  // get userdetail
  register_rest_route('func', '/userdetail', [
    'methods' => 'GET',
    'callback' => 'api_userdetail',
  ]);

  // get product
  register_rest_route('func', '/getproducts', [
    'methods' => 'GET',
    'callback' => 'api_getproducts',
  ]);

  // get categories
  register_rest_route('func', '/getcategories', [
    'methods' => 'POST',
    'callback' => 'api_getcategories',
  ]);

  // get list product in category
  register_rest_route('func', '/getproductcategory', [
    'methods' => 'POST',
    'callback' => 'api_getproductcategory',
  ]);

  // get list products in woo
  register_rest_route('func', '/getallproducts', [
    'methods' => 'GET',
    'callback' => 'api_getallproducts',
  ]);

  register_rest_route('func', '/addshippingaddress', [
    'methods' => 'POST',
    'callback' => 'api_addshippingaddress',
  ]);

  register_rest_route('func', '/addbillingaddress', [
    'methods' => 'POST',
    'callback' => 'api_addbillingaddress',
  ]);

  register_rest_route('func', '/removeshippingaddress', [
    'methods' => 'POST',
    'callback' => 'api_removeshippingaddress',
  ]);

  register_rest_route('func', '/removebillingaddress', [
    'methods' => 'POST',
    'callback' => 'api_removebillingaddress',
  ]);

  register_rest_route('func', '/updateshippingaddress', [
    'methods' => 'POST',
    'callback' => 'api_updateshippingaddress',
  ]);

  register_rest_route('func', '/updatebillingaddress', [
    'methods' => 'POST',
    'callback' => 'api_updatebillingaddress',
  ]);

  register_rest_route('func', '/createorder', [
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'api_createorder',
  ]);

  register_rest_route('func', '/orderdetail', [
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'api_orderdetail',
  ]);

  register_rest_route('func', '/insertdevice', [
    'methods' => 'POST',
    'callback' => 'api_insertdevice',
  ]);

  // update userdetail
  register_rest_route('func', '/updateuser', [
    'methods' => 'POST',
    'callback' => 'api_updateuser',
  ]);

  // check DB if product or category have any change
  register_rest_route('func', '/checkupdate', [
    'methods' => 'POST',
    'callback' => 'api_checkupdate',
  ]);

  register_rest_route('func', '/login2', [
    'methods' => 'POST',
    'callback' => 'api_login2',
  ]);
});

function api_login2($request){
  $username = $request['username'];
  $password = $request['password'];

  $init = new Jwt_Auth();  
  $JAP = new Jwt_Auth_Public('jwt-auth', '1.1.0');
  // run filter first "jwt_auth_token_before_dispatch"
  $token = $JAP->generate_token($request); 
  if(is_wp_error($token)){
    $errDatas = $token->errors;
    $msg = '';
    foreach($errDatas as $k => $errData){
      $msg = strip_tags($errData[0]);
    }
    return wp_send_json(['error' => 1, 'msg' => $msg, 'data' => ''], 200);
  }
  
  $data['user'] = $token;

  // insert device ID
  $userID = $token['user_id'];
  api_insertdevice_2($request, $userID);

  $wishlish = api_getwishlist_2($userID);
  $data['wishlish'] = $wishlish;

  return wp_send_json(['error' => 0, 'msg' => '', 'data' => $data], 200);
  exit();
}

add_filter( 'jwt_auth_token_before_dispatch', 'add_moreinfo_to_token', 30, 2 );
function add_moreinfo_to_token($data, $user){
  $data['user_id'] = $user->data->ID;
  $billingAndShipping = api_userdetail_2($user->data->ID);
  $userData = array_merge($data, $billingAndShipping);
  return $userData;
}


add_filter( 'authenticate', 'custom_authenticate_username_password', 30, 3 );
/**
 * Remove Wordpress filer and write our own with changed error text.
 */
function custom_authenticate_username_password( $user, $username, $password ) {
    if (is_a($user, 'WP_User')){
        return $user;
    }
    
    if (empty($username) || empty($password)) {
        $error = new WP_Error();
        if (empty($username )){
            $error->add('empty_email', __('The username or email field is empty.'));
        }

        if (empty($password)){
            $error->add('empty_password', __( 'The password field is empty' ));
        }
        return $error;
    }

    $user = get_user_by( 'login', $username );
    if (!$user){
        return new WP_Error( 'invalid_username', sprintf( __( 'Invalid username or email address.' ), wp_lostpassword_url()));
    }
    $user = apply_filters( 'wp_authenticate_user', $user, $password );
    if (is_wp_error($user)){
        return $user;
    }
    if (!wp_check_password( $password, $user->user_pass, $user->ID )){
        return new WP_Error( 'incorrect_password', sprintf( __( 'The password you\'ve entered is incorrect.' ),
        $username, wp_lostpassword_url() ) );
    }
    return $user;
}

function api_getproducts()
{
  setupWooCommerce();
  $api = WC()->api->WC_API_Products;
  $products = $api->get_product(3054);
  echo '<pre>';
  print_r($products);
  echo '</pre>' . __FILE__ . ': ' . __LINE__ . '';
}

function api_register(WP_REST_Request $request)
{
  $error = 0;
  $msg = '';

  $user_id = wp_insert_user([
    'user_login' => $request['username'],
    'user_pass' => $request['password'],
    'user_email' => $request['email'],
    'first_name' => $request['first_name'],
    'last_name' => $request['last_name'],
    'display_name' => $request['name'],
  ]);

  if (!is_wp_error($user_id)) {
    $msg = 'Register success.';
  } else {
    $error = 1;
    $msg = 'Registration unsuccessful. Please try again.';
  }

  echo json_encode(['error' => $error, 'msg' => $msg]);
  exit();
}

function api_reset_password(WP_REST_Request $request)
{
  $newPass = $request['new_password'];
  if ($newPass == '') {
    $msg = 'Please enter new password.';
    return wp_send_json(['error' => 1, 'msg' => $msg], 200);
  }

  $currentuserid_fromjwt = get_current_user_id();
  $user = wp_get_current_user();
  //echo "<pre>"; print_r($user); echo "</pre>".__FILE__.": ".__LINE__."";
  reset_password($user, $newPass);

  $msg =
    'Your password have been reset, you can use new password to login from now.';
  return wp_send_json(['error' => 0, 'msg' => $msg], 200);
}

function api_getwishlist(WP_REST_Request $request)
{
  global $wpdb;
  $currentUserId_fromjwt = get_current_user_id();

  $msg = '';
  $err = 0;

  $query =
    "SELECT prod_id, quantity FROM {$wpdb->prefix}yith_wcwl WHERE user_id = " .
    $currentUserId_fromjwt;
  $results = $wpdb->get_results($wpdb->prepare($query));

  $data = [];
  foreach ($results as $result) {
    $product = wc_get_product($result->prod_id);
    if (is_object($product)) {
      $featured_img_url = get_the_post_thumbnail_url($product->get_image_id());
      $arr = [
        'id' => $product->get_id(),
        'name' => $product->get_title(),
        'slug' => $product->get_slug(),
        'status' => $product->get_status(),
        'sku' => $product->get_sku(),
        'src' => $featured_img_url,
      ];
      $data[] = $arr;
    }
  }
  return wp_send_json(['error' => $err, 'msg' => $msg, 'data' => $data], 200);
}

function api_getwishlist_2($userID)
{
  global $wpdb;
  $currentUserId_fromjwt = $userID;

  $msg = '';
  $err = 0;

  $query =
    "SELECT prod_id, quantity FROM {$wpdb->prefix}yith_wcwl WHERE user_id = " .
    $currentUserId_fromjwt;
  $results = $wpdb->get_results($wpdb->prepare($query));

  $data = [];
  foreach ($results as $result) {
    $product = wc_get_product($result->prod_id);
    if (is_object($product)) {
      $featured_img_url = get_the_post_thumbnail_url($product->get_image_id());
      $arr = [
        'id' => $product->get_id(),
        'name' => $product->get_title(),
        'slug' => $product->get_slug(),
        'status' => $product->get_status(),
        'sku' => $product->get_sku(),
        'src' => $featured_img_url,
      ];
      $data[] = $arr;
    }
  }
  return $data;
}

function api_addwishlist(WP_REST_Request $request)
{
  // $user = wp_get_current_user();
  // echo "<pre>"; print_r($user); echo "</pre>".__FILE__.": ".__LINE__."";

  $msg = '';
  $err = 0;
  $msg_success = 'Added product to wishlist.';
  $productId = $request['product_id'];
  if ($productId == '') {
    return wp_send_json(
      ['error' => 1, 'msg' => 'Please enter Product ID'],
      200
    );
  }

  global $wpdb;
  $currentUserId_fromjwt = get_current_user_id();
  $user_count = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->prefix}yith_wcwl_lists WHERE user_id = " .
      $currentUserId_fromjwt
  );

  if ($user_count) {
    $checkProduct_count = $wpdb->get_var(
      "SELECT COUNT(*) FROM {$wpdb->prefix}yith_wcwl WHERE prod_id = " .
        $productId .
        ' AND user_id = ' .
        $currentUserId_fromjwt
    );

    if (!$checkProduct_count) {
      $wishListId = $wpdb->get_row(
        "SELECT ID FROM {$wpdb->prefix}yith_wcwl_lists WHERE user_id = " .
          $currentUserId_fromjwt
      );

      $data = [
        'prod_id' => $productId,
        'quantity' => 1,
        'user_id' => $currentUserId_fromjwt,
        'wishlist_id' => $wishListId->ID,
        'original_currency' => 'AUD',
      ];
      $wpdb->insert("{$wpdb->prefix}yith_wcwl", $data);

      return wp_send_json(['error' => 0, 'msg' => $msg_success], 200);
    } else {
      return wp_send_json(
        ['error' => 1, 'msg' => 'This product is ready on Wishlist'],
        200
      );
    }
  } else {
    $listAlpha =
      'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $token = substr(str_shuffle($listAlpha), 0, 12);
    $data = [
      'user_id' => $currentUserId_fromjwt,
      'wishlist_slug' => '',
      'wishlist_token' => $token,
      'wishlist_privacy' => 0,
      'is_default' => 1,
      'dateadded' => date('Y-m-d H:i:s'),
    ];
    $wpdb->insert("{$wpdb->prefix}yith_wcwl_lists", $data);

    $data = [
      'prod_id' => $productId,
      'quantity' => 1,
      'user_id' => $currentUserId_fromjwt,
      'wishlist_id' => $wpdb->insert_id,
      'original_currency' => 'AUD',
    ];
    $wpdb->insert("{$wpdb->prefix}yith_wcwl", $data);

    return wp_send_json(['error' => 0, 'msg' => $msg_success], 200);
  }

  $msg = 'have an error when add product to wishlist, pls try again';
  $err = 1;

  return wp_send_json(['error' => $err, 'msg' => $msg], 200);
}

function api_importWishlist(WP_REST_Request $request)
{
  // $user = wp_get_current_user();
  // echo "<pre>"; print_r($request->get_params()); echo "</pre>".__FILE__.": ".__LINE__."";

  $msg = '';
  $err = 0;
  $msg_success = 'Added product to wishlist.';
  $productId = $request['product_id'];
  $userId = $request['user_id'];
  if ($productId == '' or $userId == '') {
    return wp_send_json(
      ['error' => 1, 'msg' => 'Please enter Product ID'],
      200
    );
  }

  global $wpdb;
  $currentUserId_fromjwt = $userId;
  $user_count = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->prefix}yith_wcwl_lists WHERE user_id = " .
      $currentUserId_fromjwt
  );

  if ($user_count) {
    $checkProduct_count = $wpdb->get_var(
      "SELECT COUNT(*) FROM {$wpdb->prefix}yith_wcwl WHERE prod_id = " .
        $productId
    );

    if (!$checkProduct_count) {
      $wishListId = $wpdb->get_row(
        "SELECT ID FROM {$wpdb->prefix}yith_wcwl_lists WHERE user_id = " .
          $currentUserId_fromjwt
      );

      $data = [
        'prod_id' => $productId,
        'quantity' => 1,
        'user_id' => $currentUserId_fromjwt,
        'wishlist_id' => $wishListId->ID,
        'original_currency' => 'AUD',
      ];
      $wpdb->insert("{$wpdb->prefix}yith_wcwl", $data);

      return wp_send_json(['error' => 0, 'msg' => $msg_success], 200);
    } else {
      return wp_send_json(
        ['error' => 1, 'msg' => 'This product is ready on Wishlist'],
        200
      );
    }
  } else {
    $listAlpha =
      'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $token = substr(str_shuffle($listAlpha), 0, 12);
    $data = [
      'user_id' => $currentUserId_fromjwt,
      'wishlist_slug' => '',
      'wishlist_token' => $token,
      'wishlist_privacy' => 0,
      'is_default' => 1,
      'dateadded' => date('Y-m-d H:i:s'),
    ];
    $wpdb->insert("{$wpdb->prefix}yith_wcwl_lists", $data);

    $data = [
      'prod_id' => $productId,
      'quantity' => 1,
      'user_id' => $currentUserId_fromjwt,
      'wishlist_id' => $wpdb->insert_id,
      'original_currency' => 'AUD',
    ];
    $wpdb->insert("{$wpdb->prefix}yith_wcwl", $data);

    return wp_send_json(['error' => 0, 'msg' => $msg_success], 200);
  }

  $msg = 'have an error when add product to wishlist, pls try again';
  $err = 1;

  return wp_send_json(['error' => $err, 'msg' => $msg], 200);
}

function api_removewishlist(WP_REST_Request $request)
{
  $productId = $request['product_id'];
  if ($productId == '') {
    return wp_send_json(
      ['error' => 1, 'msg' => 'Please enter Product ID'],
      200
    );
  }

  global $wpdb;
  $currentUserId_fromjwt = get_current_user_id();
  $checkProduct_count = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->prefix}yith_wcwl WHERE prod_id = " .
      $productId
  );

  if ($checkProduct_count) {
    $wishListId = $wpdb->get_row(
      "SELECT ID FROM {$wpdb->prefix}yith_wcwl_lists WHERE user_id = " .
        $currentUserId_fromjwt
    );

    $where = [
      'prod_id' => $productId,
      'user_id' => $currentUserId_fromjwt,
    ];
    $wpdb->delete("{$wpdb->prefix}yith_wcwl", $where);

    return wp_send_json(['error' => 0, 'msg' => 'removed product.'], 200);
  } else {
    return wp_send_json(
      ['error' => 1, 'msg' => 'Product not exist on Wishlist'],
      200
    );
  }
}

function api_orders(WP_REST_Request $request)
{
  // Get all customer orders
  $currentUserId_fromjwt = get_current_user_id();
  $args = [
    'numberposts' => -1,
    'meta_key' => '_customer_user',
    'orderby' => 'date',
    'order' => 'DESC',
    'meta_value' => $currentUserId_fromjwt,
    'post_type' => wc_get_order_types(),
    'post_status' => [
      'wc-processing',
      'wc-pending',
      'wc-on-hold',
      'wc-completed',
      'wc-cancelled',
      'wc-refunded',
      'wc-failed',
    ],
  ];

  $query = new WP_Query($args);
  $customer_orders = $query->get_posts();

  $Order_Array = [];
  foreach ($customer_orders as $customer_order) {
    $orderq = wc_get_order($customer_order->ID);
    // $Order_Array[] = [
    //   'ID' => $orderq->get_id(),
    //   'Value' => $orderq->get_total(),
    //   'Date' => $orderq->get_date_created()->date_i18n('Y-m-d'),
    // ];
    $tmpData = $orderq->get_data();
    $tmpData['order_date_created'] = $orderq
      ->get_date_created()
      ->date_i18n('Y-m-d');
    $tmpData['date_modified'] = $orderq
      ->get_date_modified()
      ->date_i18n('Y-m-d');
    $products = [];
    foreach ($orderq->get_items() as $item_key => $item) {
      $tmpProduct = [];
      $product = $item->get_product();
      if(!empty($product)){
        $featured_img_url = get_the_post_thumbnail_url($item->get_product_id());
        $tmpProduct['product_id'] = $item->get_product_id();
        $tmpProduct['sku'] = $product->get_sku();
        $tmpProduct['name'] = $product->get_name();
        $tmpProduct['slug'] = $product->get_slug();
        $tmpProduct['src'] = $featured_img_url;
        $tmpProduct['quantity'] = $item->get_quantity();
      }
      if(count($tmpProduct)){
        $products[] = $tmpProduct;
      }
    }

    unset($tmpData['line_items']);
    if(count($products)){
      $tmpData['line_items'] = $products;
    }else{
      $tmpData['line_items'] = [];
    }
    
    unset($tmpData['parent_id']);
    unset($tmpData['currency']);
    unset($tmpData['version']);
    unset($tmpData['date_created']);
    //unset($tmpData['date_modified']);
    unset($tmpData['prices_include_tax']);
    unset($tmpData['meta_data']);
    unset($tmpData['tax_lines']);
    unset($tmpData['shipping_lines']);
    unset($tmpData['fee_lines']);
    unset($tmpData['coupon_lines']);
    unset($tmpData['customer_ip_address']);
    unset($tmpData['customer_user_agent']);
    unset($tmpData['created_via']);
    unset($tmpData['date_paid']);
    unset($tmpData['date_completed']);
    unset($tmpData['date_completed']);
    unset($tmpData['cart_hash']);
    unset($tmpData['transaction_id']);
    unset($tmpData['number']);
    unset($tmpData['discount_total']);
    unset($tmpData['discount_tax']);
    unset($tmpData['shipping_total']);
    unset($tmpData['shipping_tax']);
    unset($tmpData['cart_tax']);
    unset($tmpData['total_tax']);
    unset($tmpData['order_key']);

    $Order_Array[] = $tmpData;
  }
  $data = [
    'error' => 0,
    'msg' => '',
    'total' => count($Order_Array),
    'data' => $Order_Array,
  ];
  return wp_send_json($data, 200);
}

function api_holiday()
{
  $args = [
    'post_type' => 'holiday',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
  ];

  $loop = new WP_Query($args);
  $holiday = [];
  $holiday['msg'] = '';
  $holiday['err'] = '0';
  while ($loop->have_posts()):
    $loop->the_post();
    $data[] = ['title' => get_the_title(), 'date' => get_field('date')];
  endwhile;
  $holiday['data'] = $data;

  wp_reset_postdata();

  wp_send_json($holiday, 200);
  exit();
}

function api_userdetail()
{
  global $wpdb;
  $currentuserid_fromjwt = get_current_user_id();
  if (!$currentuserid_fromjwt) {
    wp_send_json(
      ['msg' => 'Please login first', 'err' => 1, 'data' => ''],
      200
    );
  }

  $customer = new WC_Customer($currentuserid_fromjwt);
  $data = [];
  $data['msg'] = '';
  $data['err'] = '0';
  $data['data'] = '';

  $user = wp_get_current_user();
  $detail['id'] = $user->ID;
  $detail['display_name'] = $user->display_name;
  $detail['user_email'] = $user->user_email;
  $detail['user_email'] = $user->user_email;

  $wooShipping = $customer->shipping;
  $wooShipping['id'] = 0;

  $wooBilling = $customer->billing;
  $wooBilling['id'] = 0;

  $shippingArr[] = $wooShipping;
  $shippingList = $wpdb->get_results("
    SELECT id, userdata 
    FROM {$wpdb->prefix}ocwma_billingadress
    WHERE userid = $currentuserid_fromjwt AND type = 'shipping'
  ");
  foreach ($shippingList as $s) {
    $shippingData = unserialize($s->userdata);
    $shippingData['id'] = intval($s->id);

    $newShippingData = [];
    foreach ($shippingData as $k => $shipping) {
      $newKey = str_replace('shipping_', '', $k);
      $newShippingData[$newKey] = $shipping;
    }

    $shippingArr[] = $newShippingData;
  }

  $billingArr[] = $wooBilling;
  $billingList = $wpdb->get_results("
    SELECT id, userdata 
    FROM {$wpdb->prefix}ocwma_billingadress
    WHERE userid = $currentuserid_fromjwt AND type = 'billing'
  ");
  foreach ($billingList as $s) {
    $billingData = unserialize($s->userdata);
    $billingData['id'] = intval($s->id);
    $newBillingData = [];
    foreach ($billingData as $k => $billing) {
      $newKey = str_replace('billing_', '', $k);
      $newBillingData[$newKey] = $billing;
    }

    $billingArr[] = $newBillingData;
  }

  $detail['billing'] = $billingArr;
  $detail['shipping'] = $shippingArr;

  $data['data'] = $detail;

  wp_send_json($data, 200);
  exit();
}

function api_userdetail_2($userID)
{
  global $wpdb;
  $currentuserid_fromjwt = $userID;

  if (!$userID) {
    wp_send_json(
      ['msg' => 'Please login first', 'err' => 1, 'data' => ''],
      200
    );
  }

  $customer = new WC_Customer($currentuserid_fromjwt);
  $data = [];
  $data['msg'] = '';
  $data['err'] = '0';
  $data['data'] = '';

  $user = get_user_by('ID', $currentuserid_fromjwt);

  //$detail['id'] = $user->ID;
  //$detail['display_name'] = $user->data->display_name;
  //$detail['user_email'] = $user->data->user_email;

  $wooShipping = $customer->shipping;
  $wooShipping['id'] = 0;

  $wooBilling = $customer->billing;
  $wooBilling['id'] = 0;

  $shippingArr[] = $wooShipping;
  $shippingList = $wpdb->get_results("
    SELECT id, userdata 
    FROM {$wpdb->prefix}ocwma_billingadress
    WHERE userid = $currentuserid_fromjwt AND type = 'shipping'
  ");
  foreach ($shippingList as $s) {
    $shippingData = unserialize($s->userdata);
    $shippingData['id'] = intval($s->id);

    $newShippingData = [];
    foreach ($shippingData as $k => $shipping) {
      $newKey = str_replace('shipping_', '', $k);
      $newShippingData[$newKey] = $shipping;
    }

    $shippingArr[] = $newShippingData;
  }

  $billingArr[] = $wooBilling;
  $billingList = $wpdb->get_results("
    SELECT id, userdata 
    FROM {$wpdb->prefix}ocwma_billingadress
    WHERE userid = $currentuserid_fromjwt AND type = 'billing'
  ");
  foreach ($billingList as $s) {
    $billingData = unserialize($s->userdata);
    $billingData['id'] = intval($s->id);
    $newBillingData = [];
    foreach ($billingData as $k => $billing) {
      $newKey = str_replace('billing_', '', $k);
      $newBillingData[$newKey] = $billing;
    }

    $billingArr[] = $newBillingData;
  }

  $detail['billing'] = $billingArr;
  $detail['shipping'] = $shippingArr;

  return $detail;
  exit();
}

function api_getcategories()
{
  $page = $_POST['page'];
  $per_page = $_POST['per_page'];
  $order = $_POST['order'];
  $orderby = $_POST['orderby'];
  $params = [];

  if ($per_page > 100) {
    wp_send_json(
      [
        'msg' => 'per_page must be between 1 (inclusive) and 100 (inclusive)',
        'err' => 1,
        'data' => '',
      ],
      200
    );
    exit();
  }

  if (!empty($page)) {
    $params[] = 'page=' . $page;
  }
  if (!empty($per_page)) {
    $params[] = 'per_page=' . $per_page;
  }
  if (!empty($order)) {
    $params[] = 'order=' . $order;
  }
  if (!empty($orderby)) {
    $params[] = 'orderby=' . $orderby;
  }

  if (!empty($params)) {
    $query = implode('&', $params);
  }

  $data = [];
  $data['msg'] = '';
  $data['err'] = '0';
  $data['data'] = '';

  $consumer_key = WOO_CONSUMER_KEY;
  $consumer_secret = WOO_CONSUMER_SECRET;
  $WOO_API_URL =
    get_home_url() .
    // 'https://test.cityfinefoods.com.au' .
    "/wp-json/wc/v3/products/categories?consumer_key={$consumer_key}&consumer_secret=" .
    $consumer_secret;

  $categories = json_decode(file_get_contents($WOO_API_URL . '&' . $query));
  $newCategories = [];
  foreach ($categories as $category) {
    $category->src = $category->image->src;
    unset($category->description);
    unset($category->display);
    unset($category->image);
    unset($category->menu_order);
    unset($category->_links);
    $newCategories[] = $category;
  }
  $data['data'] = $newCategories;

  $totalCat = [];
  $taxonomy = 'product_cat';
  $empty = 0;

  $args = [
    'taxonomy' => $taxonomy,
    'hide_empty' => $empty,
  ];
  $all_categories = get_categories($args);
  foreach ($all_categories as $cat) {
    $totalCat[$cat->term_id] = $cat;
    if ($cat->category_parent == 0) {
      $category_id = $cat->term_id;
      $args2 = [
        'taxonomy' => $taxonomy,
        'child_of' => 0,
        'parent' => $category_id,
        'hide_empty' => $empty,
      ];
      $sub_cats = get_categories($args2);
      if ($sub_cats) {
        foreach ($sub_cats as $sub_category) {
          $totalCat[$sub_category->term_id] = $sub_category;
        }
      }
    }
  }

  $data['total'] = count($totalCat);

  wp_send_json($data, 200);
}

function api_getproductcategory()
{
  $data = [];
  $data['msg'] = '';
  $data['err'] = '0';
  $data['data'] = '';

  $params[] = 'status=publish';

  $page = $_POST['page'];
  $per_page = $_POST['per_page'];
  $order = $_POST['order'];
  $orderby = $_POST['orderby'];
  $category = $_POST['category'];
  $search = $_POST['search'];
  $sku = $_POST['sku'];

  if ($per_page > 100) {
    wp_send_json(
      [
        'msg' => 'per_page must be between 1 (inclusive) and 100 (inclusive)',
        'err' => 1,
        'data' => '',
      ],
      200
    );
    exit();
  }

  if (!empty($page)) {
    $params[] = 'page=' . $page;
  }
  if (!empty($per_page)) {
    $params[] = 'per_page=' . $per_page;
  }
  if (!empty($order)) {
    $params[] = 'order=' . $order;
  }
  if (!empty($orderby)) {
    $params[] = 'orderby=' . $orderby;
  }
  if (!empty($category)) {
    $params[] = 'category=' . $category;
  }

  if (!empty($params)) {
    $query = implode('&', $params);
  }

  $WOO_API_URL =
    get_home_url() .
    //'https://test.cityfinefoods.com.au' .
    '/wp-json/wc/v3/products?consumer_key=' .
    WOO_CONSUMER_KEY .
    '&consumer_secret=' .
    WOO_CONSUMER_SECRET;

    $product2 = [];
  if(!empty($search)){
    $params['search'] = 'search=' . $search;
    $query2 = implode('&', $params);
    $productsBySearchText = json_decode(file_get_contents($WOO_API_URL . '&' . $query2));
    foreach($productsBySearchText as $p){
      $product2[$p->id] = $p;
    }
    
    $params['search'] = 'sku=' . $search;
    $query2 = implode('&', $params);
    $productsBySKU = json_decode(file_get_contents($WOO_API_URL . '&' . $query2));
    foreach($productsBySKU as $p){
      $product2[$p->id] = $p;
    }

    $products = $product2;
  }else{
    $products = json_decode(file_get_contents($WOO_API_URL . '&' . $query));
  }
  

  // echo '<pre>';
  // print_r($products);
  // echo '</pre>' . __FILE__ . ': ' . __LINE__ . '';
  $newProducts = [];
  foreach ($products as $product) {
    $images = '';
    if (!empty($product->images)) {
      $images = $product->images[0]->src;
    }
    $newProducts[] = [
      'id' => $product->id,
      'name' => $product->name,
      'slug' => $product->slug,
      'type' => $product->type,
      'sku' => $product->sku,
      'src' => $images,
    ];
  }
  $data['data'] = $newProducts;

  $args = [
    'post_status' => 'publish',
    'post_type' => 'product',
    'tax_query' => [
      [
        'taxonomy' => 'product_cat',
        'field' => 'term_id',
        'terms' => $category,
      ],
    ],
  ];
  if ($search != '') {
    $args['s'] = $search;
  }
  $query = new WP_Query($args);

  $data['total'] = $query->found_posts;
  wp_send_json($data, 200);
}

function checkExistsPlugin()
{
  if (
    !is_plugin_active(
      'multiple-shipping-address-woocommerce/oc-woo-multiple-address.php'
    )
  ) {
    wp_send_json(
      [
        'msg' => 'Have an error. Pls contact admin for this problem.',
        'err' => 1,
        'data' => '',
      ],
      200
    );
    return false;
  } else {
    return true;
  }
}

function api_addshippingaddress()
{
  global $wpdb;
  $currentuserid_fromjwt = get_current_user_id();
  if (!$currentuserid_fromjwt) {
    wp_send_json(
      ['msg' => 'Please login first', 'err' => 1, 'data' => ''],
      200
    );
  }
  checkExistsPlugin();

  $shippingAddress = serialize($_POST);
  $wpdb->insert($wpdb->prefix . 'ocwma_billingadress', [
    'userid' => $currentuserid_fromjwt,
    'userdata' => $shippingAddress,
    'type' => 'shipping',
    'default' => $_POST['default'],
  ]);

  wp_send_json(
    [
      'msg' => 'Added Shipping Address.',
      'err' => 0,
      'data' => '',
    ],
    200
  );
}

function api_addbillingaddress()
{
  global $wpdb;
  $currentuserid_fromjwt = get_current_user_id();
  if (!$currentuserid_fromjwt) {
    wp_send_json(
      ['msg' => 'Please login first', 'err' => 1, 'data' => ''],
      200
    );
  }
  checkExistsPlugin();

  $billingAddress = serialize($_POST);
  $wpdb->insert($wpdb->prefix . 'ocwma_billingadress', [
    'userid' => $currentuserid_fromjwt,
    'userdata' => $billingAddress,
    'type' => 'billing',
    'default' => $_POST['default'],
  ]);

  wp_send_json(
    [
      'msg' => 'Added Billing Address.',
      'err' => 0,
      'data' => '',
    ],
    200
  );
}

function api_removeshippingaddress()
{
  global $wpdb;
  $currentuserid_fromjwt = get_current_user_id();
  if (!$currentuserid_fromjwt) {
    wp_send_json(
      ['msg' => 'Please login first', 'err' => 1, 'data' => ''],
      200
    );
  }
  checkExistsPlugin();

  $shippingID = $_POST['shipping_id'];
  $wpdb->delete($wpdb->prefix . 'ocwma_billingadress', [
    'id' => $shippingID,
    'userid' => $currentuserid_fromjwt,
  ]);

  wp_send_json(
    [
      'msg' => 'Removed Shipping Address.',
      'err' => 0,
      'data' => '',
    ],
    200
  );
}

function api_removebillingaddress()
{
  global $wpdb;
  $currentuserid_fromjwt = get_current_user_id();
  if (!$currentuserid_fromjwt) {
    wp_send_json(
      ['msg' => 'Please login first', 'err' => 1, 'data' => ''],
      200
    );
  }

  checkExistsPlugin();

  $billingID = $_POST['billing_id'];
  $wpdb->delete($wpdb->prefix . 'ocwma_billingadress', [
    'id' => $billingID,
    'userid' => $currentuserid_fromjwt,
  ]);

  wp_send_json(
    [
      'msg' => 'Removed Billing Address.',
      'err' => 0,
      'data' => '',
    ],
    200
  );
}

function api_updateshippingaddress()
{
  global $wpdb;
  $currentuserid_fromjwt = get_current_user_id();
  if (!$currentuserid_fromjwt) {
    wp_send_json(
      ['msg' => 'Please login first', 'err' => 1, 'data' => ''],
      200
    );
  }

  checkExistsPlugin();

  if ($_POST['shipping_id']) {
    $shippingAddress = serialize($_POST);
    $wpdb->update(
      $wpdb->prefix . 'ocwma_billingadress',
      [
        'userdata' => $shippingAddress,
        'default' => $_POST['default'],
      ],
      ['id' => $_POST['shipping_id']]
    );
  } else {
    foreach ($_POST as $k => $v) {
      if ($k != 'shipping_id') {
        $checkShipping = strpos($k, 'shipping');
        if ($checkShipping !== false) {
          update_user_meta($currentuserid_fromjwt, $k, $v);
        }
      }
    }
  }
  wp_send_json(['msg' => 'Updated your info', 'err' => 0, 'data' => ''], 200);
  exit();
}

function api_updatebillingaddress()
{
  global $wpdb;
  $currentuserid_fromjwt = get_current_user_id();
  if (!$currentuserid_fromjwt) {
    wp_send_json(
      ['msg' => 'Please login first', 'err' => 1, 'data' => ''],
      200
    );
  }

  checkExistsPlugin();

  if ($_POST['billing_id']) {
    $shippingAddress = serialize($_POST);
    $wpdb->update(
      $wpdb->prefix . 'ocwma_billingadress',
      [
        'userdata' => $shippingAddress,
        'default' => $_POST['default'],
      ],
      ['id' => $_POST['billing_id']]
    );
  } else {
    foreach ($_POST as $k => $v) {
      if ($k != 'billing_id') {
        $checkShipping = strpos($k, 'billing');
        if ($checkShipping !== false) {
          update_user_meta($currentuserid_fromjwt, $k, $v);
        }
      }
    }
  }
  wp_send_json(['msg' => 'Updated your info', 'err' => 0, 'data' => ''], 200);
  exit();
}

function api_createorder($request)
{
  global $woocommerce;
  $currentuserid_fromjwt = get_current_user_id();
  if (!$currentuserid_fromjwt) {
    wp_send_json(
      ['msg' => 'Please login first', 'err' => 1, 'data' => ''],
      200
    );
  }

  $billingAddress = $request['billing'];
  $shippingAddress = $request['shipping'];
  $deliveryDate = $request['delivery_date'];

  // Now we create the order
  $arg = [
    'customer_id' => $currentuserid_fromjwt,
    'customer_note' => $request['customer_note'],
  ];
  $order = wc_create_order($arg);

  $products = $request['line_items'];

  // The add_product() function below is located in /plugins/woocommerce/includes/abstracts/abstract_wc_order.php
  foreach ($products as $product) {
    $checkProduct = wc_get_product($product['product_id']);
    if (is_object($checkProduct)) {
      $order->add_product($checkProduct, $product['quantity']); // This is an existing SIMPLE product
    }
  }
  $order->set_address($shippingAddress, 'shipping'); // Add shipping address
  $order->set_address($billingAddress, 'billing'); // Add billing address
  $payment_gateways = WC()->payment_gateways->payment_gateways();
  $order->set_payment_method($payment_gateways['bacs']);

  $order->calculate_totals(true); //setting true included tax
  //Set order status to processing
  $order->set_status('processing');
  $order->save();

  if (is_numeric($deliveryDate)) {
    update_post_meta(
      $order->id,
      'delivery_date',
      date('d/m/Y', $deliveryDate)
    );
  }
  wp_send_json(
    ['msg' => 'Created order.', 'err' => 0, 'data' => $order->id],
    200
  );
}

function api_orderdetail($request)
{
  $currentuserid_fromjwt = get_current_user_id();
  if (!$currentuserid_fromjwt or !$request['order_id']) {
    wp_send_json(
      [
        'msg' => 'Please login first and give Order ID',
        'err' => 1,
        'data' => '',
      ],
      200
    );
  }
  // Get an instance of the WC_Order object
  $order = wc_get_order($request['order_id']);

  $order_data = $order->get_data(); // The Order data
  $oderDetail = [
    'status' => $order_data['status'],
    'currency' => $order_data['currency'],
    'date_created' => $order_data['date_created'],
    'total' => $order_data['total'],
    'order_key' => $order_data['order_key'],
    'billing' => $order_data['billing'],
    'shipping' => $order_data['shipping'],
    'payment_method' => $order_data['payment_method'],
    'payment_method_title' => $order_data['payment_method_title'],
    'customer_note' => $order_data['customer_note'],
  ];

  // $order_id = $order_data['id'];
  // $order_parent_id = $order_data['parent_id'];
  // $order_status = $order_data['status'];
  // $order_currency = $order_data['currency'];
  // $order_version = $order_data['version'];
  // $order_payment_method = $order_data['payment_method'];
  // $order_payment_method_title = $order_data['payment_method_title'];
  // $order_payment_method = $order_data['payment_method'];
  // $order_payment_method = $order_data['payment_method'];

  // ## Creation and modified WC_DateTime Object date string ##

  // // Using a formated date ( with php date() function as method)
  // $order_date_created = $order_data['date_created']->date('Y-m-d H:i:s');
  // $order_date_modified = $order_data['date_modified']->date('Y-m-d H:i:s');

  // // Using a timestamp ( with php getTimestamp() function as method)
  // $order_timestamp_created = $order_data['date_created']->getTimestamp();
  // $order_timestamp_modified = $order_data['date_modified']->getTimestamp();

  // $order_discount_total = $order_data['discount_total'];
  // $order_discount_tax = $order_data['discount_tax'];
  // $order_shipping_total = $order_data['shipping_total'];
  // $order_shipping_tax = $order_data['shipping_tax'];
  // $order_total = $order_data['total'];
  // $order_total_tax = $order_data['total_tax'];
  // $order_customer_id = $order_data['customer_id']; // ... and so on

  // ## BILLING INFORMATION:

  // $order_billing_first_name = $order_data['billing']['first_name'];
  // $order_billing_last_name = $order_data['billing']['last_name'];
  // $order_billing_company = $order_data['billing']['company'];
  // $order_billing_address_1 = $order_data['billing']['address_1'];
  // $order_billing_address_2 = $order_data['billing']['address_2'];
  // $order_billing_city = $order_data['billing']['city'];
  // $order_billing_state = $order_data['billing']['state'];
  // $order_billing_postcode = $order_data['billing']['postcode'];
  // $order_billing_country = $order_data['billing']['country'];
  // $order_billing_email = $order_data['billing']['email'];
  // $order_billing_phone = $order_data['billing']['phone'];

  // ## SHIPPING INFORMATION:

  // $order_shipping_first_name = $order_data['shipping']['first_name'];
  // $order_shipping_last_name = $order_data['shipping']['last_name'];
  // $order_shipping_company = $order_data['shipping']['company'];
  // $order_shipping_address_1 = $order_data['shipping']['address_1'];
  // $order_shipping_address_2 = $order_data['shipping']['address_2'];
  // $order_shipping_city = $order_data['shipping']['city'];
  // $order_shipping_state = $order_data['shipping']['state'];
  // $order_shipping_postcode = $order_data['shipping']['postcode'];
  // $order_shipping_country = $order_data['shipping']['country'];
  $products = [];
  foreach ($order->get_items() as $item_key => $item) {
    $productTmp = [];
    ## Using WC_Order_Item methods ##

    // Item ID is directly accessible from the $item_key in the foreach loop or
    $item_id = $item->get_id();

    ## Using WC_Order_Item_Product methods ##

    $product = $item->get_product(); // Get the WC_Product object

    $product_id = $item->get_product_id(); // the Product id
    // $variation_id = $item->get_variation_id(); // the Variation id

    // $item_type = $item->get_type(); // Type of the order item ("line_item")

    $item_name = $item->get_name(); // Name of the product
    $quantity = $item->get_quantity();
    // $tax_class = $item->get_tax_class();
    // $line_subtotal = $item->get_subtotal(); // Line subtotal (non discounted)
    // $line_subtotal_tax = $item->get_subtotal_tax(); // Line subtotal tax (non discounted)
    // $line_total = $item->get_total(); // Line total (discounted)
    // $line_total_tax = $item->get_total_tax(); // Line total tax (discounted)

    ## Access Order Items data properties (in an array of values) ##
    // $item_data = $item->get_data();

    // $product_name = $item_data['name'];
    // $product_id = $item_data['product_id'];
    // $variation_id = $item_data['variation_id'];
    // $quantity = $item_data['quantity'];
    // $tax_class = $item_data['tax_class'];
    // $line_subtotal = $item_data['subtotal'];
    // $line_subtotal_tax = $item_data['subtotal_tax'];
    // $line_total = $item_data['total'];
    // $line_total_tax = $item_data['total_tax'];

    // // Get data from The WC_product object using methods (examples)
    // $product = $item->get_product(); // Get the WC_Product object

    // $product_type = $product->get_type();
    $product_sku = $product->get_sku();
    // $product_price = $product->get_price();
    // $stock_quantity = $product->get_stock_quantity();

    $image = wp_get_attachment_image_src(
      get_post_thumbnail_id($product_id),
      'single-post-thumbnail'
    );
    $productTmp['id'] = $product_id;
    $productTmp['sku'] = $product_sku;
    $productTmp['qty'] = $quantity;
    $productTmp['images'] = $image[0];

    $products[] = $productTmp;
  }

  $oderDetail['products'] = $products;

  wp_send_json(
    [
      'msg' => 'Order Detail.',
      'err' => 0,
      'data' => $oderDetail,
    ],
    200
  );
  //return $order_data;
}

function api_insertdevice($request)
{
  $device_id = $request['device_id'];
  $device_type = $request['device_type'];
  $currentuserid_fromjwt = get_current_user_id();
  if (!$currentuserid_fromjwt or $device_id == '') {
    wp_send_json(
      [
        'msg' => 'You need login and give us Device ID',
        'err' => 1,
        'data' => '',
      ],
      200
    );
  }

  global $wpdb;
  $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}notifications_devices WHERE device_id = '$device_id'";
  $checkDeviceToken = $wpdb->get_var($sql);
  $msg = 'This device ready in DB';
  if (!$checkDeviceToken) {
    //echo "<p>User count is {$checkDeviceToken}</p>";
    $wpdb->insert($wpdb->prefix . 'notifications_devices', [
      'device_id' => $device_id,
      'user_id' => $currentuserid_fromjwt,
      'device_type' => $device_type,
      'device_os' => $device_type == 0 ? 'ios' : 'android',
      'is_notify' => 1,
      'update_date' => strtotime('now'),
      'register_date' => strtotime('now'),
    ]);
    $msg = 'Added device to DB';
  }

  wp_send_json(
    [
      'msg' => $msg,
      'err' => 0,
      'data' => '',
    ],
    200
  );
}

function api_insertdevice_2($request, $userID)
{
  $device_id = $request['device_id'];
  $device_type = $request['device_type'];
  $currentuserid_fromjwt = $userID;
  if (!$currentuserid_fromjwt or $device_id == '') {
    wp_send_json(
      [
        'msg' => 'You need login and give us Device ID',
        'err' => 1,
        'data' => '',
      ],
      200
    );
  }

  global $wpdb;
  $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}notifications_devices WHERE device_id = '$device_id'";
  $checkDeviceToken = $wpdb->get_var($sql);
  $msg = 'This device ready in DB';
  if (!$checkDeviceToken) {
    //echo "<p>User count is {$checkDeviceToken}</p>";
    $wpdb->insert($wpdb->prefix . 'notifications_devices', [
      'device_id' => $device_id,
      'user_id' => $currentuserid_fromjwt,
      'device_type' => $device_type,
      'device_os' => $device_type == 0 ? 'ios' : 'android',
      'is_notify' => 1,
      'update_date' => strtotime('now'),
      'register_date' => strtotime('now'),
    ]);
    $msg = 'Added device to DB';
  }
  return true;
  exit;
}

function api_updateuser($request)
{
  $first_name = $request['first_name'];
  $last_name = $request['last_name'];
  $display_name = $request['display_name'];
  $currentuserid_fromjwt = get_current_user_id();
  if (!$currentuserid_fromjwt or $first_name == '' or $last_name == '') {
    wp_send_json(
      [
        'msg' => 'You need login and give us first name and last name.',
        'err' => 1,
        'data' => '',
      ],
      200
    );
  }

  global $wpdb;
  wp_update_user([
    'ID' => $currentuserid_fromjwt, // this is the ID of the user you want to update.
    'first_name' => $first_name,
    'last_name' => $last_name,
    'display_name' => $display_name,
  ]);

  wp_send_json(
    [
      'msg' => 'Update success.',
      'err' => 0,
      'data' => '',
    ],
    200
  );
}

function api_checkupdate($request){
  $currentuserid_fromjwt = get_current_user_id();
  $checkTimeFrom = $request['checktimefrom'];
  if(!$currentuserid_fromjwt OR !$checkTimeFrom OR !is_numeric($checkTimeFrom)){
    wp_send_json(
      [
        'msg' => 'Please give me params: checktimefrom and must have type number',
        'err' => 1,
        'data' => '',
      ],
      200
    );
  }

  global $wpdb;
  $sql = "SELECT action, type, object_id, date_time FROM {$wpdb->prefix}notifications_woo_change WHERE timestamp >= $checkTimeFrom ";
  $results = $wpdb->get_results($sql);
  $resData = array();
  $resData['haschange'] = 0;
  $resData['data'] = array();
  foreach($results as $result){
    if($result->action == 'delete' OR $result->action == 'trash'){
      $arr = [
        'id' => $result->object_id,
        'updated_time' => $result->date_time
      ];
    }else{
      if($result->type == 'product'){
        $product   = wc_get_product( $result->object_id );
        if(is_object($product)){
          $featured_img_url = get_the_post_thumbnail_url($product->get_image_id());
          $status = $product->get_status();
          if($status == 'publish'){
            $arr = [
              'id' => $product->get_id(),
              'name' => $product->get_title(),
              'slug' => $product->get_slug(),
              'status' => $product->get_status(),
              'sku' => $product->get_sku(),
              'src' => $featured_img_url ? $featured_img_url : '',
              'updated_time' => $result->date_time
            ];
          }
        }
      }elseif($result->type == 'category'){
        $term = get_term($result->object_id, 'product_cat');
        if(is_object($term)){
          $thumb_id = get_woocommerce_term_meta( $term->term_id, 'thumbnail_id', true );
          $featured_img_url = wp_get_attachment_url(  $thumb_id );
          $cat_arr = [
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
            'src' => $featured_img_url ? $featured_img_url : '',
            'updated_time' => $result->date_time
          ];
          if($result->action == 'addnew'){
            $arr['cat_info'] = $cat_arr;
            $arr['products'] = array();

            $queryArgs = array(
              'post_type'             => 'product',
              'post_status'           => 'publish',
              'posts_per_page'        => -1,
              'tax_query'             => array(
                array(
                  'taxonomy'      => 'product_cat',
                  'field'         => 'term_id', //This is optional, as it defaults to 'term_id'
                  'terms'         => $term->term_id
                )
              )
            );
            $query = new WP_Query($queryArgs);
            $products = [];
            foreach($query->posts as $p){
              $featured_img_url = get_the_post_thumbnail_url($p->ID);
              $products[] = [
                'id' => $p->ID,
                'name' => $p->post_title,
                'slug' => $p->post_name,
                'src' => $featured_img_url ? $featured_img_url : '',
              ];
            }
            wp_reset_postdata();
            $arr['products'] = $products;
          }else{
            $arr = $cat_arr;
          }
        }
      }
    }

    if(!empty($arr)){
      $data['action'] = $result->action;
      $data['type'] = $result->type;
      $data['data'] = $arr;
      $resData['data'][] = $data;
    }

  }
  if(!empty($resData['data'])){
    $resData['haschange'] = 1;
  }

  //echo "<pre>"; print_r($resData); echo "</pre>".__FILE__.": ".__LINE__."";

  wp_send_json( $resData, 200 );
  return true;
}

function api_getallproducts(){
	$queryArgs = array(
		'post_type'             => 'product',
		'post_status'           => 'publish',
		'posts_per_page'        => -1,
	);
	$query = new WP_Query($queryArgs);
	$products = [];
	foreach($query->posts as $p){
		$woo_product   = wc_get_product( $p->ID );
		$featured_img_url = get_the_post_thumbnail_url($p->ID);
		$terms = get_the_terms( $p->ID, 'product_cat' );
		$cat = '';
		foreach ($terms as $term) {
			if($cat == ''){
				$cat = $term->term_id;
			}else{
				$cat .= ','.$term->term_id;
			}
		}
		$products[] = [
			'id' => $p->ID,
			'name' => $p->post_title,
			'slug' => $p->post_name,
			'src' => $featured_img_url ? $featured_img_url : '',
			'sku' => $woo_product->get_sku(),
			'categories' => $cat
		];
		//echo "<pre>"; print_r($product); echo "</pre>".__FILE__.": ".__LINE__."";
	}
	wp_send_json( $products, 200 );
	wp_reset_postdata();
}