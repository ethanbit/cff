<?php
/*
Plugin Name: WP Delivery Date
Description: Show delivery date on checkout page
Author: NBD - Skype: mr.nbduc
Version: 1.0
*/

add_action('init', 'callback_for_setting_up_scripts');
function callback_for_setting_up_scripts() {
    wp_enqueue_style( 'wp_delivery_date', plugins_url('/style.css', __FILE__), false, '1.0.0', 'all');
}
// Register main datepicker jQuery plugin script
add_action('wp_enqueue_scripts', 'wp_delivery_date_enabling_date_picker');
function wp_delivery_date_enabling_date_picker()
{
  // Only on front-end and checkout page
  if (is_admin() || !is_checkout()) {
    return;
  }

  // Load the datepicker jQuery-ui plugin script
  wp_enqueue_script('jquery-ui-datepicker');
}

// Call datepicker functionality in your custom text field
add_action(
  'woocommerce_after_order_notes',
  'wp_delivery_date_datepicker_field',
  10,
  1
);

function wp_delivery_date_datepicker_field($checkout)
{
  //date_default_timezone_set('America/Los_Angeles');
  $mydateoptions = ['' => __('Select PickupDate', 'woocommerce')];

  $args = array(  
    'post_type' => 'holiday',
    'post_status' => 'publish',
  );

  $loop = new WP_Query( $args ); 
  $holiday = $loop->posts;
  //wp_reset_postdata(); 
  
  $hString = '';
  foreach($holiday as $h){
    $getDate = get_field('date', $h->ID);
    if($hString == ''){
      $hString = '"' . $getDate . '"';
    }else{
      $hString .= ', "' . $getDate . '"';
    }
  }
  ?>
  <div id="my_custom_checkout_field">
    <h3>Delivery Date</h3>

    <script>
        var disabledDays = [<?php echo $hString; ?>];
        function disableAllTheseDays(date) {
            var m = date.getMonth(), d = date.getDate(), y = date.getFullYear();
            for (i = 0; i < disabledDays.length; i++) {
                if($.inArray(d + '/' + (m+1) + '/' + y, disabledDays) != -1) {
                    return [false];
                }
            }
            return [true];
        }
        jQuery(function($){
            $("#datepicker").datepicker({ 
              minDate: 0, 
              dateFormat: "dd/mm/yy",
              beforeShowDay: disableAllTheseDays
            });
        });
    </script>
  <?php 
  woocommerce_form_field(
    'delivery_date',
    [
      'type' => 'text',
      'class' => ['my-field-class form-row-wide'],
      'id' => 'datepicker',
      'required' => true,
      'label' => __('Delivery Date'),
      'placeholder' => __('Select Date'),
      'options' => $mydateoptions,
      'autocomplete' => '_off_auto_delivery_date',
    ],
    $checkout->get_value('delivery_date')
  );
  ?>
  </div>
  <?php
}

/**
 * Process the checkout
 **/
add_action(
  'woocommerce_checkout_process',
  'wp_delivery_date_checkout_field_process'
);

function wp_delivery_date_checkout_field_process()
{
  global $woocommerce;

  // Check if set, if its not set add an error.
  if (!$_POST['delivery_date']) {
    wc_add_notice(
      '<strong>Delivery Date</strong> ' .
        __('is a required field.', 'woocommerce'),
      'error'
    );
  }
}
/**
 * Update the order meta with custom fields values
 * */
add_action(
  'woocommerce_checkout_update_order_meta',
  'wp_delivery_date_save_data'
);

function wp_delivery_date_save_data($order_id)
{
  if (!empty($_POST['delivery_date'])) {
    update_post_meta(
      $order_id,
      'delivery_date',
      sanitize_text_field($_POST['delivery_date'])
    );
  }
}

add_action(
  'woocommerce_admin_order_data_after_billing_address',
  'wp_delivery_date_display'
);

add_action('woocommerce_email_order_meta', 'wp_delivery_date_display');

function wp_delivery_date_display($order)
{
  //echo "<pre>".$order->id; print_r($order); echo "</pre>".__FILE__.": ".__LINE__."";
  echo '<p><strong>Delivery Date:</strong> ' .
    get_post_meta($order->id, 'delivery_date', true) .
    '</p>';
}
