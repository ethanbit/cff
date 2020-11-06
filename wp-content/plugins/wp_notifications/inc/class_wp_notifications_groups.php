<?php
if (!class_exists('WP_List_Table')) {
  require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WpNotifications_Groups extends WP_List_Table
{
  /** Class constructor */
  public function __construct()
  {
    parent::__construct([
      'singular' => __('Group', 'sp'), //singular name of the listed records
      'plural' => __('Groups', 'sp'), //plural name of the listed records
      'ajax' => false, //does this table support ajax?
    ]);
  }

  /**
   * Retrieve groups data from the database
   *
   * @param int $per_page
   * @param int $page_number
   *
   * @return mixed
   */
  public static function get_groups($per_page = 5, $page_number = 1)
  {
    global $wpdb;

    $sql = "SELECT * FROM {$wpdb->prefix}notifications_groups";

    if (!empty($_REQUEST['orderby'])) {
      $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
      $sql .= !empty($_REQUEST['order'])
        ? ' ' . esc_sql($_REQUEST['order'])
        : ' ASC';
    }

    if ($per_page != -1) {
      $sql .= " LIMIT $per_page";
      $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;
    }

    $result = $wpdb->get_results($sql, 'ARRAY_A');

    return $result;
  }

  public function get_group_info($groupid)
  {
    global $wpdb;
    $sql =
      "SELECT name FROM {$wpdb->prefix}notifications_groups where id = " .
      $groupid;
    $result = $wpdb->get_row($sql, 'ARRAY_A');

    return $result;
  }

  public function get_users_by_group($groupid)
  {
    global $wpdb;
    $sql =
      "SELECT * FROM {$wpdb->prefix}notifications_groups_users where group_id = " .
      $groupid;
    $result = $wpdb->get_results($sql, 'ARRAY_A');

    return $result;
  }

  public function get_group_user($user_id)
  {
    global $wpdb;
    $sql =
      "SELECT * FROM {$wpdb->prefix}notifications_groups_users where user_id = " .
      $user_id;
    $result = $wpdb->get_results($sql, 'ARRAY_A');

    return $result;
  }

  /**
   * Delete a group record.
   *
   * @param int $id group id
   */
  public static function delete_groups($id)
  {
    global $wpdb;

    $wpdb->delete("{$wpdb->prefix}notifications_groups", ['id' => $id], ['%d']);
  }

  /**
   * update a group record.
   *
   * @param int $id group id
   * @param string $name group name
   */
  public static function update_groupname($id, $name)
  {
    global $wpdb;

    $wpdb->update(
      "{$wpdb->prefix}notifications_groups",
      ['name' => $name],
      ['id' => $id]
    );
  }

  public static function addnew_group($name)
  {
    global $wpdb;

    $wpdb->insert("{$wpdb->prefix}notifications_groups", ['name' => $name]);
  }

  /**
   * Returns the count of records in the database.
   *
   * @return null|string
   */
  public static function record_count()
  {
    global $wpdb;

    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}notifications_groups";

    return $wpdb->get_var($sql);
  }

  /** Text displayed when no group data is available */
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
      case 'id':
        return $item[$column_name];
      case 'name':
        return $item[$column_name];
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
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

  function column_name($item)
  {
    $delete_nonce = wp_create_nonce('sp_groups');

    $title = '<strong>' . $item['name'] . '</strong>';

    $actions = [
      'edit' => sprintf(
        '<a href="#" data-href="?page=%s&action=%s&group=%s&_wpnonce=%s" class="changegroupname" data-groupid="' .
          $item['id'] .
          '" data-groupname="' .
          $item['name'] .
          '">Edit</a>',
        esc_attr($_REQUEST['page']),
        'edit',
        absint($item['id']),
        $delete_nonce
      ),
      'delete' => sprintf(
        '<a href="?page=%s&action=%s&group=%s&_wpnonce=%s">Delete</a>',
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
      //'id' => __('ID', 'sp'),
      'name' => __('Group', 'sp'),
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
      'id' => ['id', true],
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
    ];

    return $actions;
  }

  /**
   * Handles data query and filter, sorting, and pagination.
   */
  public function prepare_items()
  {
    //$this->_column_headers = $this->get_column_info();
    $this->_column_headers = [
      $this->get_columns(),
      [], // hidden columns
      $this->get_sortable_columns(),
      $this->get_primary_column_name(),
    ];

    /** Process bulk action */
    $this->process_bulk_action();

    $per_page = $this->get_items_per_page('groups_per_page', 20);
    $current_page = $this->get_pagenum();
    $total_items = self::record_count();

    $this->set_pagination_args([
      'total_items' => $total_items, //WE have to calculate the total number of items
      'per_page' => $per_page, //WE have to determine how many items to show on a page
    ]);

    $this->items = self::get_groups($per_page, $current_page);
  }

  public function process_bulk_action()
  {
    //Detect when a bulk action is being triggered...
    if (
      $this->current_action() == 'delete' ||
      $this->current_action() == 'updategroupname' ||
      $this->current_action() == 'addnewgroup'
    ) {
      $nonce = esc_attr($_REQUEST['_wpnonce']);
      if (!wp_verify_nonce($nonce, 'sp_groups')) {
        die('You dont have permisstion to do this action.');
        //wp_redirect(esc_url_raw(add_query_arg()));
      }
      switch ($this->current_action()) {
        case 'delete':
          $type = 'delete';
          self::delete_groups(absint($_GET['group']));
          break;
        case 'updategroupname':
          $type = 'update';
          self::update_groupname($_POST['id'], $_POST['name']);
          break;
        case 'addnewgroup':
          $type = 'addnew';
          self::addnew_group($_POST['name']);
          break;
        default:
          die('Wrong action.');
      }

      wp_redirect(
        '/wp-admin/admin.php?page=wp_notifications_groups&type=' . $type
      );
      exit();
    }

    // one action

    // bulk action
    if (isset($_POST['action']) || isset($_POST['action2'])) {
      $bulkAction =
        $_POST['action'] != -1 ? $_POST['action'] : $_POST['action2'];
      $ids = esc_sql($_POST['bulk-delete']);
      switch ($bulkAction) {
        case 'bulk-delete':
          $type = 'delete';
          // loop over the array of record ids and delete them
          foreach ($ids as $id) {
            self::delete_groups($id);
          }
          break;
      }

      wp_redirect(
        '/wp-admin/admin.php?page=wp_notifications_groups&type=' . $type
      );
      exit();
    }
  }
}
