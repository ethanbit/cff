<?php /* /home/citytestfine/public_html/wp-content/plugins/akeebabackupwp/app/Solo/ViewTemplates/CommonTemplates/FTPConnectionTest.blade.php */ ?>
<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') or die();

?>
<?php /* (S)FTP connection test */ ?>
<div class="modal fade" id="testFtpDialog" tabindex="-1" role="dialog" aria-labelledby="testFtpDialogLabel"
     aria-hidden="true" style="display:none;">
    <div class="akeeba-renderer-fef <?php echo ($this->getContainer()->appConfig->get('darkmode', -1) == 1) ? 'akeeba-renderer-fef--dark' : '' ?>">
        <h4 class="modal-title" id="testFtpDialogLabel"></h4>
        <div class="akeeba-block--success" id="testFtpDialogBodyOk"></div>
        <div class="akeeba-block--failure" id="testFtpDialogBodyFail"></div>
    </div>
</div>
