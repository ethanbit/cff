<?php

class Besa_Merlin_Config {

	private $config = [];

	public function __construct() {
		$this->init();
		add_action( 'merlin_import_files', [ $this, 'import_files' ] );
		add_action( 'merlin_after_all_import', [ $this, 'after_import_setup' ], 10, 1 );
		add_filter( 'merlin_generate_child_functions_php', [ $this, 'render_child_functions_php' ], 10 ,2 );
		add_filter( 'merlin_generate_child_style_css', [ $this, 'render_child_style_css' ], 10 ,5 );

		remove_action( 'init', 'tbay_import_init', 50 );
	} 

	private function init() {
		$wizard = new Merlin(
			$config = array(
				'directory'          => 'inc/merlin',
				// Location / directory where Merlin WP is placed in your theme.
				'merlin_url'         => 'tbay_import',
				// The wp-admin page slug where Merlin WP loads.
				'parent_slug'        => 'themes.php',
				// The wp-admin parent page slug for the admin menu item.
				'capability'         => 'manage_options',
				// The capability required for this menu to be displayed to the user.
				'dev_mode'           => true,
				// Enable development mode for testing.
				'license_step'       => false,
				// EDD license activation step.
				'license_required'   => false,
				// Require the license activation step.
				'license_help_url'   => '',
				// URL for the 'license-tooltip'.
				'edd_remote_api_url' => '',
				// EDD_Theme_Updater_Admin remote_api_url.
				'edd_item_name'      => '',
				// EDD_Theme_Updater_Admin item_name.
				'edd_theme_slug'     => '',
				// EDD_Theme_Updater_Admin item_slug.
			),
			$strings = array(
				'admin-menu'          => esc_html__( 'Theme Setup', 'besa' ),

				/* translators: 1: Title Tag 2: Theme Name 3: Closing Title Tag */
				'title%s%s%s%s'       => esc_html__( '%1$s%2$s Themes &lsaquo; Theme Setup: %3$s%4$s', 'besa' ),
				'return-to-dashboard' => esc_html__( 'Return to the dashboard', 'besa' ),
				'ignore'              => esc_html__( 'Disable this wizard', 'besa' ),

				'btn-skip'                 => esc_html__( 'Skip', 'besa' ),
				'btn-next'                 => esc_html__( 'Next', 'besa' ),
				'btn-start'                => esc_html__( 'Start', 'besa' ),
				'btn-no'                   => esc_html__( 'Cancel', 'besa' ),
				'btn-plugins-install'      => esc_html__( 'Install', 'besa' ),
				'btn-child-install'        => esc_html__( 'Install', 'besa' ),
				'btn-content-install'      => esc_html__( 'Install', 'besa' ),
				'btn-import'               => esc_html__( 'Import', 'besa' ),
				'btn-license-activate'     => esc_html__( 'Activate', 'besa' ),
				'btn-license-skip'         => esc_html__( 'Later', 'besa' ),

				/* translators: Theme Name */
				'license-header%s'         => esc_html__( 'Activate %s', 'besa' ),
				/* translators: Theme Name */
				'license-header-success%s' => esc_html__( '%s is Activated', 'besa' ),
				/* translators: Theme Name */
				'license%s'                => esc_html__( 'Enter your license key to enable remote updates and theme support.', 'besa' ),
				'license-label'            => esc_html__( 'License key', 'besa' ),
				'license-success%s'        => esc_html__( 'The theme is already registered, so you can go to the next step!', 'besa' ),
				'license-json-success%s'   => esc_html__( 'Your theme is activated! Remote updates and theme support are enabled.', 'besa' ),
				'license-tooltip'          => esc_html__( 'Need help?', 'besa' ),

				/* translators: Theme Name */
				'welcome-header%s'         => esc_html__( 'Welcome to %s', 'besa' ),
				'welcome-header-success%s' => esc_html__( 'Hi. Welcome back', 'besa' ),
				'welcome%s'                => esc_html__( 'This wizard will set up your theme, install plugins, and import content. It is optional & should take only a few minutes.', 'besa' ),
				'welcome-success%s'        => esc_html__( 'You may have already run this theme setup wizard. If you would like to proceed anyway, click on the "Start" button below.', 'besa' ),

				'child-header'         => esc_html__( 'Install Child Theme', 'besa' ),
				'child-header-success' => esc_html__( 'You\'re good to go!', 'besa' ),
				'child'                => esc_html__( 'Let\'s build & activate a child theme so you may easily make theme changes.', 'besa' ),
				'child-success%s'      => esc_html__( 'Your child theme has already been installed and is now activated, if it wasn\'t already.', 'besa' ),
				'child-action-link'    => esc_html__( 'Learn about child themes', 'besa' ),
				'child-json-success%s' => esc_html__( 'Awesome. Your child theme has already been installed and is now activated.', 'besa' ),
				'child-json-already%s' => esc_html__( 'Awesome. Your child theme has been created and is now activated.', 'besa' ),

				'plugins-header'         => esc_html__( 'Install Plugins', 'besa' ),
				'plugins-header-success' => esc_html__( 'You\'re up to speed!', 'besa' ),
				'plugins'                => esc_html__( 'Let\'s install some essential WordPress plugins to get your site up to speed.', 'besa' ),
				'plugins-success%s'      => esc_html__( 'The required WordPress plugins are all installed and up to date. Press "Next" to continue the setup wizard.', 'besa' ),
				'plugins-action-link'    => esc_html__( 'Advanced', 'besa' ),

				'import-header'      => esc_html__( 'Import Content', 'besa' ),
				'import'             => esc_html__( 'Let\'s import content to your website, to help you get familiar with the theme.', 'besa' ),
				'import-action-link' => esc_html__( 'Advanced', 'besa' ),

				'ready-header'      => esc_html__( 'All done. Have fun!', 'besa' ),

				/* translators: Theme Author */
				'ready%s'           => esc_html__( 'Your theme has been all set up. Enjoy your new theme by %s.', 'besa' ),
				'ready-action-link' => esc_html__( 'Extras', 'besa' ),
				'ready-big-button'  => esc_html__( 'View your website', 'besa' ),
				'ready-link-1'      => sprintf( '<a href="%1$s" target="_blank">%2$s</a>', 'https://tickets.thembay.com/', esc_html__( 'Ticket System', 'besa' ) ),
				'ready-link-2'      => sprintf( '<a href="%1$s">%2$s</a>', 'https://docs.thembay.com/besa/', esc_html__( 'Documentation', 'besa' ) ),
				'ready-link-3'      => sprintf( '<a href="%1$s">%2$s</a>', 'https://www.youtube.com/c/thembay/', esc_html__( 'Video Tutorials', 'besa' ) ),
				'ready-link-4'      => sprintf( '<a href="%1$s">%2$s</a>', 'https://forums.thembay.com/', esc_html__( 'Forums', 'besa' ) ),
			)
		);

	}

