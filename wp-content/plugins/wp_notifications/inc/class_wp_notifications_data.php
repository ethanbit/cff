<?php
if (!class_exists('WP_List_Table')) {
  require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WpNotifications_List extends WP_List_Table
{
  /** Class constructor */
  public function __construct()
  {
    parent::__construct([
      'singular' => __('Device', 'sp'), //singular name of the listed records
      'plural' => __('Devices', 'sp'), //plural name of the listed records
      'ajax' => false, //does this table support ajax?
    ]);
  }

  /**
   * Retrieve devices data from the database
   *
   * @param int $per_page
   * @param int $page_number
   *
   * @return mixed
   */
  public static function get_devices(
    $per_page = 5,
    $page_number = 1,
    $groupid = -1,
    $status = ''
  ) {
    global $wpdb;

    $sql = "SELECT * FROM {$wpdb->prefix}notifications_devices";

    if (!empty($_REQUEST['orderby'])) {
      $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
      $sql .= !empty($_REQUEST['order'])
        ? ' ' . esc_sql($_REQUEST['order'])
        : ' ASC';
    }
    $where = ['(device_id != "" AND device_id != -1)'];
    if ($status != '' and is_numeric($status)) {
      $where[] = 'status = ' . $status;
    }

    if (is_numeric($groupid) and $groupid != -1) {
      $where[] = 'group_id = ' . $groupid;
    }

    if (!empty($where)) {
      $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY id DESC';

    if ($per_page != -1) {
      $sql .= " LIMIT $per_page";
      $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;
    }

    $result = $wpdb->get_results($sql, 'ARRAY_A');

    return $result;
  }

  public static function get_devices_by_users($users, $status = 1)
  {
    global $wpdb;

    $sql = "SELECT device_id FROM {$wpdb->prefix}notifications_devices";

    $where = ['(device_id != "" AND device_id != -1)'];
    if ($status != '' and is_numeric($status)) {
      $where[] = 'status = ' . $status;
    }

    $where[] = "user_id IN ($users)";

    if (!empty($where)) {
      $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $result = $wpdb->get_results($sql, 'ARRAY_A');

    return $result;
  }

  /**
   * Delete a device record.
   *
   * @param int $id device id
   */
  public static function delete_devices($id)
  {
    global $wpdb;

    $wpdb->delete(
      "{$wpdb->prefix}notifications_devices",
      ['id' => $id],
      ['%d']
    );
  }

  /**
   * change Status a device record.
   *
   * @param int $id device id
   */
  public static function setStatus_devices($id, $status)
  {
    global $wpdb;

    if ($status == '') {
      return;
    }

    $setStatus = $status == 'active' ? 1 : 0;

    $wpdb->update(
      "{$wpdb->prefix}notifications_devices",
      ['status' => $setStatus],
      ['id' => $id]
    );
  }

  /**
   * Returns the count of records in the database.
   *
   * @return null|string
   */
  public static function record_count()
  {
    global $wpdb;

    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}notifications_devices WHERE device_id != '' AND device_id != -1";

    return $wpdb->get_var($sql);
  }

  /** Text displayed when no device data is available */
  public function no_items()
  {
    _e('No Device avaliable.', 'sp');
  }

  /**
   * Render a column when no column specific method exist.
   *
   * @param array $item
   * @param string $column_name
   *
   * @return mixed
   */
  public function column_default($item, $column_name)
  {
    switch ($column_name) {
      case 'device_id':
        return $item[$column_name];
      case 'device_os':
        return $item[$column_name];
      case 'user_id':
        return $item[$column_name];
      case 'status':
        return $item[$column_name];
      case 'action':
        return '<span data-userid="' .
          $item['user_id'] .
          '" data-user="' .
          $this->column_user_id($item) .
          '" id="sendnotification_' .
          $item['id'] .
          '" class="button action sendnotification"><span class="dashicons dashicons-email" style="display: inline-block;padding-top: 4px;"></span> Send Message</span>';
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }

  public function getStatus($item)
  {
    if ($item['status'] == 1) {
      return '<i class="device_active device_status">Active</i>';
    } else {
      return '<i class="device_inactive device_status">inActive</i>';
    }
  }

  /**
   * Render the bulk edit checkbox
   *
   * @param array $item
   *
   * @return string
   */
  function column_cb($item)
  {
    return sprintf(
      '<input type="checkbox" name="bulk-delete[]" value="%s" />',
      $item['id']
    );
  }

  function column_user_id($item)
  {
    global $wpdb;
    $query =
      "SELECT display_name FROM {$wpdb->prefix}users WHERE ID=" .
      $item['user_id'];
    $result = $wpdb->get_row($query);
    $html = "
    <p>ID: {$item['user_id']}</p>
    <p>Name: {$result->display_name}</p>
    ";
    return $html;
  }

  function column_status($item)
  {
    if ($item['status'] == 1) {
      $status = 'inactive';
      $class = 'device_active';
      $i = '<i>Active</i>';
    } else {
      $status = 'active';
      $class = 'device_inactive';
      $i = '<i>inActive</i>';
    }

    $updatestatus_nonce = wp_create_nonce('sp_devices');

    $a =
      '<a class="device_status ' .
      $class .
      '" href="?page=' .
      $_REQUEST['page'] .
      '&action=updatestatus&status=' .
      $status .
      '&device=' .
      $item['id'] .
      '&_wpnonce=' .
      $updatestatus_nonce .
      '">';

    return $a . $i . '</a>';
  }

  /**
   * Method for name column
   *
   * @param array $item an array of DB data
   *
   * @return string
   */
  function column_device_id($item)
  {
    $delete_nonce = wp_create_nonce('sp_devices');

    $title =
      '<strong>Device ID: <span>' . $item['device_id'] . '</span></strong>';

    $actions = [
      'delete' => sprintf(
        '<a href="?page=%s&action=%s&device=%s&_wpnonce=%s">Delete</a>',
        esc_attr($_REQUEST['page']),
        'delete',
        absint($item['id']),
        $delete_nonce
      ),
    ];

    return $title . $this->row_actions($actions);
  }

  /**
   *  Associative array of columns
   *
   * @return array
   */
  function get_columns()
  {
    $columns = [
      'cb' => '<input type="checkbox" />',
      'device_id' => __('Device Info', 'sp'),
      'device_os' => __('Device OS', 'sp'),
      'user_id' => __('User Info', 'sp'),
      'status' => __('Status', 'sp'),
      'action' => __('Action', 'sp'),
    ];

    return $columns;
  }

  /**
   * Columns to make sortable.
   *
   * @return array
   */
  public function get_sortable_columns()
  {
    $sortable_columns = [
      'status' => ['status', true],
      //'city' => ['city', false],
    ];

    return $sortable_columns;
  }

  /**
   * Returns an associative array containing the bulk action
   *
   * @return array
   */
  public function get_bulk_actions()
  {
    $actions = [
      'bulk-delete' => 'Delete',
      'bulk-active' => 'Active',
      'bulk-inactive' => 'inActive',
    ];

    return $actions;
  }

  /**
   * Handles data query and filter, sorting, and pagination.
   */
  public function prepare_items()
  {
    $this->_column_headers = $this->get_column_info();

    /** Process bulk action */
    $this->process_bulk_action();

    $per_page = $this->get_items_per_page('devices_per_page', 20);
    $current_page = $this->get_pagenum();
    $total_items = self::record_count();

    $this->set_pagination_args([
      'total_items' => $total_items, //WE have to calculate the total number of items
      'per_page' => $per_page, //WE have to determine how many items to show on a page
    ]);

    $this->items = self::get_devices($per_page, $current_page);
  }

  public function process_bulk_action()
  {
    //Detect when a bulk action is being triggered...
    if (
      $this->current_action() == 'delete' ||
      $this->current_action() == 'updatestatus'
    ) {
      $nonce = esc_attr($_REQUEST['_wpnonce']);
      if (!wp_verify_nonce($nonce, 'sp_devices')) {
        die('You dont have permisstion to do this action.');
        //wp_redirect(esc_url_raw(add_query_arg()));
      }
      switch ($this->current_action()) {
        case 'delete':
          $type = 'delete';
          self::delete_devices(absint($_GET['device']));
          break;
        case 'updatestatus':
          $type = 'status';
          self::setStatus_devices($_GET['device'], $_GET['status']);
          break;
        default:
          die('Wrong action.');
      }

      wp_redirect(
        '/wp-admin/admin.php?page=wp_notifications_devices&type=' . $type
      );
      exit();
    }

    // one action

    // bulk action
    if (isset($_POST['action']) || isset($_POST['action2'])) {
      $bulkAction =
        $_POST['action'] != -1 ? $_POST['action'] : $_POST['action2'];
      switch ($bulkAction) {
        case 'bulk-delete':
          $ids = esc_sql($_POST['bulk-delete']);
          $type = 'delete';
          // loop over the array of record ids and delete them
          foreach ($ids as $id) {
            self::delete_devices($id);
          }
          break;
        case 'bulk-active':
          $ids = esc_sql($_POST['bulk-active']);
          $type = 'status';
          foreach ($ids as $id) {
            self::setStatus_devices($id, 'active');
          }
          break;
        case 'bulk-inactive':
          $ids = esc_sql($_POST['bulk-inactive']);
          $type = 'status';
          foreach ($ids as $id) {
            self::setStatus_devices($id, 'inactive');
          }
          break;
      }

      wp_redirect(
        '/wp-admin/admin.php?page=wp_notifications_devices&type=' . $type
      );
      exit();
    }
  }
}
