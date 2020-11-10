<?php
$base_dir = plugin_dir_path(__DIR__) . $plugin_dir;
require_once $base_dir . '/inc/class_wp_notifications_groups.php';
require_once $base_dir . '/inc/class_wp_notifications_data.php';
require_once $base_dir . '/wp_notifications_history.php';

class WpNotificationsSend
{
  public function display()
  {
    // send message to group
    $send = $this->sendMessage();

    $groups = new WpNotifications_Groups();
    $datas = $groups->get_groups(-1, 1);
    ?>
    <div class="wrap">
			<h2>Send Notifications</h2>
      <div class="container">
        <?php if ($send) { ?>
        <div class="alert alert-success" role="alert">
          <?php echo $send; ?>
        </div>
        <?php } ?>

        <div class="row">
          <div class="col-sm-12 col-md-3"></div>
          <div class="col-sm-12 col-md-6">
            <form method="post">
              <div class="row">
                <div class="col">
                  <div class="form-group">
                    <label for="exampleFormControlSelect1">Groups</label>
                    <select class="form-control" id="group" name="group">
                      <option>-- Select Group --</option>
                      <?php foreach ($datas as $data) {
                        $selected = '';
                        if ($data['id'] == $_POST['group']) {
                          $selected = 'selected';
                        }
                        echo '<option ' .
                          $selected .
                          ' value="' .
                          $data['id'] .
                          '">' .
                          $data['name'] .
                          '</option>';
                      } ?>
                    </select>
                  </div>
                </div>
                <div class="col">
                  <div class="form-group">
                    <label for="exampleFormControlSelect1">Status</label>
                    <select class="form-control" id="status" name="status">
                      <option value="1">Active</option>
                      <option value="0">inActive</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label for="exampleFormControlSelect1">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo $_POST[
                  'title'
                ]; ?>" />
              </div>
              <div class="form-group">
                <label for="exampleFormControlSelect1">Message</label>
                <textarea class="form-control" id="msg" name="msg" rows="5"><?php echo $_POST[
                  'msg'
                ]; ?></textarea>
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-primary">Send</button>
              </div>
            </form>
          </div>
          <div class="col-sm-12 col-md-3"></div>
        </div>
      </div>
		</div>
    <?php
  }

  public function sendMessage()
  {
    if (isset($_POST) and !empty(trim($_POST['msg']))) {
      $groupid = $_POST['group'];
      $userid = $_POST['userid'];
      $status = $_POST['status'];
      $title = $_POST['title'];
      $from = $_POST['from'];
      $msg = trim($_POST['msg']);
      $result = '';
      $devices = new WpNotifications_List();
      $history = new WpNotificationsHistory();

      if ($groupid) {
        $groups = new WpNotifications_Groups();
        $usersInGroup = $groups->get_users_by_group($groupid);
        $users = '';
        foreach ($usersInGroup as $user) {
          if ($users == '') {
            $users = $user['user_id'];
          } else {
            $users .= ',' . $user['user_id'];
          }
        }

        $datas = $devices->get_devices_by_users($users, $status);
        $history_id = $history->createHistory(
          'group',
          '',
          $groupid,
          count($datas)
        );
      } elseif ($userid) {
        $datas = $devices->get_devices_by_users($userid);
        $history_id = $history->createHistory(
          'user',
          $userid,
          '',
          count($datas)
        );
      }

      $success = 0;
      foreach ($datas as $data) {
        $deviceToken = $data['device_id'];
        if ($deviceToken != '' and strlen($deviceToken) > 20) {
          $result = $this->send($deviceToken, $title, $msg, $history_id);
          if ($result->success) {
            $success++;
          }
        }
      }

      $returnData = "Send success to $success/" . count($datas) . ' devices.';
      if ($from == 'ajax') {
        echo $returnData;
        exit();
      }

      return $returnData;
    }

    return false;
  }

  public function send($deviceToken, $title, $msg, $history_id)
  {
    $history = new WpNotificationsHistory();
    $FIREBASE_API_KEY =
      'AAAA6sCa7l8:APA91bEuWdYyeyD-GwxcDxvnr5VvvjEXYgChKRT-Q7HCRU_F-3wghqO_vyAP39etK0gQU-57G9GsJHCh-OgXpEfixt9H1wykzepmjxrk4-1Pfw91CqxFaEnudMsExB28nanEc8aM9Viu';

    ob_start();
    $sendData = [
      'body' => $msg,
      'title' => $title,
      'icon' => 'myicon' /*Default Icon*/,
      'sound' => 'mySound' /*Default sound*/,
      'image' =>
        'https://connect.cityfinefoods.com.au/resources/views/admin/images/admin_profile/1574571328.cff.png',
    ];

    $url = 'https://fcm.googleapis.com/fcm/send';

    $arrayToSend = [
      'to' => $deviceToken,
      'notification' => $sendData,
      'priority' => 'high',
    ];

    $json = json_encode($arrayToSend);

    $headers = [];
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: key=' . $FIREBASE_API_KEY;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //Send the request
    $response = curl_exec($ch);
    //Close request
    curl_close($ch);

    $data = ob_get_contents();
    ob_clean();

    $result = json_decode($data);
    // echo '<pre>';
    // print_r($result);
    // echo '</pre>' . __FILE__ . ': ' . __LINE__ . '';
    if ($result->success) {
      $history->updateHistory($history_id);
    }

    return $result;
  }

  // public function createHistory($type, $user_id = '', $group_id = '', $count)
  // {
  //   global $wpdb;
  //   $wpdb->insert("{$wpdb->prefix}notifications_history", [
  //     'type' => $type,
  //     'user_id' => $user_id,
  //     'group_id' => $group_id,
  //     'count' => $count,
  //     'success' => 0,
  //     'created_at' => date('Y-m-d H:i:s'),
  //   ]);
  //   return $wpdb->insert_id;
  // }

  // public function updateHistory($history_id)
  // {
  //   global $wpdb;
  //   $wpdb->update(
  //     "{$wpdb->prefix}notifications_history",
  //     ['success' => '`success` + 1'],
  //     ['id' => $history_id]
  //   );
  // }
}
?>