	public function render_child_functions_php( $output, $slug ) {
    $slug_no_hyphens = strtolower( preg_replace( '#[^a-zA-Z]#', '', $slug ) );
    $output = "<?php
	/**
	 * @version    1.0
	 * @package    {$slug_no_hyphens}
	 * @author     Thembay Team <support@thembay.com>
	 * @copyright  Copyright (C) 2019 Thembay.com. All Rights Reserved.
	 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
	 *
	 * Websites: https://thembay.com
	 */
  function {$slug_no_hyphens}_child_enqueue_styles() {
    wp_enqueue_style( '{$slug}-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( '{$slug}-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( '{$slug}-style' ),
        wp_get_theme()->get('Version')
    );
  }

	add_action(  'wp_enqueue_scripts', '{$slug_no_hyphens}_child_enqueue_styles' );\n
	";
  
    // Let's remove the tabs so that it displays nicely.
    $output = trim( preg_replace( '/\t+/', '', $output ) );
  
    // Filterable return.
    return $output;
  }
  
  public function render_child_style_css( $output, $slug, $parent, $author, $version ) {
		$render_output = "/**
* Theme Name: {$parent} Child
* Description: This is a child theme for {$parent}
* Author: Thembay
* Author URI: https://thembay.com/
* Version: {$version}
* Template: {$slug}
*/\n

/*  [ Add your custom css below ]
- - - - - - - - - - - - - - - - - - - - */";

		return $render_output;
	}

