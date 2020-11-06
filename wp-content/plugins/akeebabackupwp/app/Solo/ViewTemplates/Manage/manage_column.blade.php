<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;
use Solo\Helper\Utils as AkeebaHelperUtils;

defined('_AKEEBA') or die();

/** @var  array $record */
/** @var  Solo\View\Manage\Html $this */

$router = $this->container->router;

if (!isset($record['remote_filename']))
{
	$record['remote_filename'] = '';
}

$archiveExists = $record['meta'] == 'ok';
$showManageRemote = in_array($record['meta'], array(
		'ok', 'remote'
	)) && !empty($record['remote_filename']) && (AKEEBABACKUP_PRO == 1);
$showUploadRemote = $this->privileges['backup'] && $archiveExists && empty($record['remote_filename']) && ($this->enginesPerProfile[$record['profile_id']] != 'none') && ($record['meta'] != 'obsolete') && (AKEEBABACKUP_PRO == 1);
$showDownload = $this->privileges['download'] && $archiveExists;
$showViewLog = $this->privileges['backup'] && isset($record['backupid']) && !empty($record['backupid']);
$postProcEngine = '';
$thisPart = '';
$thisID = urlencode($record['id']);

if ($showUploadRemote)
{
	$postProcEngine = $this->enginesPerProfile[$record['profile_id']];
	$showUploadRemote = !empty($postProcEngine);
}

?>
<div style="display: none">
    <div id="akeeba-buadmin-{{ (int)$record['id'] }}" tabindex="-1" role="dialog">
        <div class="akeeba-renderer-fef {{ ($this->getContainer()->appConfig->get('darkmode', -1) == 1) ? 'akeeba-renderer-fef--dark' : '' }}">
            <h4>@lang('COM_AKEEBA_BUADMIN_LBL_BACKUPINFO')</h4>

            <p>
                <strong>@lang('COM_AKEEBA_BUADMIN_LBL_ARCHIVEEXISTS')</strong><br />
                @if ($record['meta'] == 'ok')
                    <span class="akeeba-label--success">
					@lang('SOLO_YES')
				</span>
                @else
                    <span class="akeeba-label--failure">
					@lang('SOLO_NO')
				</span>
                @endif
            </p>
            <p>
                <strong>@lang('COM_AKEEBA_BUADMIN_LBL_ARCHIVEPATH' . ($archiveExists ? '' : '_PAST'))</strong>
                <br />
                <span class="akeeba-label--information">
		        {{{ AkeebaHelperUtils::getRelativePath(APATH_BASE, dirname($record['absolute_path'])) }}}
		</span>
            </p>
            <p>
                <strong>@lang('COM_AKEEBA_BUADMIN_LBL_ARCHIVENAME' . ($archiveExists ? '' : '_PAST'))</strong>
                <br />
                <code>
                    {{{ $record['archivename'] }}}
                </code>
            </p>
        </div>
    </div>

    @if ($showDownload)
        <div id="akeeba-buadmin-download-{{ (int) $record['id'] }}" tabindex="-2" role="dialog">
            <div class="akeeba-renderer-fef {{ ($this->getContainer()->appConfig->get('darkmode', -1) == 1) ? 'akeeba-renderer-fef--dark' : '' }}">
                <div class="akeeba-block--warning">
                    <h4>
                        @lang('COM_AKEEBA_BUADMIN_LBL_DOWNLOAD_TITLE')
                    </h4>
                    <p>
                        @lang('COM_AKEEBA_BUADMIN_LBL_DOWNLOAD_WARNING')
                    </p>
                </div>

                @if ($record['multipart'] < 2)
                    <a class="akeeba-btn--primary--small comAkeebaManageDownloadButton"
                       data-id="{{{ $record['id'] }}}">
                        <span class="akion-ios-download"></span>
                        @lang('COM_AKEEBA_BUADMIN_LOG_DOWNLOAD')
                    </a>
                @endif

                @if ($record['multipart'] >= 2)
                    <div>
                        @sprintf('COM_AKEEBA_BUADMIN_LBL_DOWNLOAD_PARTS', $record['multipart'])
                    </div>
                    @for ($count = 0; $count < $record['multipart']; $count++)
                    @if ($count > 0)
                    &bull;
                @endif
                <a class="akeeba-btn--small--dark"
                   data-id="{{{ $record['id'] }}}"
                   data-part="{{{ $count }}}">
                    <span class="akion-android-download"></span>
                    @sprintf('COM_AKEEBA_BUADMIN_LABEL_PART', $count);
                </a>
                @endfor
                @endif
            </div>
        </div>
    @endif
</div>

@if ($showManageRemote)
    <div style="padding-bottom: 3pt;">
        <a class="akeeba-btn--primary akeeba_remote_management_link"
           data-management="{{{ $router->route('index.php?view=Remotefiles&tmpl=component&task=listActions&id=' . (int)$record['id']) }}}"
           data-reload="{{{ $router->route('index.php?view=Manage') }}}"
        >
            <span class="akion-cloud"></span>
            @lang('COM_AKEEBA_BUADMIN_LABEL_REMOTEFILEMGMT')
        </a>
    </div>
@elseif ($showUploadRemote)
    <a class="akeeba-btn--primary akeeba_upload"
       data-upload="{{{ $router->route('index.php?view=Upload&tmpl=component&task=start&id=' . $record['id']) }}}"
       data-reload="{{{ $router->route('index.php?view=Manage') }}}"
       title="<?php echo Text::sprintf('COM_AKEEBA_TRANSFER_DESC', Text::_("ENGINE_POSTPROC_{$postProcEngine}_TITLE")) ?>"
    >
        <span class="akion-android-upload"></span>
        @lang('COM_AKEEBA_TRANSFER_TITLE')
        (<em>{{ $postProcEngine }}</em>)
    </a>
@endif

<div style="padding-bottom: 3pt">
    @if ($showDownload)
        <a class="akeeba-btn--{{ $showManageRemote || $showUploadRemote ? 'smal--grey' : 'green' }} akeeba_download_button"
           data-dltarget="#akeeba-buadmin-download-{{ (int)$record['id'] }}"
        >
            <span class="akion-android-download"></span>
            @lang('COM_AKEEBA_BUADMIN_LOG_DOWNLOAD')
        </a>
    @endif

    @if ($showViewLog)
        <a class="akeeba-btn--grey akeebaCommentPopover"
           {{ ($record['meta'] != 'obsolete') ? '' : 'disabled="disabled"' }}
           href="@route('index.php?view=Log&tag=' . $this->escape($record['tag']) . '.' . $this->escape($record['backupid']) . '&task=start&profileid=' . $record['profile_id'])"
           data-original-title="@lang('COM_AKEEBA_BUADMIN_LBL_LOGFILEID')"
           data-content="{{{ $record['backupid'] }}}"
        >
            <span class="akion-ios-search-strong"></span>
            @lang('COM_AKEEBA_LOG')
        </a>
    @endif

    <a class="akeeba-btn--grey--small akeebaCommentPopover akeeba_showinfo_link"
       data-infotarget="#akeeba-buadmin-{{ (int)$record['id'] }}"
       data-content="@lang('COM_AKEEBA_BUADMIN_LBL_BACKUPINFO')"
    >
        <span class="akion-information-circled"></span>
    </a>
</div>
