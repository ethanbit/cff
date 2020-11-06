=== Akeeba Backup CORE for WordPress ===
Contributors: nikosdion
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10903325
Tags: backup, restore, migrate, move
Requires at least: 3.8.0
Tested up to: 4.5
Requires PHP: 5.4
Stable tag: 7.1.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl.html

Easily backup, restore and move your WordPress site with the fastest, most robust, native PHP backup plugin.

== Description ==

Akeeba Backup Core for WordPress is an open-source, free of charge backup plugin for WordPress, quite a bit different
than the others. Its mission is simple: create a full site backup (files and database) that can be restored on any
WordPress-capable server. Even without having WordPress already installed.

Akeeba Backup creates a full backup of your site in a single archive. The archive contains all the files,
a database snapshot and a web installer which is as easy to use as WordPress' famous five minute installation procedure.
The backup and restore process is AJAX powered to avoid server timeouts, even with huge sites. Serialised data is
handled automatically. Our long experience –the backup engine is being continuously developed and perfected since 2006–
guarantees that. You can also make a backup of only your database, or only your site's files.

If you want a reliable, easy to use, open source backup solution for your WordPress site, you've found it!

*Important note*: The software, its [documentation](https://www.akeebabackup.com/documentation/akeeba-solo.html)
 and [video tutorials](https://www.akeebabackup.com/videos/1215-akeeba-backup-wordpress.html) are
 provided free of charge. Personalised support is not free; it requires paying for a support subscription. That's what
 pays the bills and lets us keep on writing good quality software full time.

Features:

* You own your data. Hosted services hold your data only as long as you pay them a monthly fee. With Akeeba Backup you have full control over the backup archives you generate.
* Send your backups to another server by FTP or SFTP. (SFTP support requires the SSH2 PHP module to be installed on the server hosting your WordPress site).
* Serialised data are automatically adjusted on restoration WITHOUT third party tools and WITHOUT precarious regular expressions which can break your site.
* WordPress Multisite supported out of the box, today.
* The fastest native PHP backup engine. You don't need to upload Linux executable files on your server!
* Works on any virtually any server environment: Apache, NginX, Lightspeed, Lighttpd, IIS and more on Windows, Linux, Mac OS X, Solaris and more.
* No more timeouts on large sites. Our renowned engine is designed for big sites in mind. Largest successfully backed up site reported so far: 110GB (yes, Gigabytes).
* It configures itself for optimal operation with your site. Just click on Configuration Wizard.
* One click backup with desktop notifications when it's finished. No need to stare at the screen any more.
* AJAX-powered backup (site and database, database only, files only or incremental files only backup).
* Choose between standard ZIP format, the highly efficient JPA archive format or the encrypted JPS format (encrypted JPS format available in paid version only).
* You can exclude specific files and folders.
* You can exclude specific database tables or just their contents.
* Unattended backup mode (scheduled / automated backups), fully compatible with WebCRON.org.
* *NEW* Scheduled backups with CRON jobs running on your server.
* *NEW* Automatic log analyser to help you fix backup issues without having to pay for a support subscription.
* AJAX-powered site restoration script included in the backup.
* *NEW* Integrated restoration for restoring the backup on the same server you backed up from.
* Import backup archives after uploading them back to your server. Useful for restoring after reinstalling WordPress on the same or a new server.
* Archives can be restored on any host using Akeeba Kickstart (free of charge script to extract the backup archives on any server, *without* installing WordPress and Akeeba Backup). Useful for transferring your site between subdomains/hosts or even to/from your local testing server (XAMPP, WAMPServer, MAMP, Zend Server, etc).

and much, much more!

Indicative uses:

* Security backups.
* Creating development sites to test new ideas, make site redesigns or troubleshoot issues.
* Transfer a site you created locally to a live server.
* Create "template" sites and clone them to fast-track the development of your clients' sites.

Restoring your backups requires extracting them first. If you are restoring to a different server you need to download
our [free of charge Akeeba Kickstart script](https://www.akeebabackup.com/download/akeeba-kickstart.html) from our site.
If you are restoring on the same server you can simply use the integrated restoration feature in the plugin itself.

If you need to extract a backup archive on your Windows, Linux or Mac OS X computer you can use our free of charge
[Akeeba eXtract Wizard](github.com/akeeba/nativexplatform/releases) desktop software.

[More features](https://www.akeebabackup.com/products/1610-akeeba-wp-core-vs-professional.html) are available in the
separate product called "Akeeba Backup Professional for WordPress" which you can only download after purchasing a
[support subscription](https://www.akeebabackup.com/subscribe/new/backupwp.html?layout=default) on our site. This
includes automatically transferring your backups to Amazon S3, Dropbox, OneDrive, Box.com and another 40+ storage
providers for safekeeping. Clarification: these features are NOT available in Akeeba Backup CORE for WordPress available
from WordPress.org. These premium features are only provided as a thank-you to people who choose to support us
financially by purchasing a support subscription on our site.

== Installation ==

1. Install Akeeba Backup for WordPress either via the WordPress.org plugin directory, or by uploading the files to your
   server. In the latter case we suggest you to upload the files into your site's `/wp-content/plugins/akeebabackupwp`
   directory.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You will see the Akeeba Backup icon in your sidebar, below the Plugins section in the wp-admin area of your site.
   Click on it.
1. Click on the Configuration Wizard button and site back while the plugin configures itself *automatically*.
1. Every time you want to take a backup, click on the big blue Backup Now button in the plugin's interface.
1. That's it! Really, it is that simple!

== Frequently Asked Questions ==

= I have spotted a bug. Now what? =

Please use [our Contact Us page](https://www.akeebabackup.com/contact-us.html) to file a bug report. Make sure that you
indicate "Bug report" in the Category selection. We will review your bug report and work to fix it. We may contact you
for further information if necessary. If we don't contact you be assured that if you did report a bug we are already
working on fixing it.

= I am trying to install the plugin but the upload fails =

The plugin is quite big (around 4MB). Most servers have an upload limit of 2MB. You can either ask your host to increase
the file upload limit to 5MB or you can install the plugin manually. Please see the Installation section for more
information.

= I have a problem using the plugin. What should I do? =

The first thing you should do is [read our extensive documentation](https://www.akeebabackup.com/documentation/akeeba-solo.html)
and our [troubleshooter](https://www.akeebabackup.com/documentation/troubleshooter.html). If you'd like to receive
personalised support from the developers of the plugin you can [subscribe](https://www.akeebabackup.com/subscribe/new/backupwp.html?layout=default)
to our services. Due to the very specialised nature of the software and our goal of providing exceptional support we do
not outsource our support. All support requests are answered by the developers who write the software. This is why we
require a subscription to provide support.

= Does your software support WordPress MU (multi-sites a.k.a. blog networks)? =

Yes. We have added full WordPress multi-sites support since late 2014. You can restore backups to different servers or
locations and things will still work.

= What about serialised data? =

Not a problem! You've probably used a lot of tools to try and manually replace serialised data after moving your site to
a different domain or directory and you were worried because they don't always work very well. We have implemented our
own tokeniser and assembler for serialised data which works the same way PHP works under the hood. Simply put, our
solution doesn't use precarious regular expressions and isn't even the least inclined on killing your serialised data.

Please note that for data replacement to work properly all of your plugins must be storing their data in UTF-8 encoding
in the database. Some themes use a double encoding which may result in invalid data. Unfortunately that's a problem with
these themes and we can't fix it. On the other hand these themes' developers seem to be aware of this issue and provide
their own settings export and import. If your theme provides such a feature please use it. We can't reliably work around
third party code not following the character encoding standards established well over twenty years ago...

= WordPress moved to UTF8MB4 (UTF-8 Multibyte). Do you support it? =

Yes, in full. Akeeba Backup will work no matter if your site uses UTF8MB4 or the old UTF-8 encoding. If you backup a
site with data encoded in UTF-8 the restoration will work on a server supporting UTF8MB4. Going the opposite way will
not work because of a MySQL restriction. If you end up with truncated text or MySQL errors on restoration that's the
reason. In this case you will have to ask your host to update their version of MySQL to 5.5 or later.

= What are the requirements for your plugin? =

Akeeba Backup for WordPress requires PHP 5.4 or any later version. Older versions of PHP including PHP 4, 5.0, 5.1,
5.2 and 5.3 are not supported. We recommend using PHP 5.6 or later for security and performance reasons.

Akeeba Backup for WordPress has been tested on WordPress 3.8 and later. It should work on earlier versions of WordPress
but we cannot guarantee this.

Akeeba Backup for WordPress requires at least 16MB of PHP memory (memory_limit). We strongly suggest 64MB or more for
optimal operation on large sites with hundreds of media files and hundreds of thousands of comments.

Some features may require the PHP cURL extension to be installed and activated on your server. If unsure ask your host.

Finally, you need adequate disk space to take a backup of your site. As a rule of thumb, that's about 80% the current
size of your site's public web directory (usually called public_html, htdocs, httpdocs, www or something in the like).

= Can I use this plugin on commercial sites / sites I am building for my clients? =

Yes, of course! Our plugin is licensed under the GNU General Public License version 3 or, at your option, any later
version of the license published by the Free Software Foundation. This license gives you the same Four Freedoms as
WordPress' license; in fact, GPLv3 is simply a newer version of the same GPLv2 license WordPress is using, one which
protects your interests even more.

= I have sites using other scripts / CMS. Can I use your software with them? =

Akeeba Backup is available in three different packages. Akeeba Backup for WordPress is designed to backup and restore
WordPress sites. Akeeba Backup for Joomla! does the same for Joomla! sites. Akeeba Solo is our standalone backup
software which support WordPress, Joomla!, Magento, PrestaShop, phpBB3 and many other CMS and scripts. Use the contact
link on our site to request more information for your specific needs.

== Screenshots ==

1. A control panel interface puts everything you need under your fingertips.
2. Akeeba Backup automatically configures itself for optimal performance on your site.
3. Click on Backup Now, sit back and your backup is taken in a snap.
4. Managing backups is dead simple. And see just how fast backups are!
5. Advanced users can tweak Akeeba Backup to their liking
6. Excluding directories uses an intuitive file manager. No need to fiddle with unsightly directory names!
7. Want to automate your backups? Akeeba Backup will give you step by step instructions, specific to your site.

== Changelog ==

* eeba Solo 7.1.2
* Improved error handling allows reporting PHP fatal errors (only available on sites using PHP 7)
* [LOW] Fixed typos that could create issues with servers using very restrictive security rules
* [LOW] Error page would trigger an error, effectively making all errors invisible without using WordPress' debug mode
* [LOW] (S)FTP connection test would report "false" instead of the reason of failure
* [LOW] Fixed archive download using the browser under some circustances

= 7.1.1 =
* Possible exception when the user has erroneously put their backup output directory to the site's root with open_basedir restrictions restricting access to its parent folder.
* [MEDIUM] OneDrive for Business is not working at all in Akeeba Backup for WordPress

= 7.1.0 =
* Automatic security check of the backup output directory
* Option to change post GUIDs on restoration
* Yes/No toggles are now colorful instead of plain teal
* Renamed helper functions for the benefit of some WordPress themes which try to redefine them
* Improved storage of temporary data during backup [akeeba/engine#114]
* Log files now have a .php extension to prevent unauthorized access in very rare cases
* Enforce the recommended, sensible security measures when using the default backup output directory
* Ongoing JavaScript refactoring
* Google Drive: fetch up to 100 shared drives (previously: up to 10)
* [MEDIUM] CloudFiles post-processing engine: Fixed file uploads
* [MEDIUM] Swift post-processing engine: Fixed file uploads
* [LOW] Send by Email reported a successful email sent as a warning
* [LOW] Extra greater-than sign in the Configuration icon's URL in the Control Panel page
* [LOW] Database dump: foreign keys' (constraints) and local indices' names did not get their prefix replaced like tables, views etc do

= 7.0.2 =
* Log the full path to the computed site's root, without <root> replacement
* [HIGH] Core (free of charge) version only: the PayPal donation link included a tracking pixel. Changed to donation link, without tracking.
* [MEDIUM] Integrated restoration: sanity checks were not applied, resulting in extraction errors
* [MEDIUM] WebDav post-processing engine: first backup archive was always uploaded on the remote root, ignoring any directory settings
* [HIGH] Restoration will fail if a table's name is a superset of another table's name e.g. foo_example_2020 being a superset of foo_example_2.

= 7.0.1 =
* pCloud: removing download to browser (cannot work properly due to undocumented API restrictions)
* [HIGH] An error about not being able to open a file with an empty name occurs when taking a SQL-only backup but there's a row over 1MB big
* [LOW] Notice in Control Panel when maximum error reporting is enabled
* [LOW] Backup log file did not appear correctly (but you could still download it)
* [LOW] Redirections for the legacy frontend backup method should be to remote.php, not index.php
* [LOW] Bad HTML in the document head when using raw display (e.g. Manage Remote Files popup)
* [LOW] Fixed displaying release notes when a new version comes out
* [LOW] Dark Mode: modal close icon was invisible both in the backup software and during restoration
* [LOW] Fixed automatically filling DropBox tokens after OAuth authentication

= 7.0.0 =
* Remove TABLESPACE and DATA|INDEX DIRECTORY table options during backup
* [LOW] FTP and SFTP connection tests were always failing
* [LOW] Fixed applying quotas for obsolete backups

= 7.0.0.rc1 =
* Upload to OVH now supports Keystone v3 authentication, mandatory starting mid-January 2020
* Remove obsolete "Use IFRAMEs instead of AJAX" option
* [HIGH] An error in an early backup domain could result in a forever-running backup
* [HIGH] DB connection errors wouldn't result in the backup failing, as it should be doing
* [HIGH] Manage remotely stored files: Fetch to server would fail after the first batch of downloads

= 7.0.0.b3-patch1 =
* Missing files led to immediate backup failure

= 7.0.0.b3 =
* Common PHP version warning scripts
* Reinstated support for pCloud after they fixed their OAuth2 server
* Improved Dark Mode
* Improved PHP 7.4 compatibility
* Improved integration with the WordPress plugins update system
* Clearer message when setting decryption fails in CLI backup script
* Replace JavaScript eval() with JSON.parse()
* [HIGH] The database dump was broken with some versions of PCRE (e.g. the one distributed with Ubuntu 18.04)
* [HIGH] The integrated restoration was broken

= 7.0.0.b2 =
* Removed pCloud support
* ANGIE: Options to remove AddHandler lines on restoration
* [MEDIUM] Fixed OAuth authentication flow
* [LOW] Configuration wizard will always prompt to the user

= 7.0.0.b1 =
* Amazon S3 now supports Bahrain and Stockholm regions
* Amazon S3 now supports Intelligent Tiering, Glacier and Deep Archive storage classes
* Google Storage now supports the nearline and coldline storage classes
* Manage Backups: Improved performance of the Transfer (re-upload to remote storage) feature.
* Windows Azure BLOB Storage: download back to server and download to browser are now supported
* New OneDrive integration supports both regular OneDrive and OneDrive for Business
* pCloud support
* Support for Dropbox for Business
* Minimum required PHP version is now 5.6.0
* Common version numbering among all of our backup products means this version is 7, not 4
* All views have been converted to Blade for easier development and better future-proofing
* The integrated restoration feature is now only available in the Professional version
* The front-end legacy backup API and the Remote JSON API are now available only in the Professional version
* The Site Transfer Wizard is now only available in the Professional version
* WP-CLI integration is now only available in the Professional version
* SugarSync integration: you now need to provide your own access keys following the documentation instructions
* Backup error handling and reporting (to the log and to the interface) during backup has been improved.
* The Test FTP/SFTP Connection buttons now return much more informative error messages.
* Manage Backups: much more informative error messages if the Transfer to remote storage process fails.
* The backup and log IDs will follow the numbering you see in the left hand column of the Manage Backups page.
* Manage Backups: The Remote File Management page is now giving better, more accurate information.
* Manage Backups: Fetch Back To Server was rewritten to gracefully deal with more problematic cases.
* Removed AES encapsulations from the JSON API for security reasons. We recommend you always use HTTPS with the JSON API.
* [HIGH] Changing the database prefix would not change it in the referenced tables inside PROCEDUREs, FUNCTIONs and TRIGGERs
* [HIGH] Backing up PROCEDUREs, FUNCTIONs and TRIGGERs was broken
* [HIGH] Manage Backups: would not show Transfer Archive for qualifying backups not fully uploaded to remote storage.
* [MEDIUM] Database only backup of PROCEDUREs, FUNCTIONs and TRIGGERs does not output the necessary DELIMITER commands to allow direct import
* [MEDIUM] BackBlaze B2: upload error when chunk size is higher than the backup archive's file size
* [LOW] Manage Backups: the Remote Files Management dialog's size was off by several pixels
* [LOW] Manage Backups: downloading a part file from S3 beginning with text data would result in inline display of the file instead of download.
* [LOW] Disabled menu items (e.g. Backup Now) page confused people; removed them to prevent confusion


== Upgrade Notice ==

Please consult our site