<?php
if (!defined('ABSPATH')) {
  exit();
}

if (!class_exists('OCWMA_admin_menu')) {
  class OCWMA_admin_menu
  {
    protected static $OCWMA_instance;

    function OCWMA_submenu_page()
    {
      add_submenu_page(
        'woocommerce',
        'Multiple Address Option',
        'Multiple Address Option',
        'manage_options',
        'multiple-address',
        [$this, 'OCWMA_callback']
      );
    }

    function OCWMA_callback()
    {
      ?>    
                <div class="wrap">
                    <h2><u>Multiple address setting</u></h2>
                    <?php if (
                      isset($_REQUEST['message']) &&
                      $_REQUEST['message'] == 'success'
                    ) { ?>
                        <div class="notice notice-success is-dismissible"> 
                            <p><strong>Record updated successfully.</strong></p>
                        </div>
                    <?php } ?>
                </div>
                <div class="ocwma-container">
                    <form method="post" >
                      <?php wp_nonce_field(
                        'ocwma_nonce_action',
                        'ocwma_nonce_field'
                      ); ?>   
                            <div class="ocwma_cover_div">
                                <table class="ocwma_data_table">
                                    <h2>General Setting</h2>
                                    <tr>
                                        <th>MAX Address</th>
                                        <td>
                                            <input type="number" name="ocwma_max_adress" value="<?php if (
                                              !empty(
                                                get_option('ocwma_max_adress')
                                              )
                                            ) {
                                              echo get_option(
                                                'ocwma_max_adress'
                                              );
                                            } else {
                                              echo '3';
                                            } ?>">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="ocwma_cover_div">
                                <table class="ocwma_data_table">
                                    <h2>Multiple Button Style</h2>
                                    <tr>
                                        <th>Button Title for Billing</th>
                                        <td>
                                            <input type="text" name="ocwma_head_title" value="<?php if (
                                              !empty(
                                                get_option('ocwma_head_title')
                                              )
                                            ) {
                                              echo get_option(
                                                'ocwma_head_title'
                                              );
                                            } else {
                                              echo 'Add Billing Address';
                                            } ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Button Title for Shipping</th>
                                        <td>
                                            <input type="text" name="ocwma_head_title_ship" value="<?php if (
                                              !empty(
                                                get_option(
                                                  'ocwma_head_title_ship'
                                                )
                                              )
                                            ) {
                                              echo get_option(
                                                'ocwma_head_title_ship'
                                              );
                                            } else {
                                              echo 'Add Shipping Address';
                                            } ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Font size</th>
                                        <td>
                                            <input type="text" name="ocwma_font_size" value="<?php if (
                                              !empty(
                                                get_option('ocwma_font_size')
                                              )
                                            ) {
                                              echo get_option(
                                                'ocwma_font_size'
                                              );
                                            } else {
                                              echo '15';
                                            } ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Font color</th>
                                        <td>
                                            <input type="text" class="color-picker" data-alpha="true" name="ocwma_font_clr" value="<?php echo get_option(
                                              'ocwma_font_clr',
                                              '#ffffff'
                                            ); ?>"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Background Color</th>
                                        <td>
                                            <input type="text" class="color-picker" data-alpha="true" name="ocwma_btn_bg_clr" value="<?php echo get_option(
                                              'ocwma_btn_bg_clr',
                                              '#000000'
                                            ); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                      <th>Button Padding</th>
                                        <td>
                                            <input type="text" name="ocwma_btn_padding" value="<?php if (
                                              !empty(
                                                get_option('ocwma_btn_padding')
                                              )
                                            ) {
                                              echo get_option(
                                                'ocwma_btn_padding'
                                              );
                                            } else {
                                              echo '8px 10px';
                                            } ?>">
                                            <span>give value in px(ex.6px 8px)</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        <input type="hidden" name="action" value="ocwma_save_option">
                        <input type="submit" value="Save changes" name="submit" class="button-primary" id="wfc-btn-space">
                    </form>  
                </div>
            <?php
    }

    function OCWMA_save_options()
    {
      if (current_user_can('administrator')) {
        if (
          isset($_REQUEST['action']) &&
          $_REQUEST['action'] == 'ocwma_save_option'
        ) {
          if (
            !isset($_POST['ocwma_nonce_field']) ||
            !wp_verify_nonce($_POST['ocwma_nonce_field'], 'ocwma_nonce_action')
          ) {
            print 'Sorry, your nonce did not verify.';
            exit();
          } else {
            update_option(
              'ocwma_font_clr',
              sanitize_text_field($_REQUEST['ocwma_font_clr']),
              'yes'
            );
            update_option(
              'ocwma_font_clr',
              sanitize_text_field($_REQUEST['ocwma_font_clr']),
              'yes'
            );
            update_option(
              'ocwma_btn_bg_clr',
              sanitize_text_field($_REQUEST['ocwma_btn_bg_clr']),
              'yes'
            );
            update_option(
              'ocwma_max_adress',
              sanitize_text_field($_REQUEST['ocwma_max_adress']),
              'yes'
            );
            update_option(
              'ocwma_head_title',
              sanitize_text_field($_REQUEST['ocwma_head_title']),
              'yes'
            );
            update_option(
              'ocwma_head_title_ship',
              sanitize_text_field($_REQUEST['ocwma_head_title_ship']),
              'yes'
            );
            update_option(
              'ocwma_font_size',
              sanitize_text_field($_REQUEST['ocwma_font_size']),
              'yes'
            );
          }
        }
      }
    }

    function init()
    {
      add_action('admin_menu', [$this, 'OCWMA_submenu_page']);
      add_action('init', [$this, 'OCWMA_save_options']);
    }

    public static function OCWMA_instance()
    {
      if (!isset(self::$OCWMA_instance)) {
        self::$OCWMA_instance = new self();
        self::$OCWMA_instance->init();
      }
      return self::$OCWMA_instance;
    }
  }

  OCWMA_admin_menu::OCWMA_instance();
}
