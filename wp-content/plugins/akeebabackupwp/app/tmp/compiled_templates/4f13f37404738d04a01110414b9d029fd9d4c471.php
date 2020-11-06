<?php /* /home/citytestfine/public_html/wp-content/plugins/akeebabackupwp/app/Solo/ViewTemplates/Main/warning_phpversion.blade.php */ ?>
<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;
use \Awf\Date\Date;

defined('_AKEEBA') or die();

// Used for type hinting
/** @var \Solo\View\Main\Html $this */

?>
<?php /* Old PHP version reminder */ ?>
<?php echo $this->loadAnyTemplate('CommonTemplates/phpversion_warning', [
    'softwareName'  => $this->getContainer()->segment->get('insideCMS', false) ? 'Akeeba Backup' : 'Akeeba Solo',
    'minPHPVersion' => '5.6.0',
]); ?>