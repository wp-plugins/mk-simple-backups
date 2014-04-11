=== Plugin Name ===
Contributors: michitzky
Tags: backup, db, uploads
Requires at least: 3.8
Stable tag: 0.4
Tested up to: 3.9RC1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows you to create simple backups on a dedicated page nested in the "Tools" Menu.

== Description ==

Allows you to create three basic Backups from the Backend:

* .ZIP containing your uploads
* .ZIP containing the active Theme
* SQL Dump containing the Mysql Database

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
It allows you to create three simple Backups from your Backend: Active Theme, Database, Uploads. The generated Files can then be downloaded and stored on your local drives. After downloading, the plugin allows easy flushing of the server-side Files.

= Can the Backups also be stored on the server? =
It is not advised for two reasons: first) when your Hosting/Server breaks, a server-side backup might go down with it and second) the sql Dump contains usernames for your wordpress installation, it MUST NOT be permanently stored on the server

== Screenshots ==

1. Default View

== Changelog ==

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

= 0.2.1 =
Important Fixes

= 0.1 =
It doesn't work otherwise.