<?php
/*
Plugin Name: Akeeba Backup for WordPress
Plugin URI: https://www.akeebabackup.com
Description: The complete backup solution for WordPress
Version: 7.1.2
Author: Akeeba Ltd
Author URI: https://www.akeebabackup.com
Network: true
License: GPLv3
*/

/**
 * Make sure we are being called from WordPress itself
 */
defined('WPINC') or die;

/**
 * This should never happen unless your site is broken! It'd mean that you're double loading our plugin which is not how
 * WordPress works. We still defend against this because we've learned to expect the unexpected ;)
 */
if (defined('AKEEBA_SOLOWP_PATH'))
{
	return;
}

// Preload our helper classes
require_once dirname(__FILE__) . '/helpers/AkeebaBackupWP.php';
require_once dirname(__FILE__) . '/helpers/AkeebaBackupWPUpdater.php';

// Initialization of our helper class
AkeebaBackupWP::preboot_initialization(__FILE__);

/**
 * Redirect to the ANGIE installer if the installer currently exists
 */
AkeebaBackupWP::redirectIfInstallationPresent();

/**
 * Register public plugin hooks
 */
register_activation_hook(__FILE__, array('AkeebaBackupWP', 'install'));

/**
 * Register the plugin updater hooks (if necessary)
 */
AkeebaBackupWP::loadIntegratedUpdater();

/**
 * Register administrator plugin hooks
 */
if (is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX))
{
	add_action('admin_menu', array('AkeebaBackupWP', 'adminMenu'));
	add_action('network_admin_menu', array('AkeebaBackupWP', 'networkAdminMenu'));

	if (!AkeebaBackupWP::$wrongPHP)
	{
		add_action('init', array('AkeebaBackupWP', 'startSession'), 1);
		add_action('init', array('AkeebaBackupWP', 'loadJavascript'), 1);
		add_action('plugins_loaded', array('AkeebaBackupWP', 'fakeRequest'), 1);
		add_action('wp_logout', array('AkeebaBackupWP', 'endSession'));
		add_action('wp_login', array('AkeebaBackupWP', 'endSession'));
		add_action('in_admin_footer', array('AkeebaBackupWP', 'clearBuffer'));
		add_action('clear_auth_cookie', array('AkeebaBackupWP', 'onUserLogout'), 1);
	}
}

// Register WP-CLI commands
if (defined('WP_CLI') && WP_CLI)
{
	if (file_exists(__DIR__ . '/wpcli/register_commands.php'))
	{
		require_once __DIR__ . '/wpcli/register_commands.php';
	}
}
