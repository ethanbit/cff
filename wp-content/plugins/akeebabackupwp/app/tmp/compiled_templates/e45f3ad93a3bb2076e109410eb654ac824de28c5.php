<?php /* /home/citytestfine/public_html/wp-content/plugins/akeebabackupwp/app/Solo/ViewTemplates/Configuration/default.blade.php */ ?>
<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;

defined('_AKEEBA') or die();

/** @var $this \Solo\View\Configuration\Html */

$router = $this->getContainer()->router;

?>
<?php /* Configuration Wizard pop-up */ ?>
<?php if($this->promptForConfigurationWizard): ?>
	<?php echo $this->loadAnyTemplate('Configuration/confwiz_modal'); ?>
<?php endif; ?>

<?php /* Modal dialog prototypes */ ?>
<?php echo $this->loadAnyTemplate('CommonTemplates/FTPBrowser'); ?>
<?php echo $this->loadAnyTemplate('CommonTemplates/SFTPBrowser'); ?>
<?php echo $this->loadAnyTemplate('CommonTemplates/FTPConnectionTest'); ?>
<?php echo $this->loadAnyTemplate('CommonTemplates/ErrorModal'); ?>
<?php echo $this->loadAnyTemplate('CommonTemplates/FolderBrowser'); ?>

<?php if($this->securesettings == 1): ?>
    <div class="akeeba-block--success">
		<?php echo Text::_('COM_AKEEBA_CONFIG_UI_SETTINGS_SECURED'); ?>
    </div>
<?php elseif($this->securesettings == 0): ?>
    <div class="akeeba-block--failure">
		<?php echo Text::_('COM_AKEEBA_CONFIG_UI_SETTINGS_NOTSECURED'); ?>
    </div>
<?php endif; ?>

<?php echo $this->loadAnyTemplate('CommonTemplates/ProfileName'); ?>

<div class="akeeba-block--info">
	<?php echo \Awf\Text\Text::_('COM_AKEEBA_CONFIG_WHERE_ARE_THE_FILTERS'); ?>
</div>


<form name="adminForm" id="adminForm" method="post"
	  action="<?php echo $this->container->router->route('index.php?view=configuration'); ?>"
      class="akeeba-form--horizontal akeeba-form--with-hidden akeeba-form--configuration">

    <div class="akeeba-panel--info" style="margin-bottom: -1em">
        <header class="akeeba-block-header">
            <h5>
	            <?php echo \Awf\Text\Text::_('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION'); ?>
            </h5>
        </header>

		<div class="akeeba-form-group">
			<label for="profilename" rel="popover"
				   data-original-title="<?php echo \Awf\Text\Text::_('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION'); ?>"
				   data-content="<?php echo \Awf\Text\Text::_('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION_TOOLTIP'); ?>">
				<?php echo \Awf\Text\Text::_('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION'); ?>
			</label>
            <input type="text" name="profilename" id="profilename" value="<?php echo $this->profilename; ?>" />
		</div>

		<div class="akeeba-form-group">
			<label for="quickicon" rel="popover"
				   data-original-title="<?php echo \Awf\Text\Text::_('COM_AKEEBA_CONFIG_QUICKICON_LABEL'); ?>"
				   data-content="<?php echo \Awf\Text\Text::_('COM_AKEEBA_CONFIG_QUICKICON_DESC'); ?>">
				<?php echo \Awf\Text\Text::_('COM_AKEEBA_CONFIG_QUICKICON_LABEL'); ?>
			</label>
            <div>
                <input type="checkbox" name="quickicon"
                       id="quickicon" <?php echo $this->quickIcon ? 'checked="checked"' : ''; ?> />
            </div>
        </div>
	</div>

	<!-- This div contains dynamically generated user interface elements -->
	<div id="akeebagui">
	</div>

    <div class="akeeba-hidden-fields-container">
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="token" value="<?php echo $this->container->session->getCsrfToken()->getValue(); ?>"/>
    </div>

</form>
