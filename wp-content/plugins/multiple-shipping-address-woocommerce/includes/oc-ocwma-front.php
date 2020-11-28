<?php
if (!defined('ABSPATH')) {
  exit();
}

if (!class_exists('OCWMA_front')) {
  class OCWMA_front
  {
    protected static $instance;

    function get_adress_book_endpoint_url($address_book)
    {
      $url = wc_get_endpoint_url('edit-address', 'shipping', get_permalink());
      return add_query_arg('address-book', $address_book, $url);
    }

    function ocwma_wc_address_book_add_to_menu($items)
    {
      foreach ($items as $key => $value) {
        if ('edit-address' === $key) {
          $items[$key] = __('Address Book', 'woo-address-book');
        }
      }
      return $items;
    }

    function ocwma_popup_div_footer()
    {
      ?>
              <div id="ocwma_billing_popup" class="ocwma_billing_popup_class">
              </div>
              <div id="ocwma_shipping_popup" class="ocwma_shipping_popup_class">
              </div>
              <?php
    }
    
    function showBillingAddress($user, $user_id){
      //echo '<div class="ocwma_table_bill">';
      if (!empty($user)) {
      ?>
        <!-- <div class="ocwma_bill_table"> -->
      <?php
        foreach ($user as $row) {
          $userdata_bil = $row->userdata;
          $user_data = unserialize($userdata_bil);
        ?>
          <div class="dh_multil_billing_address">
            <p>
              <span><b><?php echo $user_data['reference_field'] ?></b></span>
              <span>
                <button type="button" class="btn btn-primary btn-sm form_option_edit" data-id="<?php echo $user_id ?>"  data-eid-bil="<?php echo $row->id ?>"><i class="fa fa-pencil" aria-hidden="true"></i> edit</button>
                <a href="?action=delete_ocma&did=<?php echo $row->id ?>" class="btn btn-sm btn-danger delete_btn"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a>
              </span>
            </p>
            <p>
              <span ><?php echo 
              $user_data['billing_first_name'] .
              '&nbsp' .
              $user_data['billing_last_name'] ?></span>
            </p>
              <span><?php echo $user_data['billing_company'] ?></span>
            </p>
            <p>
              <span><?php echo $user_data['billing_address_1'] ?></span>
            </p>
            <p>
              <span><?php echo $user_data['billing_address_2'] ?></span>
            </p>
            <p>
              <span><?php echo $user_data['billing_city'].' '.$user_data['billing_state'].' '.$user_data['billing_postcode']. ' '.$user_data['billing_country'] ?></span>
            </p>
            <!-- <p>
              <span><?php echo $user_data['billing_country'] ?></span>
            </p>
            <p>
              <span><?php echo $user_data['billing_postcode'] ?></span>
            </p>
            <p>
              <span><?php echo $user_data['billing_state'] ?></span>
            </p> -->
          </div>
        <?php
        }
        ?>
        <!-- </div> -->
        <?php
      }
      //echo '</div>';
    }

    function showShippingAddress($tablename, $user_id){
      global $wpdb;
      $user_shipping = $wpdb->get_results(
        "SELECT * FROM {$tablename} WHERE type='shipping' AND userid=" .
          $user_id
      );
      echo '<div class="ocwma_table_ship">';
      if (!empty($user_shipping)) {
      ?>
      <div class="ocwma_ship_table">
      <?php
        foreach ($user_shipping as $row) {
          $userdata_ship = $row->userdata;
          $user_data = unserialize($userdata_ship);
        ?>
          <div class="dh_multil_shipping_address">
            <p>
              <span><b><?php echo $user_data['reference_field'] ?></b></span>
              <span>
                <button class="btn btn-primary form_option_ship_edit" data-id="<?php echo $user_id ?>"  data-eid-ship="<?php echo $row->id ?>"><i class="fa fa-pencil" aria-hidden="true"></i> edit</button>
                <a href="?action=delete-ship&did-ship=<?php echo $row->id ?>" class="btn btn-danger delete_btn"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a>
              </span>
            </p>
            <p>
              <span ><?php echo 
                $user_data['shipping_first_name'] .
                '&nbsp' .
                $user_data['shipping_last_name'] ?></span>
            </p>
              <span><?php echo $user_data['shipping_company'] ?></span>
            </p>
            <p>
            <span><?php echo $user_data['shipping_address_1'] ?></span>
            </p>
            <p>
              <span><?php echo $user_data['shipping_address_2'] ?></span>
            </p>
            <p>
              <span><?php echo $user_data['shipping_city'].' '.$user_data['shipping_state'].' '.$user_data['shipping_postcode'].' '.$user_data['shipping_country']  ?></span>
            </p>
            <!-- <p>
              <span><?php echo $user_data['shipping_country'] ?></span>
            </p>
            <p>
              <span><?php echo $user_data['shipping_postcode'] ?></span>
            </p>
            <p>
              <span><?php echo $user_data['shipping_state'] ?></span>
            </p> -->
          </div>
        <?php
        }
      ?>
      </div>
      <?php
      }
      echo '</div>';
    }

    function ocwma_my_account_endpoint_content()
    {
      $user_id = get_current_user_id();
      global $wpdb;
      $tablename = $wpdb->prefix . 'ocwma_billingadress';
      $user = $wpdb->get_results(
        "SELECT * FROM {$tablename} WHERE type='billing' AND userid=" . $user_id
      );
      //echo '<div class="ocwma_table_custom">';
      
      $this->showBillingAddress($user, $user_id);
      $this->showShippingAddress($tablename, $user_id);
      
      //echo '</div>';
      ?>
            <div class="cus_menu">
                <div class="billling-button">
                  <button class="form_option_billing" data-id="<?php echo $user_id; ?>" style="background-color: <?php echo get_option(
                    'ocwma_btn_bg_clr',
                    '#000000'
                  ); ?>; color: <?php echo get_option(
                    'ocwma_font_clr',
                    '#ffffff'
                  ); ?>; padding: <?php echo get_option(
                    'ocwma_btn_padding',
                    '8px 10px'
                  ); ?>; font-size: <?php echo get_option('ocwma_font_size', '15') .
                    'px'; ?>;">
                      <?php echo get_option(
                      'ocwma_head_title',
                      'Add Billing Address'
                      ); ?>
                  </button>
                </div>
                <div class="shipping-button">
                  <button class="form_option_shipping" data-id="<?php echo $user_id; ?>" style="background-color: <?php echo get_option(
                    'ocwma_btn_bg_clr',
                    '#000000'
                  ); ?>; color: <?php echo get_option(
                    'ocwma_font_clr',
                    '#ffffff'
                  ); ?>; padding: <?php echo get_option(
                    'ocwma_btn_padding',
                    '8px 10px'
                  ); ?>; font-size: <?php echo get_option('ocwma_font_size', '15') .
                    'px'; ?>;">
                      <?php echo get_option(
                      'ocwma_head_title_ship',
                      'Add Shipping Address'
                      ); ?>
                  </button>
                </div>
            </div>
              <?php
    }

    function ocwma_billing_popup_open()
    {
      $user_id = sanitize_text_field($_REQUEST['popup_id_pro']);
      $edit_id = sanitize_text_field($_REQUEST['eid-bil']);

      global $wpdb;
      $tablename = $wpdb->prefix . 'ocwma_billingadress';
      if (empty($edit_id)) {
        $user = $wpdb->get_results(
          "SELECT count(*) as count FROM {$tablename} WHERE type='billing'  AND userid=" .
            $user_id
        );
        $save_adress = $user[0]->count;
        $max_count = get_option('ocwma_max_adress', '3');
        if ($save_adress >= $max_count) {
          echo '<div class="ocwma_modal-content">';
          echo '<span class="ocwma_close">&times;</span>';
          echo "<h3 class='ocwma_border'>you can add maximum  " .
            get_option('ocwma_max_adress', '3') .
            ' addresses !</h3>';
          echo '</div>';
          echo '</div>';
        } else {

          echo '<div class="ocwma_modal-content">';
          echo '<span class="ocwma_close">&times;</span>';

          $address_fields = wc()->countries->get_address_fields(
            get_user_meta(get_current_user_id(), 'billing_country', true)
          );

          //echo '<pre>';
          //print_r($address_fields);
          ?>
          <form method="post" id="oc_add_billing_form">
            <div class="ocwma_woocommerce-address-fields">
              <div class="ocwma_woocommerce-address-fields_field-wrapper">
                <input type="hidden" name="type"  value="billing">
                <p class="form-row form-row-wide" id="reference_field" data-priority="30">
                  <label for="reference_field" class="">
                    <b>Reference Name:</b>
                    <abbr class="required" title="required">*</abbr>
                  </label>
                  <span class="woocommerce-input-wrapper">
                    <input type="text" class="input-text" name="reference_field" id="oc_refname">
                  </span>
                </p>
                  <?php foreach ($address_fields as $key => $field) {
                    woocommerce_form_field(
                      $key,
                      $field,
                      wc_get_post_data_by_key($key)
                    );
                  } ?>
              </div>
              <p>
                <button type="submit" name="add_billing" id="oc_add_billing_form_submit" class="button" value="ocwma_billpp_save_option"><?php esc_html_e(
                  'Save Address',
                  'fr-address-book-for-woocommerce'
                ); ?></button>
              </p>
            </div>
          </form>
        <?php
        echo '</div>';
        echo '</div>';

        }
      } else {
        // echo $edit_id;
        ob_start(); ?>
        <div class="ocwma_modal-content">
        <span class="ocwma_close">&times;</span> 
        <?php
        $user = $wpdb->get_results(
          "SELECT * FROM {$tablename} WHERE type='billing' AND userid=" .
            $user_id .
            ' AND id=' .
            $edit_id
        );
        $user_data = unserialize($user[0]->userdata);
        $address_fields = wc()->countries->get_address_fields(
          get_user_meta(get_current_user_id(), 'billing_country', true)
        );
        ?>
            <form method="post" id="oc_edit_billing_form">
              <div class="ocwma_woocommerce-address-fields">
                <div class="ocwma_woocommerce-address-fields_field-wrapper">
                    <input type="hidden" name="userid"  value="<?php echo $user_id; ?>">
                    <input type="hidden" name="edit_id"  value= "<?php echo $edit_id; ?>">
                    <input type="hidden" name="type"  value="billing">
                    <p class="form-row form-row-wide" id="reference_field" data-priority="30">
                    <label for="reference_field" class="">
                      <b>Reference Name:</b>
                      <abbr class="required" title="required">*</abbr>
                    </label>
                    <span class="woocommerce-input-wrapper">
                      <input type="text" class="input-text" id="oc_refname" name="reference_field" value="<?php echo $user_data[
                        'reference_field'
                      ]; ?>">
                    </span>
                  </p>
                <?php foreach ($address_fields as $key => $field) {
                  woocommerce_form_field($key, $field, $user_data[$key]);
                } ?>
                </div>
                <p>
                  <button type="submit" name="add_billing_edit" id="oc_edit_billing_form_submit" class="button" value="ocwma_billpp_save_option"><?php esc_html_e(
                    'Update Address',
                    'fr-address-book-for-woocommerce'
                  ); ?></button>   
                </p>
              </div>
            </form>
              
        </div>
        </div>

        <?php
        $edit_html = ob_get_clean();

        $return_arr[] = ['html' => $edit_html];
        echo json_encode($return_arr);

      }
      die();
    }

    function ocwma_shipping_popup_open()
    {
      $user_id = sanitize_text_field($_REQUEST['popup_id_pro']);
      $edit_id = sanitize_text_field($_REQUEST['eid-ship']);
      //echo $edit_id;
      global $wpdb;
      $tablename = $wpdb->prefix . 'ocwma_billingadress';
      if (empty($edit_id)) {
        $user = $wpdb->get_results(
          "SELECT count(*) as count FROM {$tablename} WHERE type='shipping'  AND userid=" .
            $user_id
        );
        $save_adress = $user[0]->count;
        $max_count = get_option('ocwma_max_adress', '3');
        if ($save_adress >= $max_count) {
          echo '<div class="ocwma_modal-content">';
          echo '<span class="ocwma_close">&times;</span>';
          echo "<h3 class='ocwma_border'>you can add maximum  " .
            get_option('ocwma_max_adress', '3') .
            ' addresses ! !</h3>';
          echo '</div>';
          echo '</div>';
        } else {

          echo '<div class="ocwma_modal-content">';
          echo '<span class="ocwma_close">&times;</span>';
          $countries = new WC_Countries();
          if (!isset($country)) {
            $country = $countries->get_base_country();
          }
          if (!isset($user_id)) {
            $user_id = get_current_user_id();
          }
          $address_fields = WC()->countries->get_address_fields(
            $country,
            'shipping_'
          );
          ?>
                        <form method="post" id="oc_add_shipping_form">
                            <div class="ocwma_woocommerce-address-fields">
                                <div class="ocwma_woocommerce-address-fields_field-wrapper">
                                        <input type="hidden" name="type"  value="shipping">
                                        <p class="form-row form-row-wide" id="reference_field" data-priority="30">
	                                        <label for="reference_field" class="">
                                            <b>Reference Name:</b>
                                            <abbr class="required" title="required">*</abbr>
                                          </label>
	                                        <span class="woocommerce-input-wrapper">
	                                          <input type="text" class="input-text" id="oc_refname" name="reference_field">
	                                        </span>
                                      	</p>
                                      <?php foreach (
                                        $address_fields
                                        as $key => $field
                                      ) {
                                        woocommerce_form_field(
                                          $key,
                                          $field,
                                          wc_get_post_data_by_key($key)
                                        );
                                      } ?>
                                </div>
                                <p>
                                 <button type="submit" name="add_shipping" id="oc_add_shipping_form_submit" class="button" value="ocwma_shippp_save_optionn"><?php esc_html_e(
                                   'Save Address',
                                   'address-book-for-woocommerce'
                                 ); ?></button>   
                                </p>
                            </div>
                        </form>
                      <?php
                      echo '</div>';
                      echo '</div>';

        }
      } else {

        echo '<div class="ocwma_modal-content">';
        echo '<span class="ocwma_close">&times;</span>';
        $user = $wpdb->get_results(
          "SELECT * FROM {$tablename} WHERE type='shipping' AND userid=" .
            $user_id .
            ' AND id=' .
            $edit_id
        );
        $user_data = unserialize($user[0]->userdata);
        $countries = new WC_Countries();
        if (!isset($country)) {
          $country = $countries->get_base_country();
        }
        if (!isset($user_id)) {
          $user_id = get_current_user_id();
        }
        $address_fields = WC()->countries->get_address_fields(
          $country,
          'shipping_'
        );
        ?>
                  <form method="post" id="oc_edit_shipping_form">
                      <div class="ocwma_woocommerce-address-fields">
                          <div class="ocwma_woocommerce-address-fields_field-wrapper">
                                <input type="hidden" name="type"  value="shipping">
                                    <input type="hidden" name="userid"  value="<?php echo $user_id; ?>">
                                  <input type="hidden" name="edit_id"  value= "<?php echo $edit_id; ?>">
                                  <p class="form-row form-row-wide" id="reference_field" data-priority="30">
                                    <label for="reference_field" class="">
                                      <b>Reference Name:</b>
                                      <abbr class="required" title="required">*</abbr>
                                    </label>
                                    <span class="woocommerce-input-wrapper">
                                      <input type="text" class="input-text" id="oc_refname" name="reference_field" value="<?php echo $user_data[
                                        'reference_field'
                                      ]; ?>">
                                    </span>
                                  </p>
                                <?php foreach (
                                  $address_fields
                                  as $key => $field
                                ) {
                                  woocommerce_form_field(
                                    $key,
                                    $field,
                                    $user_data[$key]
                                  );
                                } ?>
                          </div>
                          <p>
                           <button type="submit" name="add_shipping_edit" class="button" id="oc_edit_shipping_form_submit" value="ocwma_shippp_save_optionn"><?php esc_html_e(
                             'Update Address',
                             'address-book-for-woocommerce'
                           ); ?></button>   
                          </p>
                      </div>
                  </form>
                <?php
                echo '</div>';
                echo '</div>';

      }
      die();
    }
    /* billigdata */

    function ocwma_billing_data_select()
    {
      $user_id = get_current_user_id();
      $select_id = sanitize_text_field($_REQUEST['sid']);
      global $wpdb;
      $tablename = $wpdb->prefix . 'ocwma_billingadress';
      $user = $wpdb->get_results(
        "SELECT * FROM {$tablename} WHERE type='billing' AND userid=" .
          $user_id .
          ' AND id=' .
          $select_id
      );
      $user_data = unserialize($user[0]->userdata);
      echo json_encode($user_data);
      exit();
    }
    /* shipping */

    function ocwma_shipping_data_select()
    {
      $user_id = get_current_user_id();
      $select_id = sanitize_text_field($_REQUEST['sid']);
      global $wpdb;
      $tablename = $wpdb->prefix . 'ocwma_billingadress';
      $user = $wpdb->get_results(
        "SELECT * FROM {$tablename} WHERE type='shipping' AND userid=" .
          $user_id .
          ' AND id=' .
          $select_id
      );
      $user_data = unserialize($user[0]->userdata);
      echo json_encode($user_data);
      exit();
    }

    function OCWMA_all_billing_address()
    {
      $user_id = get_current_user_id();
      global $wpdb;
      $tablename = $wpdb->prefix . 'ocwma_billingadress';
      ?>
         <select class="ocwma_select">
          <option>...Choose address...</option>
          <?php
          $user = $wpdb->get_results(
            "SELECT * FROM {$tablename} WHERE type='billing' AND userid=" .
              $user_id
          );
          foreach ($user as $row) {

            $userdata_bil = $row->userdata;
            $user_data = unserialize($userdata_bil);
            ?> <option value="<?php echo $row->id; ?>">  <?php echo $user_data[
  'reference_field'
]; ?></option><?php
          }
          ?>
          </select>
          <button class="form_option_billing" data-id="<?php echo $user_id; ?>" style="background-color: <?php echo get_option(
  'ocwma_btn_bg_clr',
  '#000000'
); ?>; color: <?php echo get_option(
  'ocwma_font_clr',
  '#ffffff'
); ?>; padding: <?php echo get_option(
  'ocwma_btn_padding',
  '8px 10px'
); ?>; font-size: <?php echo get_option('ocwma_font_size', '15') .
  'px'; ?>;"><?php echo get_option(
  'ocwma_head_title',
  'Add Billing Address'
); ?></button>

          <?php
    }

    function OCWMA_all_shipping_address()
    {
      $user_id = get_current_user_id();
      global $wpdb;
      $tablename = $wpdb->prefix . 'ocwma_billingadress';
      ?>
           <select class="ocwma_select_shipping">
            <option>...Choose address...</option>
            <?php
            $user = $wpdb->get_results(
              "SELECT * FROM {$tablename} WHERE type='shipping' AND userid=" .
                $user_id
            );
            foreach ($user as $row) {

              $userdata_bil = $row->userdata;
              $user_data = unserialize($userdata_bil);
              ?> <option value="<?php echo $row->id; ?>">  <?php echo $user_data[
  'reference_field'
]; ?></option><?php
            }
            ?>
            </select>
            <button class="form_option_shipping" data-id="<?php echo $user_id; ?>" style="background-color: <?php echo get_option(
  'ocwma_btn_bg_clr',
  '#000000'
); ?>; color: <?php echo get_option(
  'ocwma_font_clr',
  '#ffffff'
); ?>; padding: <?php echo get_option(
  'ocwma_btn_padding',
  '8px 10px'
); ?>; font-size: <?php echo get_option('ocwma_font_size', '15') .
  'px'; ?>;"><?php echo get_option(
  'ocwma_head_title_ship',
  'Add Shipping Address'
); ?></button>
 
            <?php
    }

    function OCWMA_save_options()
    {
      global $wpdb;
      $tablename = $wpdb->prefix . 'ocwma_billingadress';

      if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_ocma') {
        $delete_id = sanitize_text_field($_REQUEST['did']);
        $sql = "DELETE  FROM {$tablename} WHERE id='" . $delete_id . "'";
        $wpdb->query($sql);
        wp_safe_redirect(
          wc_get_endpoint_url(
            'edit-address',
            '',
            wc_get_page_permalink('myaccount')
          )
        );
        exit();
      }

      if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete-ship') {
        $delete_id = sanitize_text_field($_REQUEST['did-ship']);
        $sql = "DELETE  FROM {$tablename} WHERE id='" . $delete_id . "'";

        $wpdb->query($sql);
        wp_safe_redirect(
          wc_get_endpoint_url(
            'edit-address',
            '',
            wc_get_page_permalink('myaccount')
          )
        );
        exit();
      }
    }

    function ocwma_validate_billing_form_fields_func()
    {
      global $wpdb;
      $tablename = $wpdb->prefix . 'ocwma_billingadress';

      $address_fields = wc()->countries->get_address_fields(
        get_user_meta(get_current_user_id(), 'billing_country', true)
      );

      $ocwma_userid = get_current_user_id();

      $billing_data = [];
      $field_errors = [];

      $billing_data['reference_field'] = sanitize_text_field(
        $_REQUEST['reference_field']
      );

      if ($_REQUEST['reference_field'] == '') {
        $field_errors['oc_refname'] = '1';
      }

      foreach ($address_fields as $key => $field) {
        $billing_data[$key] = sanitize_text_field($_REQUEST[$key]);

        if ($_REQUEST[$key] == '') {
          if ($field['required'] == 1) {
            $field_errors[$key] = '1';
          }
        }
      }

      unset($field_errors['billing_state']);

      if (empty($field_errors)) {
        $billing_data_serlized = serialize($billing_data);
        $wpdb->insert($tablename, [
          'userid' => $ocwma_userid,
          'userdata' => $billing_data_serlized,
          'type' => sanitize_text_field($_REQUEST['type']),
        ]);

        $added = 'true';
      } else {
        $added = 'false';
      }

      $return_arr = [
        'added' => $added,
        'field_errors' => $field_errors,
      ];

      echo json_encode($return_arr);
      exit();
    }

    function ocwma_validate_shipping_form_fields_func()
    {
      global $wpdb;
      $tablename = $wpdb->prefix . 'ocwma_billingadress';

      $countries = new WC_Countries();
      $country = $countries->get_base_country();

      $address_fields = WC()->countries->get_address_fields(
        $country,
        'shipping_'
      );

      $ocwma_userid = get_current_user_id();

      $billing_data = [];
      $field_errors = [];

      $billing_data['reference_field'] = sanitize_text_field(
        $_REQUEST['reference_field']
      );

      if ($_REQUEST['reference_field'] == '') {
        $field_errors['oc_refname'] = '1';
      }

      foreach ($address_fields as $key => $field) {
        $billing_data[$key] = sanitize_text_field($_REQUEST[$key]);

        if ($_REQUEST[$key] == '') {
          if ($field['required'] == 1) {
            $field_errors[$key] = '1';
          }
        }
      }

      unset($field_errors['shipping_state']);

      if (empty($field_errors)) {
        $billing_data_serlized = serialize($billing_data);
        $wpdb->insert($tablename, [
          'userid' => $ocwma_userid,
          'userdata' => $billing_data_serlized,
          'type' => sanitize_text_field($_REQUEST['type']),
        ]);

        $added = 'true';
      } else {
        $added = 'false';
      }

      $return_arr = [
        'added' => $added,
        'field_errors' => $field_errors,
      ];

      echo json_encode($return_arr);
      exit();
    }

    function ocwma_validate_edit_billing_form_fields_func()
    {
      global $wpdb;
      $tablename = $wpdb->prefix . 'ocwma_billingadress';

      $address_fields = wc()->countries->get_address_fields(
        get_user_meta(get_current_user_id(), 'billing_country', true)
      );

      $edit_id = sanitize_text_field($_REQUEST['edit_id']);

      $ocwma_userid = get_current_user_id();

      $billing_data = [];
      $field_errors = [];

      $billing_data['reference_field'] = sanitize_text_field(
        $_REQUEST['reference_field']
      );

      if ($_REQUEST['reference_field'] == '') {
        $field_errors['oc_refname'] = '1';
      }

      foreach ($address_fields as $key => $field) {
        $billing_data[$key] = sanitize_text_field($_REQUEST[$key]);

        if ($_REQUEST[$key] == '') {
          if ($field['required'] == 1) {
            $field_errors[$key] = '1';
          }
        }
      }

      unset($field_errors['billing_state']);

      if (empty($field_errors)) {
        $billing_data_serlized = serialize($billing_data);
        $condition = [
          'id' => $edit_id,
          'userid' => $ocwma_userid,
          'type' => sanitize_text_field($_REQUEST['type']),
        ];

        $wpdb->update(
          $tablename,
          [
            'userdata' => $billing_data_serlized,
          ],
          $condition
        );

        $added = 'true';
      } else {
        $added = 'false';
      }

      $return_arr = [
        'added' => $added,
        'field_errors' => $field_errors,
      ];

      echo json_encode($return_arr);
      exit();
    }

    function ocwma_validate_edit_shipping_form_fields_func()
    {
      global $wpdb;
      $tablename = $wpdb->prefix . 'ocwma_billingadress';

      $edit_id = sanitize_text_field($_REQUEST['edit_id']);

      $countries = new WC_Countries();
      $country = $countries->get_base_country();

      $address_fields = WC()->countries->get_address_fields(
        $country,
        'shipping_'
      );

      $ocwma_userid = get_current_user_id();

      $billing_data = [];
      $field_errors = [];

      $billing_data['reference_field'] = sanitize_text_field(
        $_REQUEST['reference_field']
      );

      if ($_REQUEST['reference_field'] == '') {
        $field_errors['oc_refname'] = '1';
      }

      foreach ($address_fields as $key => $field) {
        $billing_data[$key] = sanitize_text_field($_REQUEST[$key]);

        if ($_REQUEST[$key] == '') {
          if ($field['required'] == 1) {
            $field_errors[$key] = '1';
          }
        }
      }

      unset($field_errors['shipping_state']);

      if (empty($field_errors)) {
        $billing_data_serlized = serialize($billing_data);

        $condition = [
          'id' => $edit_id,
          'userid' => $ocwma_userid,
          'type' => sanitize_text_field($_REQUEST['type']),
        ];
        $wpdb->update(
          $tablename,
          [
            'userdata' => $billing_data_serlized,
          ],
          $condition
        );

        $added = 'true';
      } else {
        $added = 'false';
      }

      $return_arr = [
        'added' => $added,
        'field_errors' => $field_errors,
      ];

      echo json_encode($return_arr);
      exit();
    }

    function init()
    {
      global $wpdb;
      $charset_collate = $wpdb->get_charset_collate();
      $tablename = $wpdb->prefix . 'ocwma_billingadress';
      $sql = "CREATE TABLE $tablename (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                userid TEXT NOT NULL,
                userdata TEXT NOT NULL,
                type TEXT NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

      require_once ABSPATH . 'wp-admin/includes/upgrade.php';
      dbDelta($sql);

      add_filter(
        'woocommerce_account_menu_items',
        [$this, 'ocwma_wc_address_book_add_to_menu'],
        10
      );
      add_action('woocommerce_account_edit-address_endpoint', [
        $this,
        'ocwma_my_account_endpoint_content',
      ]);
      add_action('wp_footer', [$this, 'ocwma_popup_div_footer']);
      add_action('wp_ajax_productscommentsbilling', [
        $this,
        'ocwma_billing_popup_open',
      ]);
      add_action('wp_ajax_nopriv_productscommentsbilling', [
        $this,
        'ocwma_billing_popup_open',
      ]);
      add_action('wp_ajax_productscommentsshipping', [
        $this,
        'ocwma_shipping_popup_open',
      ]);
      add_action('wp_ajax_nopriv_productscommentsshipping', [
        $this,
        'ocwma_shipping_popup_open',
      ]);
      add_action('woocommerce_before_checkout_billing_form', [
        $this,
        'OCWMA_all_billing_address',
      ]);
      add_action('woocommerce_before_checkout_shipping_form', [
        $this,
        'OCWMA_all_shipping_address',
      ]);
      add_action('wp_ajax_productscommentsbilling_select', [
        $this,
        'ocwma_billing_data_select',
      ]);
      add_action('wp_ajax_nopriv_productscommentsbilling_select', [
        $this,
        'ocwma_billing_data_select',
      ]);
      add_action('wp_ajax_productscommentsshipping_select', [
        $this,
        'ocwma_shipping_data_select',
      ]);
      add_action('wp_ajax_nopriv_productscommentsshipping_select', [
        $this,
        'ocwma_shipping_data_select',
      ]);
      add_action('wp_ajax_ocwma_validate_billing_form_fields', [
        $this,
        'ocwma_validate_billing_form_fields_func',
      ]);
      add_action('wp_ajax_nopriv_ocwma_validate_billing_form_fields', [
        $this,
        'ocwma_validate_billing_form_fields_func',
      ]);
      add_action('wp_ajax_ocwma_validate_shipping_form_fields', [
        $this,
        'ocwma_validate_shipping_form_fields_func',
      ]);
      add_action('wp_ajax_nopriv_ocwma_validate_shipping_form_fields', [
        $this,
        'ocwma_validate_shipping_form_fields_func',
      ]);
      add_action('wp_ajax_ocwma_validate_edit_billing_form_fields', [
        $this,
        'ocwma_validate_edit_billing_form_fields_func',
      ]);
      add_action('wp_ajax_nopriv_ocwma_validate_edit_billing_form_fields', [
        $this,
        'ocwma_validate_edit_billing_form_fields_func',
      ]);
      add_action('wp_ajax_ocwma_validate_edit_shipping_form_fields', [
        $this,
        'ocwma_validate_edit_shipping_form_fields_func',
      ]);
      add_action('wp_ajax_nopriv_ocwma_validate_edit_shipping_form_fields', [
        $this,
        'ocwma_validate_edit_shipping_form_fields_func',
      ]);
      add_action('init', [$this, 'OCWMA_save_options']);
    }

    public static function instance()
    {
      if (!isset(self::$instance)) {
        self::$instance = new self();
        self::$instance->init();
      }
      return self::$instance;
    }
  }

  OCWMA_front::instance();
}
