<?php
/*
Plugin Name: WP Notifications
Description: Google Firebase push notifications. Easily manage to send flash messages to the users of users devices using their device token.
Author: NBD - Skype: mr.nbduc
Version: 1.0
*/

$plugin_dir = basename(__DIR__);
$base_dir = plugin_dir_path(__DIR__) . $plugin_dir;

require_once $base_dir . '/inc/class_wp_notifications_data.php';
require_once $base_dir . '/wp_notifications_send.php';
require_once $base_dir . '/wp_notifications_groups.php';
require_once $base_dir . '/wp_notifications_history.php';
require_once $base_dir . '/wp_notifications_woo_changed.php';
define('WP_NOTIFICATIONS_URL', plugin_dir_url(__FILE__));

class WpNotifications_Plugin
{
  // class instance
  static $instance;

  // Devices WP_List_Table object
  public $listingDevices;

  // class constructor
  public function __construct()
  {
    //add_action('init', [$this, 'style_script']);
    add_action('admin_enqueue_scripts', [$this, 'style_script']);
    add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
    add_action('admin_menu', [$this, 'plugin_menu']);

    $this->_add_devices_table();
  }

  public function style_script()
  {
    if (wp_doing_ajax()) {
      return;
    }
    wp_enqueue_style(
      'wp_notifications-bootstrap',
      WP_NOTIFICATIONS_URL . 'assets/css/bootstrap.min.css',
      [],
      '1.0'
    );
    wp_enqueue_style(
      'wp_notifications-admin',
      WP_NOTIFICATIONS_URL . 'assets/css/wp_notifications_admin.css',
      [],
      '1.0'
    );
    wp_enqueue_script(
      'wp_notifications-bootstrapjs',
      WP_NOTIFICATIONS_URL . 'assets/js/bootstrap.min.js',
      [],
      '1.0'
    );
    wp_enqueue_script(
      'wp_notifications-js',
      WP_NOTIFICATIONS_URL . 'assets/js/wp_notifications.js',
      [],
      '1.0'
    );
  }

  private function _add_devices_table()
  {
    global $wpdb;
    $number_of_tables = $wpdb->query(
      $wpdb->prepare("SHOW TABLES LIKE '%notifications_devices'")
    );
    $checkExistTable = (bool) (1 == $number_of_tables);

    if (!$checkExistTable) {
      $sql = "CREATE TABLE {$wpdb->prefix}notifications_devices (
							`id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
              `device_id` TEXT,
							`user_id` BIGINT( 20 ) NOT NULL ,
              `device_type` TINYINT( 1 ) NOT NULL,
              `status` TINYINT( 1 ) NOT NULL,
              `device_os` VARCHAR( 255 ) DEFAULT NULL,
              `is_notify` TINYINT( 1 ) NOT NULL DEFAULT '1',
              `update_date` int DEFAULT NULL,
              `register_date` int NOT NULL DEFAULT '0',
							PRIMARY KEY  ( id ),
							UNIQUE KEY `id` (`id`)
						) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

      require_once ABSPATH . 'wp-admin/includes/upgrade.php';
      dbDelta($sql);
    }

    return;
  }

  public static function set_screen($status, $option, $value)
  {
    return $value;
  }

  public function plugin_menu()
  {
    $hook = add_menu_page(
      'WP Notifications Page',
      'WP Notifications',
      'manage_options',
      'wp_notifications_devices',
      [$this, 'wp_notifications_devices_page'],
      '',
      5
    );
    add_submenu_page(
      'wp_notifications_devices',
      'Send Notifications',
      'Send Notifications',
      'manage_options',
      'wp_notifications_send',
      [new WpNotificationsSend(), 'display'],
      '',
      5
    );
    add_submenu_page(
      'wp_notifications_devices',
      'Notifications History',
      'Notifications History',
      'manage_options',
      'wp_notifications_history',
      [new WpNotificationsHistory(), 'display'],
      '',
      5
    );
    add_submenu_page(
      'wp_notifications_devices',
      'Groups',
      'Groups',
      'manage_options',
      'wp_notifications_groups',
      [new WpNotificationGroups(), 'display'],
      '',
      5
    );

    add_action("load-$hook", [$this, 'screen_option']);
  }

  /**
   * Plugin List page
   */
  public function wp_notifications_devices_page()
  {
    ?>
		<div class="wrap">
			<h2>Listing Devices</h2>

			<form method="post">
      <?php
      $this->listingDevices->prepare_items();
      $this->listingDevices->display();
      ?>
      </form>
		</div>
    <!-- Modal send msg -->
    <div class="modal fade" id="msgModal" tabindex="-1" role="dialog" aria-labelledby="msgModal" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <form method="post" id="sendtouser">
          <input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo wp_create_nonce(
            'sp_notifications'
          ); ?>">
          <input type="hidden" name="userid" id="userid" value="">
          <input type="hidden" name="action" id="action" value="sendmsg">
          <input type="hidden" name="from" id="from" value="ajax">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="msgModalLabel">Send Message to:</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="alert alert-success" style="display:none" role="alert"></div>
              <div class="form-group" id="msgtouser"></div>
              <div class="form-group">
                <label for="title">Title</label>
                <input type="text" class="form-control" id="title" name="title"/>
              </div>
              <div class="form-group">
                <label for="msg">Message:</label>
                <textarea class="form-control" id="msg" name="msg" rows="3"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <span type="button" class="btn btn-secondary" data-dismiss="modal">Close</span>
              <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
          </div>
        </form>
      </div>
    </div>
	<?php
  }

  /**
   * Screen options
   */
  public function screen_option()
  {
    $option = 'per_page';
    $args = [
      'label' => 'Devices',
      'default' => 20,
      'option' => 'devices_per_page',
    ];

    add_screen_option($option, $args);

    $this->listingDevices = new WpNotifications_List();
  }

  /** Singleton instance */
  public static function get_instance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }
}

add_action('plugins_loaded', function () {
  WpNotifications_Plugin::get_instance();

  add_action('user_new_form', function () {
    showGroupOnUserPage();
  });

  add_action('show_user_profile', function () {
    showGroupOnUserPage();
  });
  add_action('edit_user_profile', function () {
    showGroupOnUserPage();
  });
  add_action('profile_update', 'SaveGroup');
  add_action('delete_user', 'processDeleteUser');
});

add_action('wp_ajax_sendmsg', [new WpNotificationsSend(), 'sendMessage']);
add_action('wp_ajax_nopriv_sendmsg', [
  new WpNotificationsSend(),
  'sendMessage',
]);

function showGroupOnUserPage()
{
  $groupObj = new WpNotificationGroups();
  return $groupObj->showGroupOnUserPage();
}

function SaveGroup()
{
  $groupObj = new WpNotificationGroups();
  return $groupObj->saveGroupUser();
}

function processDeleteUser()
{
  $groupObj = new WpNotificationGroups();
  return $groupObj->processDeleteUser();
}
?>
