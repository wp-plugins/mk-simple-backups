<?

/**
 * Plugin Name: mk Simple Backups
 * Plugin URI: http://wordpress.org/plugins/mk-simple-backups/
 * Description: Allows you to create simple backups on a dedicated page nested in the "Tools" Menu.
 * Version: 0.4.5
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
				
				case "flush";
					$s = $bkp->flushBackupDir();
					if($s) $msg[] = array("txt"=>"Files in the backup directory were deleted"); 
					else $msg[] = array("txt"=>"Some files could not be removed by the script. Check File-Permissions or delete the files manually.", "error" => true);
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
							'One file',
							'%s files',
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
					<li class="uploads"><strong>/uploads</strong>, <? _e('All uploads within wp-content/uploads', 'mk-simple-backups'); ?><br />
					<a href="<? bloginfo("siteurl"); ?>/wp-admin/tools.php?page=mk-simple-backups&amp;action=createuploadbkp"><? _e('create Upload Backup', 'mk-simple-backups'); ?></a></li>
					<li class="db"><strong><? _e('SQL-Dump', 'mk-simple-backups'); ?></strong>, <? _e('Database', 'mk-simple-backups'); ?><br />
					<a href="<? bloginfo("siteurl"); ?>/wp-admin/tools.php?page=mk-simple-backups&amp;action=createdbbkp"><? _e('create DB Backup', 'mk-simple-backups'); ?></a></li>
					<li class="test"><strong><? _e('Blank File', 'mk-simple-backups'); ?></strong>, <? _e('test writing permissions', 'mk-simple-backups'); ?><br />
					<a href="<? bloginfo("siteurl"); ?>/wp-admin/tools.php?page=mk-simple-backups&amp;action=createtestfile"><? _e('create Blank File', 'mk-simple-backups'); ?></a></li>
				</ul>
				<p><hr /></p>
				<p><? _e('Backup directory', 'mk-simple-backups'); ?>: <br />
					<em><?=$bkp->backup_dir?></em></p>
				<?
			
			} else {
				?>
				<p class="error"><? printf(__(' Backup directory could not be created: %s', 'mk-simple-backups'), $bkp->backup_dir); ?></p>
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