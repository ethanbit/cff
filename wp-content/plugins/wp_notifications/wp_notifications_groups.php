<?php
$base_dir = plugin_dir_path(__DIR__) . $plugin_dir;
require_once $base_dir . '/inc/class_wp_notifications_groups.php';

class WpNotificationGroups
{
  public function display()
  {
    ?>
		<div class="wrap">
			<h2>Groups <a href="#" class="page-title-action" id="addnewgroup">Add New</a></h2>

			<form method="post">
        <?php
        $groups = new WpNotifications_Groups();
        $groups->prepare_items();
        $groups->display();
        ?>
      </form>
		</div>

    <!-- Modal Addnew/Edit -->
    <div class="modal fade" id="groupEditModal" tabindex="-1" role="dialog" aria-labelledby="groupEditModal" aria-hidden="true">
      <div class="modal-dialog" role="document">
      <form method="post">
        <input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo wp_create_nonce(
          'sp_groups'
        ); ?>">
        <input type="hidden" name="id" id="groupid" value="">
        <input type="hidden" name="action" id="action" value="updategroupname">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="groupEditModalLabel">Change Group Name</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="exampleInputEmail1">Group Name:</label>
              <input type="text" class="form-control" id="groupname" name="name" />
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

  public function showGroupOnUserPage()
  {
    $groupsObj = new WpNotifications_Groups();
    $groups = $groupsObj->get_groups(-1);

    if (isset($_GET['user_id']) and is_numeric($_GET['user_id'])) {
      $user_id = $_GET['user_id'];
    } else {
      $user_id = get_current_user_id();
    }
    $groupsUser = $groupsObj->get_group_user($user_id);
    $inGroups = [];
    foreach ($groupsUser as $g) {
      $inGroups[] = $g['group_id'];
    }
    ?>
    <h2>Groups</h2>
    <table class="form-table-usergroup" role="presentation">
      <tbody>
        <?php foreach ($groups as $group) {

          $checked = '';
          if (in_array($group['id'], $inGroups)) {
            $checked = 'checked';
          }
          ?>
        <tr id="password" class="user-pass1-wrap">
          <td width="50px"></td>
          <td>
            <label>
              <input <?php echo $checked; ?> type="checkbox" name="usergroup[]" value="<?php echo $group[
   'id'
 ]; ?>" />
              <?php echo $group['name']; ?>
            </label>
          </td>
        </tr>
        <?php
        } ?>
      </tbody>
    </table>
    <?php
  }

  public function saveGroupUser()
  {
    $action = $_POST['action'];
    if ($action == 'createuser') {
      $user = get_user_by('email', $_POST['email']);
      $data = [
        'user_id' => $user->ID,
        'groups' => $_POST['usergroup'],
      ];
      $this->_save($data);
    } elseif ($action == 'update') {
      $data = [
        'user_id' => $_POST['user_id'],
        'groups' => $_POST['usergroup'],
      ];
      $this->_save($data);
    }
  }

  private function _save($data)
  {
    global $wpdb;
    // remove old groups
    $wpdb->delete($wpdb->prefix . 'notifications_groups_users', [
      'user_id' => $data['user_id'],
    ]);

    if (count($data['groups'])) {
      for ($i = 0; $i < count($data['groups']); $i++) {
        $wpdb->insert($wpdb->prefix . 'notifications_groups_users', [
          'group_id' => $data['groups'][$i],
          'user_id' => $data['user_id'],
        ]);
      }
    }

    return true;
  }

  public function processDeleteUser()
  {
    global $wpdb;
    $user_id = $_GET['user'];
    $wpdb->delete($wpdb->prefix . 'notifications_groups_users', [
      'user_id' => $user_id,
    ]);

    return true;
  }
}
?>
