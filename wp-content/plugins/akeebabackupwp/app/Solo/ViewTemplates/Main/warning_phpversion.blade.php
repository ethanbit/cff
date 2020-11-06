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
{{-- Old PHP version reminder --}}
@include('CommonTemplates/phpversion_warning', [
    'softwareName'  => $this->getContainer()->segment->get('insideCMS', false) ? 'Akeeba Backup' : 'Akeeba Solo',
    'minPHPVersion' => '5.6.0',
])