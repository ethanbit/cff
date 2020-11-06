<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\Engine\Platform;
use Awf\Database\Installer;
use Awf\Mvc\Model;

/**
 * @package		akeebabackupwp
 * @copyright	2014-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU GPL version 3 or later
 */

class AkeebaBackupWP
{
	/** @var string The name of the wp-content/plugins directory we live in */
	public static $dirName = 'akeebabackupwp';

	/** @var string The name of the main plugin file */
	public static $fileName = 'akeebabackupwp.php';

	/** @var string Absolute filename to self */
	public static $absoluteFileName = null;

	/** @var array List of all JS files we can possibly load */
	public static $jsFiles = array();

	/** @var array List of all CSS files we can possibly load */
	public static $cssFiles = array();

	/** @var bool Do we have an outdated PHP version? */
	public static $wrongPHP = false;

	/** @var string Minimum PHP version */
	public static $minimumPHP = '5.6.0';

	protected static $loadedScripts = array();

	/**
	 * @var array Application configuration, read from helpers/private/config.php
	 */
	private static $appConfig = null;

	/**
	 * Initialization, runs once when the plugin is loaded by WordPress
	 *
	 * @param  string  $pluginFile  The absolute path of the plugin file being loaded
	 *
	 * @return void
	 */
	public static function preboot_initialization($pluginFile)
	{
		if (defined('AKEEBA_SOLOWP_PATH'))
		{
			return;
		}

		$baseUrlParts = explode('/', plugins_url('', $pluginFile));

		AkeebaBackupWP::$dirName = end($baseUrlParts);
		AkeebaBackupWP::$fileName = basename($pluginFile);
		AkeebaBackupWP::$absoluteFileName = $pluginFile;
		AkeebaBackupWP::$wrongPHP = version_compare(PHP_VERSION, AkeebaBackupWP::$minimumPHP, 'lt');

		$aksolowpPath = plugin_dir_path($pluginFile);
		define('AKEEBA_SOLOWP_PATH', $aksolowpPath);
	}

	/**
	 * Store the unquoted request variables to prevent WordPress from killing JSON requests.
	 */
	public static function fakeRequest()
	{
		// See http://stackoverflow.com/questions/8949768/with-magic-quotes-disabled-why-does-php-wordpress-continue-to-auto-escape-my
		global $_REAL_REQUEST;
		$_REAL_REQUEST = $_REQUEST;
	}

	/**
	 * Start a session (if not already started). It also takes care of our magic trick for displaying raw views without
	 * rendering WordPress' admin interface.
	 */
	public static function startSession()
	{
		// We no longer start a session; we are now using a session handler that uses nothing but the database
		/**
		if (!session_id())
		{
			session_start();
		}
		/**/

		$page = self::$dirName . '/' . self::$fileName;

		// Is this an Akeeba Solo page?
		if (isset($_REQUEST['page']) && ($_REQUEST['page'] == $page))
		{
			// Is it a format=raw, format=json or tmpl=component page?
			if (
				(isset($_REQUEST['format']) && ($_REQUEST['format'] == 'raw')) ||
				(isset($_REQUEST['format']) && ($_REQUEST['format'] == 'json')) ||
				(isset($_REQUEST['tmpl']) && ($_REQUEST['tmpl'] == 'component'))
			)
			{
				define('AKEEBA_SOLOWP_OBFLAG', 1);
				@ob_start();
			}
		}
	}

