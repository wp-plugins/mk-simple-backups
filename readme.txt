=== Plugin Name ===
Contributors: michitzky
Tags: backup, db, uploads
Requires at least: 3.8
Stable tag: 0.6.1
Tested up to: 3.9RC1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows you to create simple backups on a dedicated page nested in the "Tools" Menu.

== Description ==

On a page within the "Tools" Submenu, you'll find a simple form which lets you choose the ingredients of a current Backup:

* Uploads (either by scanning your Upload Directory, or by fetching Attachments from your Database)
* Active Theme (and its parent theme, if used)
* SQL-Dump containing the Mysql Database

Backups will be stored within a folder in wp-content and be downloadable from the Backend. Once the downloads are finished, the backup files on the Server can be flushed. 

Plugin Author does not take any responsibility for the safety of your data or the integrity of the generated backups.

== Installation ==

As always:

1. Upload `mk-simple-backups` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Where does the plugin show? =
After installing and activating the Plugin, it will create a new page called "Backup" in the Backend. This page is nested within "Tools".

= What does it do? =
It allows you to create simple Backups from your Backend containing: Active Theme, Database, Uploads. The generated Archive can then be downloaded and stored on your local drives. After downloading, the plugin allows easy flushing of the server-side files.

= Can the Backups also be stored on the server? =
It is not advised for two reasons: first) when your Hosting/Server breaks, a server-side backup might go down with it and second) the sql-dump contains usernames for your wordpress installation, it MUST NOT be permanently stored on the server

== Screenshots ==

1. Default View

== Changelog ==

= 0.6.1 =
* Fixed minor language glitches
* Fix for Blank File icon
* Updated Read-me

= 0.6 =
* Simplified UI by shifting from single-action links to option-based form
* Backup Name uses Site Name
* Backup Name reflects contents
* Moved blank file link to info section
* Added plugin repository link to the footer
* Updated Language Files (german, english)
* Simplified Icons
* Updated Screenshot
* Updated Read-Me

= 0.5 =
* New Backup-Type: Attempt to backup only original Uploads, saving space and server-memory (Thumbs can be regenerated later, in most cases)
* New Backup-Type: Attempt to create all backups in one go
* Theme Backup will include Parent Theme, if used
* Plugin will try to increase the amount of seconds before script-timeout to 300 (5min)
* Display custom Upload-Directory in the Plugin Interface
* Added detailed descriptions to clarify what the Plugin does to the Main Plugin page
* Updated Language Files
* Abbreviated Filehandling by using file_put_contents()
* Abbreviated Zip-Handling

= 0.4.5 =
* Skipping to 0.4.5 because of some svn trouble

= 0.4 =
* Added German Translation

= 0.3.1 =
* Read-Me Troubles fixed

= 0.3 =
* Plugin uses wp_upload_dir() to get uploads-folder
* Plugin will attempt to remove the created files and folders when it gets deactivated
* Plugin will create a .htaccess file within the backup directory, preventing it from displaying a file listing in the browser
* Fully commented code
* Bugfix for errors when backup can't be created

= 0.2.2 =
* Removed Clutter from the Read-Me File
* Expanded Description and FAQ

= 0.2.1 =
* Added a screenshot of the default view
* Fixed incorrectly generated SQL Comments

= 0.2 =
* General Clean-Up of Code
* Load CSS only on Plugin Page
* removed inline styles
* Added icons to the Create-Backup Links and the generated files
* Translated Plugin to english
* Added i18n 

= 0.1 =
* Basic Functionality

== Upgrade Notice ==

= 0.6 =
Switching to form based Backup Creation

= 0.5 =
Added features, check the changelog for detailed information

= 0.2.1 =
Important Fixes

= 0.1 =
It doesn't work otherwise.