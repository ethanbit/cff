<?php
$base_dir = plugin_dir_path(__DIR__) . $plugin_dir;
require_once $base_dir . '/inc/class_wp_notifications_history.php';
class WpNotificationsHistory
{
  public function display()
  {
    ?>
		<div class="wrap">
			<h2>Notifications History</h2>

			<form method="post">
        <?php
        $history = new WpNotifications_History();
        $history->prepare_items();
        $history->display();?>
      </form>
		</div>
	<?php
  }

  public function createHistory($type, $user_id = '', $group_id = '', $count)
  {
    global $wpdb;
    $wpdb->insert("{$wpdb->prefix}notifications_history", [
      'type' => $type,
      'user_id' => $user_id,
      'group_id' => $group_id,
      'count' => $count,
      'success' => 0,
      'created_at' => date('Y-m-d H:i:s'),
    ]);
    return $wpdb->insert_id;
  }

  public function updateHistory($history_id)
  {
    global $wpdb;
    $successCounter = $wpdb->get_row(
      "SELECT success FROM {$wpdb->prefix}notifications_history WHERE id = $history_id",
      ARRAY_A
    );
    $success = $successCounter['success'] + 1;
    $wpdb->update(
      "{$wpdb->prefix}notifications_history",
      ['success' => $success],
      ['id' => $history_id]
    );
  }
}