	/**
	 * Load template scripts with fallback to our own copies (useful for support)
	 */
	public static function loadJavascript()
	{
		// We no longer start a session; we are now using a session handler that uses nothing but the database
		/**
		if (!session_id())
		{
		session_start();
		}
		/**/

		$page = self::$dirName . '/' . self::$fileName;

		// Is this an Akeeba Solo page?
		if (!isset($_REQUEST['page']) || !($_REQUEST['page'] == $page))
		{
			return;
		}

		/**
		 * We preload all of our CSS files.
		 *
		 * We have to do that to prevent an ugly flash of the page since, by default, WordPress adds the CSS to the
		 * footer (right above the closing body tag). This would cause the browser to re-evaluate the stylesheet,
		 * causing the flash.
		 */
		$theEntireUniverseOfStyles = array(
			// FEF
			'fef-wp',
			// Custom CSS
			'theme',
		);

		$relPath = __DIR__ . '/../';

		self::loadAppConfig();

		if (isset(self::$appConfig['darkmode']) && (self::$appConfig['darkmode'] == 1))
		{
			$theEntireUniverseOfStyles[] = 'dark';
		}

		foreach ($theEntireUniverseOfStyles as $style)
		{
			$scriptPath = 'app/media/css/' . $style . '.min.css';

			if (file_exists($relPath . $scriptPath))
			{
				AkeebaBackupWP::enqueueStyle(plugins_url($scriptPath, self::$absoluteFileName));
			}
		}

	}

	/**
	 * Terminate a session if it's already started
	 */
	public static function endSession()
	{
		if (session_id())
		{
			session_destroy();
		}
	}

	/**
	 * Part of our magic trick for displaying raw views without rendering WordPress' admin interface.
	 */
	public static function clearBuffer()
	{
		if (defined('AKEEBA_SOLOWP_OBFLAG'))
		{
			@ob_end_clean();
			exit(0);
		}
	}

	/**
	 * Installation hook. Creates the database tables if they do not exist and performs any post-installation work
	 * required.
	 */
	public static function install()
	{
		// Require WordPress 3.1 or later
		if (version_compare(get_bloginfo('version'), '3.1', 'lt'))
		{
			deactivate_plugins(self::$fileName);
		}

		$container = self::loadAkeebaBackup();

		if ($container)
		{
			/** @var \Solo\Model\Main $cpanelModel */
			$cpanelModel = Model::getInstance($container->application_name, 'Main', $container);

			try
			{
				$cpanelModel->checkAndFixDatabase();
			}
			catch (\RuntimeException $e)
			{
				// The update is stuck. We will display a warning in the Control Panel
			}

			// Run the update scripts, if necessary
			$cpanelModel->postUpgradeActions();

			update_option('akeebabackupwp_plugin_dir', self::$dirName);

			// Copy the mu-plugins in the correct folder
			$mu_folder = ABSPATH.'wp-content/mu-plugins';

			if (defined('WPMU_PLUGIN_DIR') && WPMU_PLUGIN_DIR)
			{
				$mu_folder = WPMU_PLUGIN_DIR;
			}

			if (!is_dir($mu_folder))
			{
				mkdir($mu_folder, 0755, true);
			}

			@copy(WP_PLUGIN_DIR.'/'.self::$dirName.'/helpers/assets/mu-plugins/akeeba-backup-coreupdate.php',
				$mu_folder.'/akeeba-backup-coreupdate.php');
		}

		// Register the uninstallation hook
		register_uninstall_hook(self::$absoluteFileName, array('AkeebaBackupWP', 'uninstall'));
	}

	/**
	 * Uninstallation hook
	 *
	 * Removes database tables if they exist and performs any post-uninstallation work required.
	 *
	 * @return  void
	 */
	public static function uninstall()
	{
		$container = self::loadAkeebaBackup();

		if ($container)
		{
			$dbInstaller = new Installer($container);
			$dbInstaller->removeSchema();
		}
	}

	/**
	 * Create the administrator menu for Akeeba Backup
	 */
	public static function adminMenu()
	{
		if (is_multisite())
		{
			return;
		}

		$page_hook_suffix = add_menu_page('Akeeba Backup', 'Akeeba Backup', 'edit_others_posts', self::$absoluteFileName, array('AkeebaBackupWP', 'boot'), plugins_url('app/media/logo/abwp-24-white.png', self::$absoluteFileName));

		//add_action('admin_print_scripts-' . $page_hook_suffix, array(__CLASS__, 'adminPrintScripts'));
	}

