=== Plugin Name ===
Contributors: michitzky
Tags: backup, db, uploads
Requires at least: 3.7
Stable tag: 1.0.2
Tested up to: 4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows you to create simple backups on a dedicated page nested in the "Tools" Menu.

== Description ==

On a page within the "Tools" Submenu, you'll find a simple form which lets you choose the ingredients of a current Backup:

* Uploads (either by scanning your Upload Directory, or by fetching Attachments from your Database)
* Active Theme (and its parent theme, if used)
* SQL-Dump containing the Mysql Database
* A list of active Plugins

Backups will be stored within a folder in wp-content and be downloadable from the Backend. Once the downloads are finished, the backup files on the Server can be flushed. 

What's good:

* Simple: Choose what is included in your Backup and download the resulting ZIP Archive from your Backend
* Unobtrusive: It's nested within the "Tools" submenu and comes without any bling bling, following default WP Backend Styles
* Lightweight: 28KB zipped, 72KB unzipped

The plugin author does not take any responsibility for the safety of your data or the integrity of the generated backups.

Credits:

This plugin uses 2createStudio's shuttle export Script for SQL Export:
https://github.com/2createStudio/shuttle-export

Banner-Image on Repository: B. Walker, 11.08.2010: http://www.fotocommunity.de/pc/pc/display/22021026
Icons within Plugin: FamFamFam Silk Icons: http://www.famfamfam.com/lab/icons/silk/

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
It is not advised for two reasons: first) when your Hosting/Server breaks, a server-side backup might go down with it and second) the sql-dump contains usernames for your wordpress installation, hence it must not be permanently stored on the server

= Why are Plugins not backed up? =
Plugins are a vital part of many Wordpress Installations, which makes them prime candidates for a backup. However, there are a couple of reasons not to include them: a) They outdate quickly, making a backed up Version easily obsolete, b) Plugins can get  large and in quantity would slow down Backups considerably, c) They must not include custom code and therefore can easily be re-downloaded in current or older Versions from the repository. This plugin can generate a plain-text list of currently active Plugins with their respective Version-Number and the Plugin-URI.

= Why is there no way to schedule Backups? =
This Plugin was designed around the idea of having a simple tool to create small Backups before updating Wordpress. Scheduled and automatic Backups are currently not planned. If you're looking for something like that, I suggest using UpdraftPlus or BackWPup.

== Screenshots ==

1. Default View

== Changelog ==

= 1.0.2 =
* Using shuttle export by 2createStudio for SQL Export: 
https://github.com/2createStudio/shuttle-export
* Removed custom export code
* Fixed some typos

= 1.0.1 =
* Plugin will check whether system() is enabled and deactivate the SQL-Dump if not
* Removed manual SQL Dump, too many possibilities of incomplete dumps
* Reordered components

= 1.0 =
* Changed the workflow of generating Backups: Instead of creating separate ZIPs for each component, the Plugin will now generate a single ZIP and add the requested parts to this single Archive. This greatly enhances Backup-Speed on file-intensive Media Libraries and Themes (no more double zipping)
* Added the option to include a list of active Plugins in your Backup
* Changed in-Archive folders to a more convenient structure
* Fixed error when trying to flush empty Backup-Directory
* Plugin uses long php starttags in all files
* Moved Blank File link to the Bottom
* Updated Helptexts
* Updated FAQ

= 0.7.3 = 
* Compatibility for Wordpress 4.0
* Added Icons for the new Plugin Browser
* Fixed Error when attempting to backup empty Uploads

= 0.7.2 =
* Plugin attempts to use system() for SQL Dump, reducing server-load, especially for larger Databases. Old approach will be attempted on hostings without support for system()

= 0.7.1 =
* Files within ZIP don't use complete server-path, but only relevant folders
* Added copyright information for Repository Banner
* Language Fix

= 0.7 =
* Plugin now saves the form-state when creating a Backup
* Plugin option will be removed when deactivating the Plugin
* Added Plugin Repository Banner (Armadillo!)
* Updated Screenshot
* Updated Read-me

= 0.6.2 =
* Fixed Issue with Upload Backup

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

= 0.7.1 =
Faster and more reliable SQL Dumps await in 0.8


