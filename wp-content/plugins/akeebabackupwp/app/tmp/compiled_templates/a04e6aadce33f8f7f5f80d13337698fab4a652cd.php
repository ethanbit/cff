<?php /* /home/citytestfine/public_html/wp-content/plugins/akeebabackupwp/app/Solo/ViewTemplates/CommonTemplates/FolderBrowser.blade.php */ ?>
<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;

defined('_AKEEBA') or die();

?>
<?php /* Filesystem browser */ ?>
<div class="modal" id="folderBrowserDialog" tabindex="-1" role="dialog" aria-labelledby="folderBrowserDialogLabel"
     aria-hidden="true" style="display: none;">
    <div class="akeeba-renderer-fef <?php echo ($this->getContainer()->appConfig->get('darkmode', -1) == 1) ? 'akeeba-renderer-fef--dark' : '' ?>">
        <h4 id="folderBrowserDialogLabel">
	        <?php echo \Awf\Text\Text::_('COM_AKEEBA_CONFIG_UI_BROWSER_TITLE'); ?>
        </h4>
        <div id="folderBrowserDialogBody">
        </div>
    </div>
</div>