	/**
	 * Create the blog network administrator menu for Akeeba Backup
	 */
	public static function networkAdminMenu()
	{
		if (!is_multisite())
		{
			return;
		}

		add_menu_page('Akeeba Backup', 'Akeeba Backup', 'manage_options', self::$absoluteFileName, array('AkeebaBackupWP', 'boot'), plugins_url('app/media/logo/abwp-24-white.png', self::$absoluteFileName));
	}

	/**
	 * Boots the Akeeba Backup application
	 *
	 * @param   string  $bootstrapFile  The name of the application bootstrap file to use.
	 */
	public static function boot($bootstrapFile = 'boot_webapp.php')
	{
		if (empty($bootstrapFile))
		{
			$bootstrapFile = 'boot_webapp.php';
		}

		if (!defined('AKEEBA_COMMON_WRONGPHP'))
		{
			define('AKEEBA_COMMON_WRONGPHP', 1);
		}

		$minPHPVersion         = '5.6.0';
		$recommendedPHPVersion = '7.3';
		$softwareName          = 'Akeeba Backup for WordPress';

		if (!require_once(dirname(self::$absoluteFileName) . '/helpers/wrongphp.php'))
		{
			return;
		}

		// HHVM made sense in 2013, now PHP 7 is a way better solution than an hybrid PHP interpreter
		if (defined('HHVM_VERSION'))
		{
			include_once dirname(self::$absoluteFileName) . '/helpers/hhvm.php';

			return;
		}

		$network = is_multisite() ? 'network/' : '';

		if (!defined('AKEEBA_SOLO_WP_ROOTURL'))
		{
			define('AKEEBA_SOLO_WP_ROOTURL', site_url());
		}

		if (!defined('AKEEBA_SOLO_WP_URL'))
		{
			$bootstrapUrl = admin_url() . $network . 'admin.php?page=' . self::$dirName . '/' . self::$fileName;
			define('AKEEBA_SOLO_WP_URL', $bootstrapUrl);
		}

		if (!defined('AKEEBA_SOLO_WP_SITEURL'))
		{
			$baseUrl = plugins_url('app/index.php', self::$absoluteFileName);
			define('AKEEBA_SOLO_WP_SITEURL', substr($baseUrl, 0, -10));
		}

		$strapFile = dirname(self::$absoluteFileName) . '/helpers/' . $bootstrapFile;

		if (!file_exists($strapFile))
		{
			die("Oops! Cannot initialize Akeeba Backup. Cannot locate the file $strapFile");
		}

		include_once $strapFile;
	}

	/**
	 * Enqueues a Javascript file for loading
	 *
	 * @param   string  $url  The URL of the Javascript file to load
	 */
	public static function enqueueScript($url)
	{
		$parts = explode('?', $url);
		$url = $parts[0];

		if (in_array($url, self::$loadedScripts))
		{
			return;
		}

		self::$loadedScripts[] = $url;

		if (!defined('AKEEBABACKUP_VERSION'))
		{
			@include_once dirname(self::$absoluteFileName) . '/app/version.php';
		}

		$handle = 'akjs' . md5($url);

		wp_enqueue_script($handle, $url, [], AKEEBABACKUP_VERSION, false);
	}

	/**
	 * Enqueues an inline Javascript script
	 *
	 * @param   string  $content  The script contents
	 */
	public static function enqueueInlineScript($content)
	{
		/**
		 * WordPress only adds inline scripts as "extra data" of an already queued script file. Since we want to add our
		 * inline scripts **after** our script files we find the handle of the last script file we queued and add the
		 * inline script to it.
		 *
		 * This means that this method will only really work correctly if it's called AFTER the last self::enqueueScript
		 * call.
		 */
		$url = end(self::$loadedScripts);

		if (!defined('AKEEBABACKUP_VERSION'))
		{
			@include_once dirname(self::$absoluteFileName) . '/app/version.php';
		}

		$handle = 'akjs' . md5($url);

		wp_add_inline_script($handle, $content);
	}

