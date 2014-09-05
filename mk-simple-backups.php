<?php

/**
 * Plugin Name: mk Simple Backups
 * Plugin URI: http://wordpress.org/plugins/mk-simple-backups/
 * Description: Allows you to create simple backups on a dedicated page nested in the "Tools" Menu.
 * Version: 1.0
 * Author: Michael Kühni
 * Author URI: http://michaelkuehni.ch
 * License: GPL2
 */


// backend only
if(is_admin()) {
	
	
	
	// get plugin directory
	$plugin_dir_mksimplebackups = plugin_dir_url( __FILE__ );



	// register a spot in the Backend Nav Structure
	function mk_simple_backups_register_menuitems() {
		$mksbkp_page = add_management_page( "Backup", "Backup", "manage_options", "mk-simple-backups", "mkMainBackupDisplay", "" );
		add_action( "admin_print_scripts-$mksbkp_page", 'mk_simple_backups_enqueue_scripts' );
		// attempt to increase execution time for large backups, 5 minutes
		@set_time_limit(300);
	}
	add_action( 'admin_menu', 'mk_simple_backups_register_menuitems' );
	// register a deactivate function, firing when the plugin gets deactivated
	register_deactivation_hook( __FILE__, 'mk_simple_backups_deactivate' );
	// enqeue css
	function mk_simple_backups_enqueue_scripts() {
		global $plugin_dir_mksimplebackups;
		wp_enqueue_style("mk-simple-backups-general", $plugin_dir_mksimplebackups . "/assets/css/general.css", null );
	}
	// load plugin textdomain
	function mk_simple_backups_load_textdomain() {
	  load_plugin_textdomain( 'mk-simple-backups', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
	}
	add_action( 'init', 'mk_simple_backups_load_textdomain' );
	
	
	// set option
	update_option( $settings_var_name, $settings );
	

	// get Classes
	require_once("class.backuphandler.php");
	
	// instantiate class
	$bkp = new backupHandler();

	
	

	function mkMainBackupDisplay() {
	
		global $_GET;
		global $bkp;
		global $wpdb;
	
		
		
		
		$msg = array();
		$this_plugin_data = get_plugin_data( __FILE__);
	
	
		?>
		<div class="wrap">
		
			<h2><?php _e( 'Backup', 'mk-simple-backups' ); ?></h2>
			<?php
		
			// by $_GET parameter, determine action and create message for success/failure
			switch($_GET["action"]) {
			
				default:
					// nothing
					break;
				
				case "createtestfile":
					$s = $bkp->createTestFile();
					if($s != false) $msg[] = array("txt"=>sprintf(__( 'Blank File %s created', 'mk-simple-backups' ), $s ));
					else $msg[] = array("txt"=>__( 'Blank File could not be created', 'mk-simple-backups' ), "error"=>true);
					break;
				
				case "flush";
					if($bkp->backups_exist) {
						$s = $bkp->flushBackupDir();
						if($s) $msg[] = array("txt"=>__("Files in the backup directory were deleted", "mk-simple-backups")); 
						else $msg[] = array("txt"=>__("Some files could not be removed by the script. Check File-Permissions or delete the files manually.", "mk-simple-backups"), "error" => true);
					}
					break;
					
				case "createBackup":
					
					$bkp_options = $_POST["options"];
					$textdesc = array();
					$new_settings = array( "db"=>false, "theme"=>false, "upload"=>false, "plugins"=>false, "upload_type"=>"db" );
					
					
					
					
					if(count($bkp_options) > 0) {
						
						
						$bkp->createZip();
						
						
						foreach($bkp_options AS $o) {
							
							switch($o) {
								case "db":
									$s = $bkp->addDBBackup();
									if($s == false) {
										$s = $bkp->addManualDBBackup();
									}
									$desc = $textdesc["db"] = __("Database", "mk-simple-backups");
									$new_settings["db"] = true;
									break;
								case "theme":
									$s = $bkp->addThemeBackup();
									$desc =  $textdesc["theme"] = __("Active Theme", "mk-simple-backups");
									$new_settings["theme"] = true;
									break;
								case "plugins":
									$s = $bkp->addPluginList();
									$desc = $textdesc["plugins"] =  __("List of Plugins", "mk-simple-backups");
									$new_settings["plugins"] = true;
									break;
								case "uploads":
									$new_settings["upload"] = true;
									if($_POST["upload_options"] == "file") {
										$s = $bkp->addUploadBackup();
										$desc = $textdesc["upload"] =  __("Uploads (file based)", "mk-simple-backups");
										$new_settings["upload_type"] = "file";
									}
									else {
										$s = $bkp->addUploadBackupByDB();
										$desc = $textdesc["upload"] = __("Uploads (db based)", "mk-simple-backups");
										$new_settings["upload_type"] = "db";
									}
									break;
							}
							
							if($s == false) {
								
								$msg[] = array( "txt"=>sprintf(__("Complete Backup failed upon attempting to backup %s", "mk-simple-backups"), $desc), "error"=>true );
								break;
								
							}	
						} // end foreach
						
						
						
						if($s == true) {
							
							$bkp->closeZip();
							
							// remove leftover Files
							if(count($bkp->tmp_files > 0)) $bkp->removeFiles( $bkp->tmp_files );
							
							$bkp->backups_exist = true;
							
							if(count($bkp_options) < 4) {
								
								$msg[] = array( "txt"=>sprintf(__("Backup %s created (containing %s)", "mk-simple-backups"), $bkp_filename, implode(", ", $textdesc)) );
								
							} else $msg[] = array( "txt"=>sprintf(__("Backup %s created (complete)", "mk-simple-backups"), $bkp_filename ) );
							
						} else {
							
							$msg[] = array( "txt"=>sprintf(__("ZIP Archive %s failed, partial Backups exist", "mk-simple-backups"), $bkp_filename), "error"=>true );
							
						}

					} // end if check for anything to backup
					
					
					break;
			}
			
			// save settings
			$settings_var_name = "mk-simple-backups-settings";
			if(isset($new_settings)) update_option( $settings_var_name, serialize($new_settings));
			
			// get settings
			$settings_s =  get_option($settings_var_name);
			if($settings_s == false) {
				// default settings
				$settings = array( "db" => true, "upload" => true, "theme" => true, "upload_type" => "db");
			} else $settings = unserialize($settings_s);
			
			
		
			// echo msg's
			if(count($msg) > 0) {
				?>
				<div id="message" class="updated">
					<?php
					foreach($msg AS $m) {
					
						if($m["error"] == true) $class = ' class="error"';
						else unset($class);
					
						?><p <?=$class?>><?=$m["txt"] ?></p><?php
					}
					?>
				</div>
				<?php
			}
			
			
			// check if backups exists
			if($bkp->backups_exist) {
				
				// get list of files in bkp dir
				$files = $bkp->getBackupList( array(".htaccess") );
			
				?>
				<h3><?php printf(
						_n(
							__('One file', "mk-simple-backups"),
							__('%s files', "mk-simple-backups"),
							count($files),
							'mk-simple-backups'
						),count($files));  ?></h3>
				<ul class="filelist">
				<?php
				
				// cycle through files in bkp dir
				foreach($files AS $name=>$src) {
					
					// determine type of file by the first 2 characters
					$suffix = substr($name, strlen($name)-3);
	
					if($suffix == "zip") $fileclass = "archive";
					else $fileclass = "default";
					
					// get filesize
					$filesize = filesize( $bkp->backup_dir . "/" . $src);
					
					// format filesize
					if($filesize/900000 > 1) $nice_filesize = round($filesize/1000000, 2) . " MB";
					else if ($filesize/1000 > 1) $nice_filesize = round($filesize/1000, 1) . " KB";
					else $nice_filesize = $filesize . " B";
				
					?>
					<li class="<?=$fileclass?>"><a href="<?php echo $bkp->backup_dir_weburl . "/" . $src ?>" target="_blank"><?php echo $name?></a>, <?php echo $nice_filesize; ?></li>
					<?php
				}
				?>
				</ul>
				<p><strong><?php _e('Important', 'mk-simple-backups'); ?></strong>: <?php _e('Since the sql-dump-file will include usernames for your wp-installation, make sure to remove it after downloading the backup.', 'mk-simple-backups'); ?></p>
				<p><a href="<?php bloginfo("siteurl"); ?>/wp-admin/tools.php?page=mk-simple-backups&amp;action=flush" style="color:crimson;"><?php _e('Delete files in Backup directory.', 'mk-simple-backups'); ?></a></p>
				<?php
			
			} else {
				?><p><?php _e('Currently no backups stored on the server', 'mk-simple-backups'); ?></p><?
			}
		

		
			?>
		
			<p><hr /></p>
		
			<?php 
			if($bkp->backup_directory_exists == true) {
				
				?>
				<h3><?php _e('Create Backup', 'mk-simple-backups'); ?></h3>
				<form action="<?php bloginfo("siteurl"); ?>/wp-admin/tools.php?page=mk-simple-backups&amp;action=createBackup" method="post">
					<ul class="options">
						<li class="theme"><label><input type="checkbox" name="options[]" value="theme" <?php if($settings["theme"] == true) echo 'checked="checked"'; ?>><strong><?php echo wp_get_theme();?></strong>, <?php _e('Active Theme', 'mk-simple-backups'); ?></label></li>
						<li class="db"><label><input type="checkbox" name="options[]" value="db" <?php if($settings["db"] == true) echo 'checked="checked"'; ?>><strong><?php _e('SQL-Dump', 'mk-simple-backups'); ?></strong>, <?php _e('Database', 'mk-simple-backups'); ?></label></li>
						<li class="plugins"><label><input type="checkbox" name="options[]" value="plugins" <?php if($settings["plugins"] == true) echo 'checked="checked"'; ?>><strong><?php _e('Plugins', 'mk-simple-backups'); ?></strong>, <?php _e('a <em>list</em> of currently active Plugins', 'mk-simple-backups'); ?></label></li>
						<li class="uploads"><label><input type="checkbox" name="options[]" value="uploads" <?php if($settings["upload"] == true) echo 'checked="checked"'; ?>><strong>/uploads</strong>, Uploads</label>
							<select name="upload_options">
								<option value="file" <?php if($settings["upload_type"] == "file") echo 'selected="selected"'; ?>><?php printf( __('All files within %s', 'mk-simple-backups'), $bkp->upload_dir["baseurl"]); ?></option>
								<option value="db" <?php if($settings["upload_type"] == "db") echo 'selected="selected"'; ?>><?php _e('Attachments from DB (post_type: attachment)', 'mk-simple-backups'); ?></option>
							</select></li>
						
					</ul>
					
					<p><input type="submit" value="<?php _e("create Backup", "mk-simple-backups");?>" class="button action"></p>
				</form>
				<p><hr /></p>
				<p><?php _e('Backup directory', 'mk-simple-backups'); ?>: <br />
					<em><?php echo $bkp->backup_dir?></em></p>
				<p><hr /></p>
				<h3><?php _e("Info", "mk-simple-backups") ?></h3>
				<h4><?php _e("What's going on?", "mk-simple-backups") ?></h4>
				<p class="readable"><strong><?php _e("Theme", "mk-simple-backups");?></strong>: <?php _e("The Theme Backup will create a ZIP Archive containing all files within the directory of your current active theme. If the active theme is a child-theme, the backup will detect this and include all files from the parent theme.", "mk-simple-backups")?></p>
				
				<p class="readable"><strong><?php _e("Database", "mk-simple-backups");?></strong>: <?php _e("The DB Backup creates an SQL Dump that can be used to restore your Database with all information contained. To restore the Database, you'll have to use a plugin like Adminer. Since many shared hostings don't support exec() by default, the SQL Dump is generated manually. It will scan for tables in the Database used by Wordpress, and then export all found tables. This includes custom tables created by plugins or your theme.", "mk-simple-backups")?></p>
				<p class="readable"><strong><?php _e("List of Plugins", "mk-simple-backups");?></strong>: <?php _e("This plugin will not back up your Plugins. This happens for the following reasons: a) They outdate quickly, making a backed up Version easily obsolete. b) There are huge Plugins out there, which would slow down Backups considerably. And c) Plugins must not include custom code and therefore can easily be re-downloaded in current or older Versions from the repository. This plugin simply generates a convenient plain-text list of currently active Plugins with their respective Version-Number and the Plugin-URI.", "mk-simple-backups")?></p>
				<p class="readable"><strong><?php _e("Uploads", "mk-simple-backups");?></strong>: <?php _e("The file-based Upload Backup will scan the whole Upload Directory (including subfolders) and create a ZIP Archive containing all files found. If there are many files in the upload directory, the script might time out. The DB based Upload Backup will fetch Attachments from the DB and create a ZIP Archive with found files. In the DB approach, the thumbnails and scaled versions are not included, since they can be regenerated afterwards easily. By ommitting those files, the DB approach saves space and script execution time. The DB based approach can be incomplete when plugins create files in the upload directory that arent included in the DB as attachments. There are plugins that do this.", "mk-simple-backups")?></p>
			
				
				<h4><?php _e("How long does it take?", "mk-simple-backups") ?></h4>
				<p class="readable"><?php _e("Depending on the amount of files and data, Backups can widely vary in size and time taken to create. The script attempts to increase maximum execution time for php scripts to allow larger Backups to be created. While a backup is being created, don't close your tab/browser and don't navigate to another page as that would cancel unfinished backups and may leave temporary files in the backup directory. If a complete Backup fails, you may want to try creating partial backups.", "mk-simple-backups")?></p>
				
				<h4><?php _e("My Backup has failed, why?", "mk-simple-backups") ?></h4>
				<p class="readable"><?php _e("Usually file-permissions are at fault if the plugins fails to create Backups. Use your FTP-Client to make sure writing permission on the Backup Directory (refer to the path displayed above) are set to writeable. If there's other problems, use the Plugin Page in the Plugin Repository to request support or patching.", "mk-simple-backups")?></p>
				
				<ul class="actions">
					<li class="test"><strong><?php _e('Blank File', 'mk-simple-backups'); ?></strong>, <?php _e('test writing permissions', 'mk-simple-backups'); ?><br />
					<a href="<?php bloginfo("siteurl"); ?>/wp-admin/tools.php?page=mk-simple-backups&amp;action=createtestfile"><?php _e('create Blank File', 'mk-simple-backups'); ?></a></li>
				</ul>
				
				<p><hr /></p>
				<p class="small">Plugin <a href="<?php echo $this_plugin_data["pluginURI"]?>" target="_blank"><?php echo $this_plugin_data["Name"] ?></a> v<?php echo $this_plugin_data["Version"]?></p>
				
				<?
			
			} else {
				?>
				<p class="error"><?php printf(__('Backup directory could not be created: %s', 'mk-simple-backups'), $bkp->backup_dir); ?></p>
				<?php
			}
			?>

		</div>
	
		<?php
	}

}

// will be called when the plugin deactivates
function mk_simple_backups_deactivate() {
	
	global $bkp;
	
	// delete option
	delete_option("mk-simple-backups-settings");
	
	// attempt to flush backup directory
	$bkp->flushBackupDir();
	
	// attempt to remove backup directory
	@rmdir($bkp->backup_dir);
	
}


?>