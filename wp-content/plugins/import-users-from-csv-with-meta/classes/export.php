<?php
if ( ! defined( 'ABSPATH' ) ) exit; 

class ACUI_Exporter{
	private $path_csv;
	private $user_data;

	function __construct(){
		$upload_dir = wp_upload_dir();

		$this->path_csv = $upload_dir['basedir'] . "/export-users.csv";
		$this->user_data = array( "user_login", "user_email", "source_user_id", "user_pass", "user_nicename", "user_url", "user_registered", "display_name" );
		$this->woocommerce_default_user_meta_keys = array( 'billing_first_name', 'billing_last_name', 'billing_email', 'billing_phone', 'billing_country', 'billing_address_1', 'billing_city', 'billing_state', 'billing_postcode', 'shipping_first_name', 'shipping_last_name', 'shipping_country', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_postcode' );
		$this->other_non_date_keys = array( 'shipping_phone' );

		add_action( 'wp_ajax_acui_export_users_csv', array( $this, 'export_users_csv' ) );
		add_filter( 'acui_export_get_key_user_data', array( $this, 'filter_key_user_id' ) );
		add_filter( 'acui_export_non_date_keys', array( $this, 'get_non_date_keys' ), 1, 1 );
	}

	public static function admin_gui(){
		$roles = acui_get_editable_roles();
	?>
	<h3 id="acui_export_users_header"><?php _e( 'Export users', 'import-users-from-csv-with-meta' ); ?></h3>
	<form id="acui_export_users_wrapper" method="POST" target="_blank" enctype="multipart/form-data" action="<?php echo admin_url( 'admin-ajax.php' ); ?>">
		<table class="form-table">
			<tbody>
				<tr id="acui_role_wrapper" valign="top">
					<th scope="row"><?php _e( 'Role', 'import-users-from-csv-with-meta' ); ?></th>
					<td>
						<select name="role">
							<option value=''><?php _e( 'All roles', 'import-users-from-csv-with-meta' ); ?></option>
						<?php foreach ( $roles as $key => $value ): ?>
							<option value='<?php echo $key; ?>'><?php echo $value; ?></option>
						<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr id="acui_user_created_wrapper" valign="top">
					<th scope="row"><?php _e( 'User created', 'import-users-from-csv-with-meta' ); ?></th>
					<td>
						<label for="from">from <input name="from" type="date" value=""/></label>
						<label for="to">to <input name="to" type="date" value=""/></label>
					</td>
				</tr>
				<tr id="acui_delimiter_wrapper" valign="top">
					<th scope="row"><?php _e( 'Delimiter', 'import-users-from-csv-with-meta' ); ?></th>
					<td>
						<select name="delimiter">
							<option value='COMMA'><?php _e( 'Comma', 'import-users-from-csv-with-meta' ); ?></option>
							<option value='COLON'><?php _e( 'Colon', 'import-users-from-csv-with-meta' ); ?></option>
							<option value='SEMICOLON'><?php _e( 'Semicolon', 'import-users-from-csv-with-meta' ); ?></option>
							<option value='TAB'><?php _e( 'Tab', 'import-users-from-csv-with-meta' ); ?></option>
						</select>
					</td>
				</tr>
				<tr id="acui_timestamp_wrapper" valign="top">
					<th scope="row"><?php _e( 'Convert timestamp data to date format', 'import-users-from-csv-with-meta' ); ?></th>
					<td>
						<input type="checkbox" name="convert_timestamp" value="1" checked="checked">
						<span class="description"><?php _e( 'If you have problems and you get some value exported as a date that should not be converted to date, please deactivate this option. If this option is not activated, datetime format will be ignored.', 'import-users-from-csv-with-meta' ); ?></span>
					</td>
				</tr>
				<tr id="acui_datetime_format_wrapper" valign="top">
					<th scope="row"><?php _e( 'Datetime format', 'import-users-from-csv-with-meta' ); ?></th>
					<td>
						<input name="datetime_format" type="text" value="Y-m-d H:i:s"/>
						<span class="description"><a href="https://www.php.net/manual/en/datetime.formats.php"><?php _e( 'accepted formats', 'import-users-from-csv-with-meta' ); ?></a></span>
					</td>
				</tr>
				<tr id="acui_download_csv_wrapper" valign="top">
					<th scope="row"><?php _e( 'Download CSV file with users', 'import-users-from-csv-with-meta' ); ?></th>
					<td>
						<input class="button-primary" type="submit" value="<?php _e( 'Download', 'import-users-from-csv-with-meta'); ?>"/>
					</td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="action" value="acui_export_users_csv"/>
		<?php wp_nonce_field( 'codection-security', 'security' ); ?>				
	</form>

	<script type="text/javascript">
	jQuery( document ).ready( function( $ ){
		$( "input[name='from']" ).change( function() {
			$( "input[name='to']" ).attr( 'min', $( this ).val() );
		})	
	} )
	</script>
	<?php
	}

	static function is_valid_timestamp( $timestamp ){
		return ( (string) (int) $timestamp === $timestamp ) && ( $timestamp <= PHP_INT_MAX ) && ( $timestamp >= ~PHP_INT_MAX );
	}

	function get_non_date_keys( $non_date_keys ){
		return array_merge( $non_date_keys, $this->user_data, $this->woocommerce_default_user_meta_keys, $this->other_non_date_keys );
	}

	public static function prepare( $key, $value, $datetime_format ){
		$timestamp_keys = apply_filters( 'acui_export_timestamp_keys', array( 'wc_last_active' ) );
		$non_date_keys = apply_filters( 'acui_export_non_date_keys', array() );

		if( is_array( $value ) ){
			return serialize( $value );
		}
		elseif( in_array( $key, $non_date_keys ) || empty( $datetime_format ) ){
			return $value;
		}
		elseif( strtotime( $value ) ){ // dates in datetime format
			return date( $datetime_format, strtotime( $value ) );
		}
		elseif( ( self::is_valid_timestamp( $value ) && strlen( $value ) > 4 ) || in_array( $key, $timestamp_keys) ){ // dates in timestamp format
			return date( $datetime_format, $value );
		}
		else{
			return $value;
		}
	}

	function get_role( $user_id ){
		$user = get_user_by( 'id', $user_id );
		return implode( ',', $user->roles );
	}

	function export_users_csv(){
		check_ajax_referer( 'codection-security', 'security' );

		if( !current_user_can( apply_filters( 'acui_capability', 'create_users' ) ) )
			wp_die( __( 'Only users who are able to create users can export them.', 'import-users-from-csv-with-meta' ) );

		$role = sanitize_text_field( $_POST['role'] );
		$from = sanitize_text_field( $_POST['from'] );
		$to = sanitize_text_field( $_POST['to'] );
		$delimiter = sanitize_text_field( $_POST['delimiter'] );
		$convert_timestamp = isset( $_POST['convert_timestamp'] ) && !empty( $_POST['convert_timestamp'] );
		$datetime_format = ( $convert_timestamp ) ? sanitize_text_field( $_POST['datetime_format'] ) : '';

		switch ( $delimiter ) {
			case 'COMMA':
				$delimiter = ",";
				break;
			
			case 'COLON':
				$delimiter = ":";
				break;

			case 'SEMICOLON':
				$delimiter = ";";
				break;

			case 'TAB':
				$delimiter = "\t";
				break;
		}

		$data = array();
		$row = array();
		
		// header
		foreach ( $this->user_data as $key ) {
			$row[] = $key;
		}

		$row[] = "role";

		foreach ( $this->get_user_meta_keys() as $key ) {
			$row[] = $key;
		}

		$row = apply_filters( 'acui_export_columns', $row );

		$data[] = $row;
		$row = array();

		// data
		$users = $this->get_user_id_list( $role, $from, $to );
		foreach ( $users as $user ) {
			$userdata = get_userdata( $user );

			foreach ( $this->user_data as $key ) {
				$key = apply_filters( 'acui_export_get_key_user_data', $key );
				$row[] = self::prepare( $key, $userdata->data->{$key}, $datetime_format );
			}

			$row[] = $this->get_role( $user );

			foreach ( $this->get_user_meta_keys() as $key ) {
				$row[] = self::prepare( $key, get_user_meta( $user, $key, true ), $datetime_format );
			}

			$row = apply_filters( 'acui_export_data', $row, $user, $datetime_format );

			$data[] = $row;
			$row = array();
		}

		// export to csv
		$file = fopen( $this->path_csv, "w" );

		foreach ( $data as $line ) {
			fputcsv( $file, $line, $delimiter );
		}

		fclose( $file );

		$fsize = filesize( $this->path_csv ) + 3;
		$path_parts = pathinfo( $this->path_csv );
		header( "Content-type: text/csv;charset=utf-8" );
		header( "Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\"" );
		header( "Content-length: $fsize" );
		header( "Cache-control: private" );
		header( "Content-Description: File Transfer" );
    	header( "Content-Transfer-Encoding: binary" );
    	header( "Expires: 0" );
    	header( "Cache-Control: must-revalidate" );
    	header( "Pragma: public" );
    	
    	ob_clean();
    	flush();

    	echo "\xEF\xBB\xBF";
    	readfile( $this->path_csv );

		unlink( $this->path_csv );

		wp_die();
	}

	function get_user_meta_keys() {
	    global $wpdb;
	    $meta_keys = array();

	    $select = "SELECT distinct $wpdb->usermeta.meta_key FROM $wpdb->usermeta";
	    $usermeta = $wpdb->get_results( $select, ARRAY_A );
	  
	  	foreach ($usermeta as $key => $value) {
	  		$meta_keys[] = $value["meta_key"];
	  	}
	    return apply_filters( 'acui_export_get_user_meta_keys', $meta_keys );
	}

	function get_user_id_list( $role, $from, $to ){
		$args = array( 'fields' => array( 'ID' ) );

		if( !empty( $role ) )
			$args['role'] = $role;

		$date_query = array();

		if( !empty( $from ) )
			$date_query[] = array( 'after' => $from );
		
		if( !empty( $to ) )
			$date_query[] = array( 'before' => $to );

		if( !empty( $date_query ) ){
			$date_query['inclusive'] = true;
			$args['date_query'] = $date_query;
		}

		$users = get_users( $args );
		$list = array();

	    foreach ( $users as $user ) {
	    	$list[] = $user->ID;
	    }

	    return $list;
	}

	function filter_key_user_id( $key ){
		return ( $key == 'source_user_id' ) ? 'ID' : $key;
	}
}

$acui_exporter = new ACUI_Exporter();