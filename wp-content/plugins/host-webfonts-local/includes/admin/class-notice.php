<?php
/* * * * * * * * * * * * * * * * * * * * *
 *
 *  ██████╗ ███╗   ███╗ ██████╗ ███████╗
 * ██╔═══██╗████╗ ████║██╔════╝ ██╔════╝
 * ██║   ██║██╔████╔██║██║  ███╗█████╗
 * ██║   ██║██║╚██╔╝██║██║   ██║██╔══╝
 * ╚██████╔╝██║ ╚═╝ ██║╚██████╔╝██║
 *  ╚═════╝ ╚═╝     ╚═╝ ╚═════╝ ╚═╝
 *
 * @package  : OMGF
 * @author   : Daan van den Bergh
 * @copyright: (c) 2020 Daan van den Bergh
 * @url      : https://daan.dev
 * * * * * * * * * * * * * * * * * * * */

defined( 'ABSPATH' ) || exit;

class OMGF_Admin_Notice
{
	const OMGF_ADMIN_NOTICE_TRANSIENT  = 'omgf_admin_notice';
	const OMGF_ADMIN_NOTICE_EXPIRATION = 60;
	
	/** @var array $notices */
	public static $notices = [];
	
	/** @var string $plugin_text_domain */
	private static $plugin_text_domain = 'host-webfonts-local';
	
	/**
	 * @param        $message
	 * @param string $type (info|warning|error|success)
	 * @param string $screen_id
	 * @param bool   $json
	 * @param int    $code
	 */
	public static function set_notice ( $message, $message_id = '', $die = true, $type = 'success', $code = 200, $screen_id = 'all' ) {
		self::$notices = get_transient( self::OMGF_ADMIN_NOTICE_TRANSIENT );
		
		if ( ! self::$notices ) {
			self::$notices = [];
		}
		
		self::$notices[ $screen_id ][ $type ][ $message_id ] = $message;
		
		set_transient( self::OMGF_ADMIN_NOTICE_TRANSIENT, self::$notices, self::OMGF_ADMIN_NOTICE_EXPIRATION );
		
		if ( $die ) {
			switch ( $type ) {
				case 'error':
					wp_send_json_error( $message, $code );
					break;
				default:
					wp_send_json_success( $message, $code );
			}
		}
	}
	
	/**
	 * @param string $message_id
	 * @param string $type
	 * @param string $screen_id
	 */
	public static function unset_notice ( $message_id = '', $type = 'info', $screen_id = 'all' ) {
		self::$notices = get_transient( self::OMGF_ADMIN_NOTICE_TRANSIENT );
		
		if ( isset( self::$notices [ $screen_id ][ $type ][ $message_id ] ) ) {
			unset( self::$notices [ $screen_id ][ $type ][ $message_id ] );
		}
		
		if ( is_array( self::$notices ) && empty( self::$notices [ $screen_id ] [ $type ] ) ) {
			unset ( self::$notices [ $screen_id ] [ $type ] );
		}
		
		set_transient( self::OMGF_ADMIN_NOTICE_TRANSIENT, self::$notices, self::OMGF_ADMIN_NOTICE_EXPIRATION );
	}
	
	/**
	 * Prints notice (if any) grouped by type.
	 */
	public static function print_notices () {
		$admin_notices = get_transient( self::OMGF_ADMIN_NOTICE_TRANSIENT );
		
		if ( is_array( $admin_notices ) ) {
			$current_screen = get_current_screen();
			
			foreach ( $admin_notices as $screen => $notice ) {
				if ( $current_screen->id != $screen && $screen != 'all' ) {
					continue;
				}
				
				foreach ( $notice as $type => $message ) {
					?>
                    <div id="message" class="notice notice-<?php echo $type; ?> is-dismissible">
						<?php foreach ( $message as $line ): ?>
                            <p><?= $line; ?></p>
						<?php endforeach; ?>
                    </div>
					<?php
				}
			}
		}
		
		delete_transient( self::OMGF_ADMIN_NOTICE_TRANSIENT );
	}
	
	/**
	 *
	 */
	public static function optimization_finished () {
		self::set_notice(
			__( 'OMGF has finished optimizing your Google Fonts. Enjoy! :-)', self::$plugin_text_domain ),
			'omgf-finished-optimizing',
			false
		);
		
		self::set_notice(
			'<em>' . __( 'If you\'re using any CSS minify/combine and/or Full Page Caching plugins, don\'t forget to flush their caches.', self::$plugin_text_domain ) . '</em>',
			'omgf-optimize-plugin-notice',
			false,
			'info'
		);
		
		self::set_notice(
			__( 'OMGF will keep running silently in the background and will generate additional stylesheets when other Google Fonts are found on any of your pages.', self::$plugin_text_domain ),
			'omgf-optimize-background',
			false,
			'info'
		);
	}
}