	/**
	 * Enqueues a CSS file for loading
	 *
	 * @param   string  $url  The URL of the CSS file to load
	 */
	public static function enqueueStyle($url)
	{
		if (!defined('AKEEBABACKUP_VERSION'))
		{
			@include_once dirname(self::$absoluteFileName) . '/app/version.php';
		}

		$handle = 'akcss' . md5($url);
		wp_enqueue_style($handle, $url, array(), AKEEBABACKUP_VERSION);
	}

	/**
	 * Runs when the authentication cookie is being cleared (user logs out)
	 *
	 * @return  void
	 */
	public static function onUserLogout()
	{
		// Remove the user meta which are used in our fake session handler
		$userId         = get_current_user_id();
		$allMeta        = get_user_meta($userId);

		if (empty($allMeta))
		{
			return;
		}

		foreach ($allMeta as $key => $value)
		{
			if (strpos($key, 'AkeebaSession_') === 0)
			{
				delete_user_meta($userId, $key);
			}
		}
	}

	/**
	 * Load the WordPress plugin updater integration, unless the integratedupdate flag in the configuration is unset.
	 * The default behavior is to add the integration.
	 *
	 * @return void
	 */
	public static function loadIntegratedUpdater()
	{
		if (is_null(self::$appConfig))
		{
			self::loadAppConfig();
		}

		if (isset(self::$appConfig['options']['integratedupdate']) && (self::$appConfig['options']['integratedupdate'] == 0))
		{
			return;
		}

		add_filter ('pre_set_site_transient_update_plugins', array('AkeebaBackupWPUpdater', 'getupdates'), 10, 2);
		add_filter ('plugins_api', array('AkeebaBackupWPUpdater', 'checkinfo'), 10, 3);
		add_filter ('upgrader_pre_download', array('AkeebaBackupWPUpdater', 'addDownloadID'), 10, 3);
		add_filter ('upgrader_package_options', array('AkeebaBackupWPUpdater', 'packageOptions'), 10, 2);
		add_filter ('after_plugin_row_akeebabackupwp/akeebabackupwp.php', array('AkeebaBackupWPUpdater', 'updateMessage'), 10, 3);
	}

	/**
	 * Returns the backup profile that should be used on Manual WordPress update. Returns false if we don't want to take a backup
	 *
	 * @return bool|int
	 */
	public static function getProfileManualCoreUpdate()
	{
		if (is_null(self::$appConfig))
		{
			self::loadAppConfig();
		}

		// If the option has been set and it's false, let's stop. Otherwise continue (enabled by default)
		if (isset(self::$appConfig['options']['backuponupdate_core_manual']) && !self::$appConfig['options']['backuponupdate_core_manual'])
		{
			return false;
		}

		// Default backup profile is 1
		$profile = 1;

		if (isset(self::$appConfig['options']['backuponupdate_core_manual_profile']) && (self::$appConfig['options']['backuponupdate_core_manual_profile'] > 0))
		{
			$profile = self::$appConfig['options']['backuponupdate_core_manual_profile'];
		}

		return $profile;
	}

	private static function loadAppConfig()
	{
		self::$appConfig = [];
		$filePath        = __DIR__ . '/private/config.php';

		if (!file_exists($filePath) || !is_readable($filePath))
		{
			return;
		}

		$fileData = @file_get_contents($filePath);

		if ($fileData === false)
		{
			return;
		}

		$fileData = explode("\n", $fileData, 2);
		$fileData = $fileData[1];
		$config   = @json_decode($fileData, true);

		if (is_null($config) || !is_array($config))
		{
			return;
		}

		self::$appConfig = $config;
	}

