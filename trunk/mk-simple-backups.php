<?

/**
 * Plugin Name: mk Simple Backups
 * Plugin URI: http://wordpress.org/plugins/mk-simple-backups/
 * Description: Allows you to create simple backups on a dedicated page nested in the "Tools" Menu.
 * Version: 0.5
 * Author: Michael Kühni
 * Author URI: http://michaelkuehni.ch
 * License: GPL2
 */


// backend only
if(is_admin()) {
	
	
	
	
	// get plugin directory
	$plugin_dir_mksimplebackups = plugin_dir_url( __FILE__ );

	// get BackupHandler Class
	require_once("class.backuphandler.php");
	// instantiate class
	$bkp = new backupHandler();

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
	
	



	function mkMainBackupDisplay() {
	
		global $_GET;
		global $bkp;
		global $wpdb;
	
	
		$msg = array();
	
	
		?>
		<div class="wrap">
		
			<h2><? _e( 'Backup', 'mk-simple-backups' );?></h2>
			<?
		
		
			
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
				
				case "createdbbkp":
					$s = $bkp->createDBBackup();
					if($s != false) $msg[] = array("txt"=>sprintf(__( 'DB Backup %s created', 'mk-simple-backups' ), $s ));
					else $msg[] = array("txt"=>__( 'DB Backup could not be created', 'mk-simple-backups' ), "error"=>true);
					break;
			
				case "createthemebkp":
					$s = $bkp->createThemeBackup();
					if($s != false) $msg[] = array("txt"=>sprintf(__( 'Theme Backup %s created', 'mk-simple-backups' ), $s ));
					else $msg[] = array("txt"=>__( 'Theme Backup could not be created', 'mk-simple-backups' ), "error"=>true);
					break;
				
				case "createuploadbkp":
					$s = $bkp->createUploadBackup();
					if($s != false) $msg[] = array("txt"=>sprintf(__( 'Upload Backup %s created', 'mk-simple-backups' ), $s ));
					else $msg[] = array("txt"=>__( 'Upload Backup could not be created', 'mk-simple-backups' ), "error"=>true);
					break;
				
				case "createCompleteBackup":
					$s1 = $bkp->createDBBackup();
					if($s1 == false) $msg = array( "txt"=>__("DB Backup failed, complete backup aborted", "mk-simple-backups"), "error"=>true );
					$s2 = $bkp->createThemeBackup();
					if($s2 == false) $msg = array( "txt"=>__("Theme Backup failed, complete backup aborted", "mk-simple-backups"), "error"=>true );
					$s3 = $bkp->createUploadBackupByDB();
					if($s3 == false) $msg = array( "txt"=>__("Media Backup failed, complete backup aborted", "mk-simple-backups"), "error"=>true );
					
					// currently no zip will be done
					if($s1 != false && $s2 != false && $s3 != false) {
						
						// create array with backups
						$bkp_files = array( 
							$s1=>$bkp->backup_dir . "/" . $s1, 
							$s2=>$bkp->backup_dir . "/" . $s2, 
							$s3=>$bkp->backup_dir . "/" . $s3 );
						
						// create zip
						$zip_path = $bkp->backup_dir . "/" . $bkp->getBackupName("complete", ".zip" );
						$s = $bkp->createZip( $zip_path , $bkp_files );
						
						// check if zip was created successfully
						if($s) {
							$bkp->removeFiles( array($s1, $s2, $s3) );
							$bkp->backups_exist = true;
							$msg[] = array( "txt"=>__("Complete Backup successfully created", "mk-simple-backups"));
						} else {
							$msg[] = array( "txt"=>__("Complete Backup could not be created", "mk-simple-backups"));
						}
						
						
						
					}
					break;
				
				case "createuploadbkpbydb":
					$s = $bkp->createUploadBackupByDB();
					if($s != false) $msg[] = array("txt"=> sprintf(__("Backup %s created", "mk-simple-backups"), $s));
					else $msg[] = array("txt"=>__("Media Backup by DB could not be created", "mk-simple-backups"), "error"=>true);
					break;
				
				case "flush";
					$s = $bkp->flushBackupDir();
					if($s) $msg[] = array("txt"=>__("Files in the backup directory were deleted", "mk-simple-backups")); 
					else $msg[] = array("txt"=>__("Some files could not be removed by the script. Check File-Permissions or delete the files manually.", "mk-simple-backups"), "error" => true);
					break;

			}
		
		
			// echo msg's
			if(count($msg) > 0) {
				?>
				<div id="message" class="updated">
					<?
					foreach($msg AS $m) {
					
						if($m["error"] == true) $class = ' class="error"';
						else unset($class);
					
						?><p <?=$class?>><?=$m["txt"] ?></p><?
					}
					?>
				</div>
				<?
			}
			
			?>
		
		
			<?
			// check if backups exists
			if($bkp->backups_exist) {
				
				// get list of files in bkp dir
				$files = $bkp->getBackupList( array(".htaccess") );
			
				?>
				<h3><? printf(
						_n(
							__('One file', "mk-simple-backups"),
							__('%s files', "mk-simple-backups"),
							count($files),
							'mk-simple-backups'
						),count($files));  ?></h3>
				<ul class="filelist">
				<?
				
				// cycle through files in bkp dir
				foreach($files AS $name=>$src) {
					
					// determine type of file by the first 2 characters
					$begin = substr($name, 0, 2);
					
					switch($begin)
					{
						default: 
							$fileclass = "";
							break;
						case "up":
							$fileclass = "uploads";
							break;
						
						case "db":
							$fileclass = "db";
							break;
						
						case "co":
							$fileclass = "archive";
							break;
						
						case "te":
							$fileclass = "empty";
							break;
						
						case "th":
							$fileclass="theme";
							break;
					}
					
					// get filesize
					$filesize = filesize( $bkp->backup_dir . "/" . $src);
					
					// format filesize
					if($filesize/900000 > 1) $nice_filesize = round($filesize/1000000, 2) . " MB";
					else if ($filesize/1000 > 1) $nice_filesize = round($filesize/1000, 1) . " KB";
					else $nice_filesize = $filesize . " B";
				
					?>
					<li class="<?=$fileclass?>"><a href="<?=$bkp->backup_dir_weburl . "/" . $src ?>" target="_blank"><?=$name?></a>, <?=$nice_filesize; ?></li>
					<?
				}
				?>
				</ul>
				<p><strong><? _e('Important', 'mk-simple-backups'); ?></strong>: <? _e('Since the sql-dump-file will include usernames for your wp-installation, make sure to remove it after downloading the backup.', 'mk-simple-backups'); ?></p>
				<p><a href="<? bloginfo("siteurl"); ?>/wp-admin/tools.php?page=mk-simple-backups&amp;action=flush" style="color:crimson;"><? _e('Delete files in Backup directory.', 'mk-simple-backups'); ?></a></p>
				<?
			
			} else {
				?><p><? _e('Currently no backups stored on the server', 'mk-simple-backups'); ?></p><?
			}
		

		
			?>
		
			<p><hr /></p>
		
			<? 
			if($bkp->backup_directory_exists == true) {

				?>
				<h3><? _e('Create Backup', 'mk-simple-backups'); ?></h3>
				<ul class="actions">
					<li class="theme"><strong><?=wp_get_theme();?></strong>, <? _e('Active Theme', 'mk-simple-backups'); ?><br />
					<a href="<? bloginfo("siteurl"); ?>/wp-admin/tools.php?page=mk-simple-backups&amp;action=createthemebkp"><? _e('create Theme Backup', 'mk-simple-backups'); ?></a></li>
					<li class="uploads"><strong>/uploads</strong>, <? printf( __('All uploads within %s', 'mk-simple-backups'), $bkp->upload_dir["baseurl"]); ?><br />
					<a href="<? bloginfo("siteurl"); ?>/wp-admin/tools.php?page=mk-simple-backups&amp;action=createuploadbkp"><? _e('create Upload Backup', 'mk-simple-backups'); ?></a></li>
					<li class="uploads"><strong>/uploads</strong>, <? _e('Backup Attachments from DB (post_type: attachment)', 'mk-simple-backups'); ?><br />
					<a href="<? bloginfo("siteurl"); ?>/wp-admin/tools.php?page=mk-simple-backups&amp;action=createuploadbkpbydb"><? _e('create Upload Backup', 'mk-simple-backups'); ?></a></li>
					<li class="db"><strong><? _e('SQL-Dump', 'mk-simple-backups'); ?></strong>, <? _e('Database', 'mk-simple-backups'); ?><br />
					<a href="<? bloginfo("siteurl"); ?>/wp-admin/tools.php?page=mk-simple-backups&amp;action=createdbbkp"><? _e('create DB Backup', 'mk-simple-backups'); ?></a></li>
					<li class="test"><strong><? _e('Blank File', 'mk-simple-backups'); ?></strong>, <? _e('test writing permissions', 'mk-simple-backups'); ?><br />
					<a href="<? bloginfo("siteurl"); ?>/wp-admin/tools.php?page=mk-simple-backups&amp;action=createtestfile"><? _e('create Blank File', 'mk-simple-backups'); ?></a></li>
					<li class="allinone">
						<strong><? _e("All-in-One", "mk-simple-backups") ?></strong>, <? _e("attempt to create all Backup-Types at once", "mk-simple-backups") ?><br />
						<em><? _e("script might time out", "mk-simple-backups") ?></em><br />
						<a href="<? bloginfo("siteurl"); ?>/wp-admin/tools.php?page=mk-simple-backups&amp;action=createCompleteBackup"><? _e("create DB, Media & Theme Backup", "mk-simple-backups")?></a>
					</li>
				</ul>
				<p><hr /></p>
				<p><? _e('Backup directory', 'mk-simple-backups'); ?>: <br />
					<em><?=$bkp->backup_dir?></em></p>
				<p><hr /></p>
				<h4><? _e("What's going on?", "mk-simple-backups") ?></h4>
				<p class="readable"><strong><? _e("Theme Backup", "mk-simple-backups");?></strong>: <? _e("The Theme Backup will create a ZIP Archive containing all files within the directory of your current active theme. If the active theme is a child-theme, the backup will detect this and include all files from the parent theme.", "mk-simple-backups")?></p>
				
				<p class="readable"><strong><? _e("DB Backup", "mk-simple-backups");?></strong>: <? _e("The DB Backup creates an SQL Dump that can be used to restore your Database with all information contained. To restore the Database, you'll have to use a plugin like Adminer. Since many shared hostings don't support exec() by default, the SQL Dump is generated manually. It will scan for tables in the Database used by Wordpress, and then export all found tables. This includes custom tables created by plugins or your theme.", "mk-simple-backups")?></p>
				
				<p class="readable"><strong><? _e("Upload Backups", "mk-simple-backups");?></strong>: <? _e("The file-based Upload Backup will scan the whole Upload Directory (including subfolders) and create a ZIP Archive containing all files found. If there are many files in the upload directory, the script might time out. The DB based Upload Backup will fetch Attachments from the DB and create a ZIP Archive with found files. In the DB approach, the thumbnails and scaled versions are not included, since they can be regenerated afterwards easily. By ommitting those files, the DB approach saves space and script execution time. The DB based approach can be incomplete when plugins create files in the upload directory that arent included in the DB as attachments. There are plugins that do this.", "mk-simple-backups")?></p>
				
				<p class="readable"><strong><? _e("Complete Backup", "mk-simple-backups");?></strong>: <? _e("The complete Backup will automatically perform all single Backup Tasks and create a ZIP Archive containing the three resulting files. This can take some time, if performed within a big Wordpress installation. The complete includes the DB approach for Uploads.", "mk-simple-backups")?></p>
				
				<h4><? _e("How long does it take?", "mk-simple-backups") ?></h4>
				<p class="readable"><? _e("Depending on the amount of files and data, Backups can widely vary in size and time taken to create. The script attempts to increase maximum execution time for php scripts to allow larger Backups to be created. While a backup is being created, don't close your tab/browser and don't navigate to another page as that would cancel unfinished backups and may leave temporary files in the backup directory.", "mk-simple-backups")?></p>
				
				<h4><? _e("My Backup has failed, why?", "mk-simple-backups") ?></h4>
				<p class="readable"><? _e("Usually file-permissions are at fault if the plugins fails to create Backups. Use your FTP-Client to make sure writing permission on the Backup Directory (refer to the path displayed above) are set to writeable. If there's other problems, use the Plugin Page in the Plugin Repository to request support or patching.")?></p>
				<?
			
			} else {
				?>
				<p class="error"><? printf(__('Backup directory could not be created: %s', 'mk-simple-backups'), $bkp->backup_dir); ?></p>
				<?
			}
			?>

		</div>
	
		<?
	}

}

// will be called when the plugin deactivates
function mk_simple_backups_deactivate() {
	
	global $bkp;
	
	// attempt to flush backup directory
	$bkp->flushBackupDir();
	
	// attempt to remove backup directory
	@rmdir($bkp->backup_dir);
	
}


?>