	public function after_import_setup( $selected_import ) {
		$_imports = $this->import_files();
		$selected_import = $_imports[ $selected_import ];
		$check_oneclick  = get_option( 'besa_check_oneclick', [] );
		$this->setup_options_after_import();
		$this->set_demo_menus();
		wp_delete_post( 1, true );

		// setup Home page
		$home = get_page_by_path( $selected_import['home'] );
		if ( $home ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home->ID );
		}

		if ( count( $check_oneclick ) <= 0 ) {
			$this->setup_mailchimp();
		}

		if ( ! isset( $check_oneclick[ $selected_import['home'] ] ) ) {
			$this->import_revslider( $selected_import['rev_sliders'] );
			$check_oneclick[ $selected_import['home'] ] = true;
			update_option( 'besa_check_oneclick', $check_oneclick );
		}

	}

	private function import_revslider( $revsliders ) {
		if ( class_exists( 'RevSliderAdmin' ) ) {
			require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php';
			$my_filesystem = new WP_Filesystem_Direct( array() );

			$revslider = new RevSlider();
			foreach ( $revsliders as $slider ) {
				$pathSlider = trailingslashit( ( wp_upload_dir() )['path'] ) . basename( $slider );
				if ( $this->download_revslider( $my_filesystem, $slider, $pathSlider ) ) {
					$_FILES['import_file']['error']    = UPLOAD_ERR_OK;
					$_FILES['import_file']['tmp_name'] = $pathSlider;
					$revslider->importSliderFromPost( true, 'none' );
				}

			}
		}
	}

	/**
	 * @param $filesystem WP_Filesystem_Direct
	 *
	 * @return bool
	 */
	private function download_revslider( $filesystem, $slider, $pathSlider ) {
		return $filesystem->copy( $slider, $pathSlider, true );
	}

	private function setup_mailchimp() {
		$mailchimp = get_page_by_title( 'Newsletter', OBJECT, 'mc4wp-form' );
		if ( $mailchimp ) {
			update_option( 'mc4wp_default_form_id', $mailchimp->ID );
		}
  	}
  
	public function setup_options_after_import() {
		$cpt_support = ['tbay_megamenu', 'tbay_footer', 'tbay_header', 'post', 'page']; 
		update_option( 'elementor_cpt_support', $cpt_support);
		update_option( 'elementor_disable_color_schemes', 'yes'); 
		update_option( 'elementor_disable_typography_schemes', 'yes');
		update_option( 'elementor_container_width', '1200');
		update_option( 'elementor_viewport_lg', '1200');  
		update_option( 'elementor_space_between_widgets', '0');
		update_option( 'elementor_load_fa4_shim', 'yes');
		
		$this->update_option_woocommerce();
		
		$this->update_option_yith_wcwl();
		$this->update_option_yith_compare();
		$this->update_option_yith_brands();
		$this->update_option_woof(); 

		/**Vendor**/
		$this->update_option_dokan();
		$this->update_option_wcmp();
		$this->update_option_wcfm();
		$this->update_option_wcvendors();
	}	

	private function update_option_woocommerce() {
		if( !class_exists( 'WooCommerce' ) ) return;

		$shop 		= get_page_by_path( 'shop' );
		$cart 		= get_page_by_path( 'shopping-cart' );
		$checkout 	= get_page_by_path( 'checkout' );
		$myaccount 	= get_page_by_path( 'my-account' );
		$terms 		= get_page_by_path( 'terms-of-use' );
		if ( $shop ) {
			update_option( 'woocommerce_shop_page_id', $shop->ID );
		}
		
		if ( $cart ) {
			update_option( 'woocommerce_cart_page_id', $cart->ID );
		}
		
		if ( $checkout ) {
			update_option( 'woocommerce_checkout_page_id', $checkout->ID );
		}
		
		if ( $myaccount ) {
			update_option( 'woocommerce_myaccount_page_id', $myaccount->ID );
		}
		
		if ( $terms ) {
			update_option( 'woocommerce_terms_page_id', $terms->ID );
		}
	}

	private function update_option_yith_wcwl() {
		if( !class_exists( 'YITH_WCWL' ) ) return;

		/**YITH Wishlist**/
		update_option( 'yith_wcwl_add_to_wishlist_icon', 'none'); 
		update_option( 'yith_wcwl_button_position', 'shortcode' ); 
		update_option( 'yith_wcwl_price_show', 'yes' ); 
		update_option( 'yith_wcwl_stock_show', 'yes' ); 
		update_option( 'yith_wcwl_add_to_cart_show', 'yes' ); 
		update_option( 'yith_wcwl_show_remove', 'no' ); 
		update_option( 'yith_wcwl_repeat_remove_button', 'yes' ); 
		update_option( 'yith_wcwl_enable_share', 'no' ); 
		update_option( 'yith_wcwl_wishlist_title', '' ); 
	}

	private function update_option_yith_compare() {
		if( !class_exists( 'YITH_Woocompare' ) ) return;

		/**YITH Compare**/
		update_option( 'yith_woocompare_compare_button_in_products_list', 'no' ); 
		update_option( 'yith_woocompare_compare_button_in_product_page', 'no' ); 
	}

	private function update_option_yith_brands() {
		if( !class_exists( 'YITH_WCBR' ) ) return;

		/**YITH Brands**/
		update_option( 'yith_wcbr_single_product_brands_content', 'name' );
	}

	private function update_option_woof() {
		if( !class_exists( 'WOOF' ) ) return;

		/**WOOF**/
		$settings = get_option('woof_settings');

		/**Price**/
		$settings['by_price']['show'] = '1';
		$settings['by_price']['title_text'] = esc_html__('Price', 'besa');

		/**Categories**/
		$settings['tax']['product_cat'] = '1';
		$settings['show_title_label']['product_cat'] = '1';
		$settings['custom_tax_label']['product_cat'] = esc_html__('Product Categories', 'besa');

		/**Size**/
		$settings['tax']['pa_size'] = '1';
		$settings['show_title_label']['pa_size'] = '1';
		$settings['custom_tax_label']['pa_size'] = esc_html__('Product Size', 'besa');

		/**Color**/
		$settings['tax']['pa_color'] = '1';
		$settings['show_title_label']['pa_color'] = '1';
		$settings['custom_tax_label']['pa_color'] = esc_html__('Product Color', 'besa');

		/**Tag**/
		$settings['tax']['product_tag'] = '1';
		$settings['show_title_label']['product_tag'] = '1';
		$settings['custom_tax_label']['product_tag'] = esc_html__('Product Tags', 'besa');

		/**Brand**/
		if( class_exists( 'YITH_WCBR' ) ) {
			$settings['tax']['yith_product_brand'] = '1';
			$settings['show_title_label']['yith_product_brand'] = '1';
			$settings['custom_tax_label']['yith_product_brand'] = esc_html__('Brands', 'besa');
		}	

		update_option('woof_settings', $settings);
	}

	private function update_option_dokan() {
		if( !class_exists( 'WeDevs_Dokan' ) ) return;

		$dashboard = get_page_by_path( 'dashboard' );
		$settings = get_option('dokan_pages');
		if ( $dashboard ) {
			$settings['dashboard'] = $dashboard->ID;
			update_option( 'dokan_pages', $settings );
		}

	}

	private function update_option_wcmp() {
		if( !class_exists( 'WCMp' ) ) return;

		$settings_name = get_option('wcmp_general_settings_name', array());
		$settings_name['sold_by_catalog'] = 1;
		$settings_name['is_sellerreview'] = 1;
		$settings_name['is_singleproductmultiseller'] = 1;
		$settings_name['is_policy_on'] = 1;
		$settings_name['is_vendor_shipping_on'] = 1;
		update_option('wcmp_general_settings_name', $settings_name);
		
		$vendor_name = get_option('wcmp_vendor_general_settings_name', array());
		$dashboard = get_page_by_path( 'vendor-dashboard' );
		$vendor_name['wcmp_vendor'] = $dashboard->ID;
		update_option('wcmp_vendor_general_settings_name', $vendor_name);
		
		
		$capabilities = get_option('wcmp_capabilities_product_settings_name', array());
		$capabilities['is_submit_coupon'] = 1;
		$capabilities['is_published_coupon'] = 1;
		update_option('wcmp_capabilities_product_settings_name', $capabilities);

	}

	private function update_option_wcfm() {
		if( !class_exists( 'WCFMmp' ) ) return;

		$theme_color 		= '#fa4f26';
		$theme_color_hover 	= '#e14722';
		$theme_star 		= '#FF912C';
		$body_bg 			= '#f5f5f5';
		
		/**Dashboard**/
		$wcfm_options['quick_access_disabled'] = $wcfm_options['float_button_disabled'] = 'yes';
		
		/**Modules**/
		$wcfm_options = get_option( 'wcfm_options', array() );
		$wcfm_options['module_options']['product_mulivendor'] = 'yes';
		
		/**Marketplace Settings**/
		$wcfm_marketplace_options = get_option( 'wcfm_marketplace_options', array() );
		$wcfm_marketplace_options['store_ppp'] = 8;
		update_option('wcfm_marketplace_options', $wcfm_marketplace_options);
		
		/**Vendor Registration**/
		update_option('wcfmvm_hide_become_vendor', ''); 
		update_option('wcfmvm_required_approval', 'yes');
		
		/**Store Style**/
		$wcfm_store_color_settings = get_option( 'wcfm_store_color_settings' );
		$wcfm_store_color_settings['header_icon'] = $theme_color;
		$wcfm_store_color_settings['tabs_active_text'] = $theme_color;
		$wcfm_store_color_settings['ctore_card_highlight'] = $theme_color;
		$wcfm_store_color_settings['button_bg'] = $theme_color;
		$wcfm_store_color_settings['button_active_bg'] = $theme_color;
		$wcfm_store_color_settings['start_rating'] = $theme_star;
		update_option('wcfm_store_color_settings', $wcfm_store_color_settings);
		
		/**Dashboard Style**/
		$wcfm_options['wc_frontend_manager_base_highlight_color_settings'] = $theme_color;
		$wcfm_options['wc_frontend_manager_secondary_font_color_settings'] = $theme_color;
		$wcfm_options['wc_frontend_manager_menu_icon_active_bg_color_settings'] = $theme_color;
		
		
		update_option('wcfm_options', $wcfm_options);
		
		/**Registration Form Fields**/
		$registration = get_option( 'wcfmvm_registration_static_fields' );
		$registration['first_name'] = $registration['terms'] = $registration['phone'] = $registration['last_name'] = $registration['user_name'] = $registration['address'] = 'yes';
		
		$terms_page = get_page_by_path( 'terms-of-use' );
		$registration['terms_page'] = $terms_page->ID;
		
		update_option('wcfmvm_registration_static_fields', $registration);

		/**Membership**/
		$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
		$wcfm_membership_options['membership_reject_rules']['required_approval'] = 'yes';
		$wcfm_membership_options['membership_color_settings']['wcfmvm_progress_bar_color_settings'] = $theme_color;
		$wcfm_membership_options['membership_color_settings']['wcfmvm_membership_table_head_title_bg_color_settings'] = $theme_color;
		$wcfm_membership_options['membership_color_settings']['wcfmvm_membership_table_head_bg_color_settings'] = $body_bg;
		$wcfm_membership_options['membership_color_settings']['wcfmvm_membership_table_head_price_color_settings'] = '#000000';
		$wcfm_membership_options['membership_color_settings']['wcfmvm_membership_table_head_price_desc_color_settings'] = '#999999';
		$wcfm_membership_options['membership_color_settings']['wcfmvm_membership_table_bg_heighlighter_color_settings'] = '#f5f5f5';
		$wcfm_membership_options['membership_color_settings']['wcfmvm_membership_table_button_bg_color_settings'] = $theme_color;
		$wcfm_membership_options['membership_color_settings']['wcfmvm_membership_table_button_bg_hover_color_settings'] = $theme_color_hover;
		$wcfm_membership_options['membership_color_settings']['wcfmvm_membership_preview_plan_color_settings'] = $theme_color;
		$wcfm_membership_options['membership_color_settings']['wcfmvm_membership_preview_plan_text_color_settings'] = '#ffffff';

		$membership_page = get_page_by_path( 'vendor-membership' );
		$wcfm_membership_options['membership_type_settings']['wcfm_custom_plan_page']  = $membership_page->ID;

		update_option('wcfm_membership_options', $wcfm_membership_options);
	}
	private function update_option_wcvendors() {
		if( !class_exists( 'WC_Vendors' ) ) return;

		update_option('wcvendors_vendor_allow_registration', 'yes');
	}

	public function set_demo_menus() {
		$main_menu       = get_term_by( 'name', 'Main Menu', 'nav_menu' );
		$category_menu   = get_term_by( 'name', 'Category Menu Image', 'nav_menu' );

		set_theme_mod(
			'nav_menu_locations',
			array(
				'primary'  => $main_menu->term_id,
				'mobile-menu' => $main_menu->term_id,
				'nav-category-menu' => $category_menu->term_id
			)
		);
	}

	public function import_files_type_demo(){
		$prefix = '';

		if( class_exists('WC_Vendors') ) {
			$prefix = 'wcvendors';
		}

		if( class_exists('WCFMmp') ) {
			$prefix = 'wcfm';
		}

		if( class_exists('WCMp') ) {
			$prefix = 'wcmp';
		}

		if( class_exists('WeDevs_Dokan') ) {
			$prefix = 'dokan';
		}

		return $prefix;
	}

	private function import_files_dokan(){
		
		$prefix_name = '';

		if( class_exists('WeDevs_Dokan') ) {
			$prefix_name = 'Dokan ';
		}

		return array(
			array(
				'import_file_name'           => $prefix_name.'Home 1',
				'home'                       => 'home-1',
				'import_file_url'          	 => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/data.xml",
				'import_widget_file_url'     => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/widgets.wie",
				'import_redux'         => array(
					array(
						'file_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/home-1/redux_options.json",
						'option_name' => 'besa_tbay_theme_options',
					),
				),
				'rev_sliders'                => [
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/revslider/home-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/revslider/home-2.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/revslider/home-3.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/revslider/slider-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/revslider/slider-flash-sale.zip",
				],
				'import_preview_image_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/home-1/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'besa' ),
				'preview_url'                => 'https://elementor.thembay.com/besa/',
			),
			array(
				'import_file_name'           => $prefix_name.'Home 2',
				'home'                       => 'home-2',
				'import_file_url'          	 => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/data.xml",
				'import_widget_file_url'     => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/widgets.wie",
				'import_redux'         => array(
					array(
						'file_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/home-2/redux_options.json",
						'option_name' => 'besa_tbay_theme_options',
					),
				),
				'rev_sliders'                => [
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/revslider/home-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/revslider/home-2.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/revslider/home-3.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/revslider/slider-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/revslider/slider-flash-sale.zip",
				],
				'import_preview_image_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/home-2/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'besa' ),
				'preview_url'                => 'https://elementor.thembay.com/besa/home-2',
			), 
			array(
				'import_file_name'           => $prefix_name.'Home 3',
				'home'                       => 'home-3',
				'import_file_url'          	 => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/data.xml",
				'import_widget_file_url'     => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/widgets.wie",
				'import_redux'         => array(
					array(
						'file_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/home-3/redux_options.json",
						'option_name' => 'besa_tbay_theme_options',
					),
				),
				'rev_sliders'                => [
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/revslider/home-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/revslider/home-2.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/revslider/home-3.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/revslider/slider-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/revslider/slider-flash-sale.zip",
				],
				'import_preview_image_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/dokan/home-3/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'besa' ),
				'preview_url'                => 'https://elementor.thembay.com/besa/home-3/',
			),
		);
	}

	private function import_files_wcfm(){
		
		$prefix_name = 'WCFM ';

		return array(
			array(
				'import_file_name'           => $prefix_name.'Home 1',
				'home'                       => 'home-1',
				'import_file_url'          	 => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/data.xml",
				'import_widget_file_url'     => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/widgets.wie",
				'import_redux'         => array(
					array(
						'file_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/home-1/redux_options.json",
						'option_name' => 'besa_tbay_theme_options',
					),
				),
				'rev_sliders'                => [
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/revslider/home-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/revslider/home-2.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/revslider/home-3.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/revslider/slider-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/revslider/slider-flash-sale.zip",
				],
				'import_preview_image_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/home-1/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'besa' ),
				'preview_url'                => 'https://elementor.thembay.com/besa_wcfm/',
			),
			array(
				'import_file_name'           => $prefix_name.'Home 2',
				'home'                       => 'home-2',
				'import_file_url'          	 => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/data.xml",
				'import_widget_file_url'     => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/widgets.wie",
				'import_redux'         => array(
					array(
						'file_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/home-2/redux_options.json",
						'option_name' => 'besa_tbay_theme_options',
					),
				),
				'rev_sliders'                => [
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/revslider/home-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/revslider/home-2.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/revslider/home-3.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/revslider/slider-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/revslider/slider-flash-sale.zip",
				],
				'import_preview_image_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/home-2/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'besa' ),
				'preview_url'                => 'https://elementor.thembay.com/besa_wcfm/home-2',
			), 
			array(
				'import_file_name'           => $prefix_name.'Home 3',
				'home'                       => 'home-3',
				'import_file_url'          	 => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/data.xml",
				'import_widget_file_url'     => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/widgets.wie",
				'import_redux'         => array(
					array(
						'file_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/home-3/redux_options.json",
						'option_name' => 'besa_tbay_theme_options',
					),
				),
				'rev_sliders'                => [
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/revslider/home-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/revslider/home-2.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/revslider/home-3.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/revslider/slider-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/revslider/slider-flash-sale.zip",
				],
				'import_preview_image_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcfm/home-3/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'besa' ),
				'preview_url'                => 'https://elementor.thembay.com/besa_wcfm/home-3/',
			),
		);
	}

	private function import_files_wcmp(){
		
		$prefix_name = 'WCMP ';

		return array(
			array(
				'import_file_name'           => $prefix_name.'Home 1',
				'home'                       => 'home-1',
				'import_file_url'          	 => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/data.xml",
				'import_widget_file_url'     => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/widgets.wie",
				'import_redux'         => array(
					array(
						'file_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/home-1/redux_options.json",
						'option_name' => 'besa_tbay_theme_options',
					),
				),
				'rev_sliders'                => [
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/revslider/home-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/revslider/home-2.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/revslider/home-3.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/revslider/slider-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/revslider/slider-flash-sale.zip",
				],
				'import_preview_image_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/home-1/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'besa' ),
				'preview_url'                => 'https://elementor.thembay.com/besa_wcmp/',
			),
			array(
				'import_file_name'           => $prefix_name.'Home 2',
				'home'                       => 'home-2',
				'import_file_url'          	 => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/data.xml",
				'import_widget_file_url'     => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/widgets.wie",
				'import_redux'         => array(
					array(
						'file_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/home-2/redux_options.json",
						'option_name' => 'besa_tbay_theme_options',
					),
				),
				'rev_sliders'                => [
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/revslider/home-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/revslider/home-2.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/revslider/home-3.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/revslider/slider-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/revslider/slider-flash-sale.zip",
				],
				'import_preview_image_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/home-2/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'besa' ),
				'preview_url'                => 'https://elementor.thembay.com/besa_wcmp/home-2',
			), 
			array(
				'import_file_name'           => $prefix_name.'Home 3',
				'home'                       => 'home-3',
				'import_file_url'          	 => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/data.xml",
				'import_widget_file_url'     => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/widgets.wie",
				'import_redux'         => array(
					array(
						'file_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/home-3/redux_options.json",
						'option_name' => 'besa_tbay_theme_options',
					),
				),
				'rev_sliders'                => [
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/revslider/home-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/revslider/home-2.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/revslider/home-3.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/revslider/slider-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/revslider/slider-flash-sale.zip",
				],
				'import_preview_image_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcmp/home-3/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'besa' ),
				'preview_url'                => 'https://elementor.thembay.com/besa_wcmp/home-3/',
			),
		);
	}

	
	private function import_files_wcvendors(){
		
		$prefix_name = 'wcvendors ';

		return array(
			array(
				'import_file_name'           => $prefix_name.'Home 1',
				'home'                       => 'home-1',
				'import_file_url'          	 => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/data.xml",
				'import_widget_file_url'     => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/widgets.wie",
				'import_redux'         => array(
					array(
						'file_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/home-1/redux_options.json",
						'option_name' => 'besa_tbay_theme_options',
					),
				),
				'rev_sliders'                => [
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/revslider/home-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/revslider/home-2.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/revslider/home-3.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/revslider/slider-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/revslider/slider-flash-sale.zip",
				],
				'import_preview_image_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/home-1/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'besa' ),
				'preview_url'                => 'https://elementor.thembay.com/besa_wcvendors/',
			),
			array(
				'import_file_name'           => $prefix_name.'Home 2',
				'home'                       => 'home-2',
				'import_file_url'          	 => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/data.xml",
				'import_widget_file_url'     => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/widgets.wie",
				'import_redux'         => array(
					array(
						'file_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/home-2/redux_options.json",
						'option_name' => 'besa_tbay_theme_options',
					),
				),
				'rev_sliders'                => [
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/revslider/home-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/revslider/home-2.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/revslider/home-3.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/revslider/slider-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/revslider/slider-flash-sale.zip",
				],
				'import_preview_image_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/home-2/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'besa' ),
				'preview_url'                => 'https://elementor.thembay.com/besa_wcvendors/home-2',
			), 
			array(
				'import_file_name'           => $prefix_name.'Home 3',
				'home'                       => 'home-3',
				'import_file_url'          	 => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/data.xml",
				'import_widget_file_url'     => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/widgets.wie",
				'import_redux'         => array(
					array(
						'file_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/home-3/redux_options.json",
						'option_name' => 'besa_tbay_theme_options',
					),
				),
				'rev_sliders'                => [
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/revslider/home-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/revslider/home-2.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/revslider/home-3.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/revslider/slider-1.zip",
					"https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/revslider/slider-flash-sale.zip",
				],
				'import_preview_image_url'   => "https://bitbucket.org/devthembay/update-plugin/raw/master/demosamples/besa/wcvendors/home-3/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'besa' ),
				'preview_url'                => 'https://elementor.thembay.com/besa_wcvendors/home-3/',
			),
		);
	}

	public function import_files(){
		$prefix = $this->import_files_type_demo();

		switch ($prefix) {
			case 'dokan':
				return $this->import_files_dokan();
				break;

			case 'wcfm':
				return $this->import_files_wcfm();
				break;

			case 'wcmp':
				return $this->import_files_wcmp();
				break;

			case 'wcvendors':
				return $this->import_files_wcvendors();
				break;
			
			default:
				return $this->import_files_dokan();
				break;
		}

	}
}

return new Besa_Merlin_Config();
