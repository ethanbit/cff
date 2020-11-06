<?php
if (!class_exists('WP_List_Table')) {
  require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
$base_dir = plugin_dir_path(__DIR__);
require_once $base_dir . 'inc/class_wp_notifications_groups.php';

class WpNotifications_History extends WP_List_Table
{
  /** Class constructor */
  public function __construct()
  {
    parent::__construct([
      'singular' => __('History', 'sp'), //singular name of the listed records
      'plural' => __('History', 'sp'), //plural name of the listed records
      'ajax' => false, //does this table support ajax?
    ]);
  }

  /**
   * Retrieve history data from the database
   *
   * @param int $per_page
   * @param int $page_number
   *
   * @return mixed
   */
  public static function get_history($per_page = 5, $page_number = 1)
  {
    global $wpdb;

    $sql = "SELECT * FROM {$wpdb->prefix}notifications_history";

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

  /**
   * Delete a group record.
   *
   * @param int $id group id
   */
  public static function delete_history($id)
  {
    global $wpdb;

    $wpdb->delete(
      "{$wpdb->prefix}notifications_history",
      ['id' => $id],
      ['%d']
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

    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}notifications_history";

    return $wpdb->get_var($sql);
  }

  /** Text displayed when no group data is available */
  public function no_items()
  {
    _e('No History.', 'sp');
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
      case 'type':
        return $item[$column_name];
      case 'info':
        return $item[$column_name];
      case 'total':
        return $item[$column_name];
      case 'success':
        return $item[$column_name];
      case 'created_at':
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

  function column_total($item)
  {
    return $item['count'];
  }

  function column_info($item)
  {
    $delete_nonce = wp_create_nonce('sp_history');
    //Array ( [id] => 10 [type] => user [user_id] => 1335 [group_id] => 0 [count] => 1 [success] => 0 [created_at] => 2020-10-15 10:11:03 )
    if ($item['type'] == 'user') {
      $user = get_user_by('id', $item['user_id']);
      $info['name'] = $user->display_name;
    } else {
      if ($item['group_id']) {
        $group = new WpNotifications_Groups();
        $info = $group->get_group_info($item['group_id']);
      } else {
        $info['name'] = 'No History';
      }
    }
    $title = '<strong>' . $info['name'] . '</strong>';

    $actions = [
      'delete' => sprintf(
        '<a href="?page=%s&action=%s&history=%s&_wpnonce=%s">Delete</a>',
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
      'info' => 'Info',
      'type' => 'Type',
      'success' => 'Success',
      'total' => 'Total',
      'created_at' => 'Date',
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

    $per_page = $this->get_items_per_page('history_per_page', 20);
    $current_page = $this->get_pagenum();
    $total_items = self::record_count();

    $this->set_pagination_args([
      'total_items' => $total_items, //WE have to calculate the total number of items
      'per_page' => $per_page, //WE have to determine how many items to show on a page
    ]);

    $this->items = self::get_history($per_page, $current_page);
  }

  public function process_bulk_action()
  {
    //Detect when a bulk action is being triggered...
    if ($this->current_action() == 'delete') {
      $nonce = esc_attr($_REQUEST['_wpnonce']);
      if (!wp_verify_nonce($nonce, 'sp_history')) {
        die('You dont have permisstion to do this action.');
        //wp_redirect(esc_url_raw(add_query_arg()));
      }
      switch ($this->current_action()) {
        case 'delete':
          $type = 'delete';
          self::delete_history(absint($_GET['history']));
          break;
        default:
          die('Wrong action.');
      }

      wp_redirect(
        '/wp-admin/admin.php?page=wp_notifications_history&type=' . $type
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
            self::delete_history($id);
          }
          break;
      }

      wp_redirect(
        '/wp-admin/admin.php?page=wp_notifications_history&type=' . $type
      );
      exit();
    }
  }
}