	/**
	 * Includes all the required pieces to load Akeeba Backup from within a standard WordPress page
	 *
	 * @return \Solo\Container|false
	 */
	public static function loadAkeebaBackup()
	{
		static $localContainer;

		// Do not run the whole logic if we already have a valid Container
		if ($localContainer)
		{
			return $localContainer;
		}

		if (!defined('AKEEBASOLO'))
		{
			define('AKEEBASOLO', 1);
		}

		@include_once __DIR__.'/../app/version.php';

		// Include the autoloader
		if (!include_once __DIR__ . '/../app/Awf/Autoloader/Autoloader.php')
		{
			return false;
		}

		global $akeebaBackupWordPressLoadPlatform;
		$akeebaBackupWordPressLoadPlatform = true;

		if (!file_exists(__DIR__ . '/../helpers/integration.php'))
		{
			return false;
		}

		/** @var \Solo\Container $container */
		$container = require __DIR__ . '/../helpers/integration.php';

		// Ok, really don't know why but this function gets called TWICE. It seems to completely ignore the first result
		// (even if we report that there's an update) and calls it again. This means that the require_once above will be ignored.
		// I can't simply return the current $transient because it doesn't contain the updated info.
		// So I'll save a previous copy of the container and then use it later.
		if (!$localContainer)
		{
			$localContainer = $container;
		}

		if (!$localContainer)
		{
			return false;
		}

		// Get all info saved inside the configuration
		$container->appConfig->loadConfiguration();
		$container->basePath = WP_PLUGIN_DIR.'/akeebabackupwp/app/Solo';

		// Require the application for the first time by passing all values. In this way we prime the internal cache and
		// we will be covered on cases where we fetch the application from the getInstance method instead of using the container
		\Awf\Application\Application::getInstance('Solo', $container);

		return $localContainer;
	}

	/**
	 * Issues a redirection to the 'installation' folder if such a folder is present and seems to contain a copy of
	 * ANGIE. This prevents some webmasters used to the Stone Ages from unzipping a backup archive and not running the
	 * installer, then complain very loudly that Akeeba Backup doesn't work when the only thing doesn't working is their
	 * common sense.
	 *
	 * In simple terms, this static method fixes stupid.
	 */
	public static function redirectIfInstallationPresent()
	{
		$installDir   = rtrim(ABSPATH, '/\\') . '/installation';
		$installIndex = rtrim(ABSPATH, '/\\') . '/installation/index.php';

		if (!@is_dir($installDir) && !is_file($installIndex))
		{
			return;
		}

		$indexContents = @file_get_contents($installIndex);

		if ($indexContents === false)
		{
			return;
		}

		if (!preg_match('#\s*\*\s*ANGIE\s#', $indexContents) || (strpos($indexContents, '_AKEEBA') === false))
		{
			return;
		}

		ob_end_clean();
		ob_start();

		try
		{
			// Required by the integration.php file
			define('AKEEBASOLO', 1);
			// Creates the application container, required for translations to work
			global $akeebaBackupWordPressLoadPlatform;
			$akeebaBackupWordPressLoadPlatform = true;
			/** @var \Awf\Container\Container $container */
			$container = require 'integration.php';
			// This tells AWF to consider the 'solo' app as the default
			$app = Awf\Application\Application::getInstance($container->application->getName());
			// Tell the app to load the translation strings
			$app->initialise();
			// Load the message page
			require __DIR__ . '/installation_detected.php';
			// Show the message page
			ob_end_flush();
		}
		catch (Exception $e)
		{
			// If something broke we show a low-tech, abbreviated page
			ob_end_clean();

			echo <<< HTML
<html>
<head><title>You have not completed the restoration of this site backup</title></head>
<body>
<h1>You have not completed the restoration of this site backup</h1>
<p>
	Please <a href="installation/index.php">click here</a> to run the restoration script. Do remember to delete the
	<code>installation</code> directory after you are done restoring your site to prevent this page from appearing
	again. 
</p>
</body>
</html>
HTML;
			die;
		}

		exit(200);
	}
}
