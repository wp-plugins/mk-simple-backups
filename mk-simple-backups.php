<?

/**
 * Plugin Name: mk Simple Backups
 * Description: Allows you to create simple backups on a dedicated page nested in the "Tools" Menu.
 * Version: 0.1
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

	// register a spot in the Backend Nav Structure
	function register_backup_menuitems() {
		add_management_page( "Backup", "Backup", "manage_options", "test", "mkMainBackupDisplay", "" );
	}
	add_action( 'admin_menu', 'register_backup_menuitems' );
	
	// enqeue css
	wp_enqueue_style("mk-simple-backups-general", $plugin_dir_mksimplebackups . "/assets/css/general.css" );
	
	
	// instantiate class
	$bkp = new backupHandler();




	function mkMainBackupDisplay() {
	
		global $_GET;
		global $bkp;
		global $wpdb;
	
	
		$msg = array();
	
	
		?>
		<div class="wrap">
		
			<h2>Backup</h2>
			<?
		
		
			
			// run custom actions
			switch($_GET["action"]) {
			
				default:
					// nothing
					break;
				
				case "createtestfile":
					$s = $bkp->createTestFile();
					if($s != false) $msg[] = array("txt"=>"Testdatei " . $s . " erstellt");
					else $msg[] = array("txt"=>"Testdatei konnte nicht erstellt werden", "error"=>true);
					break;
				
				case "createdbbkp":
					$s = $bkp->createDBBackup();
					if($s != false) $msg[] = array("txt"=>"DB Backup " . $s . " erstellt");
					else $msg[] = array("txt"=>"DB Backup konnte nicht erstellt werden", "error"=>true);
					break;
			
				case "createthemebkp":
					$s = $bkp->createThemeBackup();
					if($s != false) $msg[] = array("txt"=>"Theme Backup " . $s . " erstellt");
					else $msg[] = array("txt"=>"Theme Backup konnte nicht erstellt werden", "error"=>true);
					break;
				
			
				case "createuploadbkp":
					$s = $bkp->createUploadBackup();
					if($s != false) $msg[] = array("txt"=>"Upload Backup " . $s . " erstellt");
					else $msg[] = array("txt"=>"Upload Backup konnte nicht erstellt werden", "error"=>true);
					break;
				
				
				
				
				case "flush";
					$s = $bkp->flushBackupDir();
					if($s) $msg[] = array("txt"=>"Dateien im Backupverzeichnis wurden gelöscht"); 
					else $msg[] = array("txt"=>"Nicht alle Dateien konnten gelöscht werden", "error" => true);
					break;

			}
		
			// echo msg's
			if(count($msg) > 0) {
				?>
				<div id="message" class="updated">
					<?
					foreach($msg AS $m) {
					
						if($m["error"] == true) $class = ' style="color:crimson;"';
						else unset($class);
					
						echo '<p' . $class . '>' . $m["txt"] . '</p>';
					}
					?>
				</div>
				<?
			}
			
			?>
		
		
			<?
			if($bkp->backups_exist) {
			
				$files = $bkp->getBackupList();
			
				?>
				<h3>Inhalt Backup-Verzeichnis (<?=count($files); ?>)</h3>
				<ul class="filelist">
				<?
			
				foreach($files AS $name=>$src) {
					
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
					
					$filesize = filesize( $bkp->backup_dir . "/" . $src);
				
					if($filesize/900000 > 1) $nice_filesize = round($filesize/1000000, 2) . " MB";
					else if ($filesize/1000 > 1) $nice_filesize = round($filesize/1000, 1) . " KB";
					else $nice_filesize = $filesize . " B";
				
					?>
					<li class="<?=$fileclass?>"><a href="<?=$bkp->backup_dir_weburl . "/" . $src ?>" target="_blank"><?=$name?></a>, <?=$nice_filesize; ?></li>
					<?
				}
				?>
				</ul>
				<p><strong>Hinweis</strong>: Da der SQL Dump Benutzernamen für diese Wordpress Installation enthält, sollte ein Backup sofort nach dem herunterladen vom Server entfernt werden. </p>
				<p><a href="<? bloginfo("siteurl"); ?>/wp-admin/tools.php?page=test&amp;action=flush" style="color:crimson;">Backup Verzeichnis leeren</a></p>
				<?
			
			} else {
				echo '<p>Momentan keine Backups auf dem Server vorhanden</p>';
			}
		

		
			?>
		
			<p><hr /></p>
		
			<? 
			if($bkp->backup_directory_exists == true) {

				?>
				<h3>Backup erstellen</h3>
				<ul class="actions">
					<li class="theme"><strong><?=wp_get_theme();?></strong>, Aktives Theme<br />
					<a href="<? bloginfo("siteurl"); ?>/wp-admin/tools.php?page=test&amp;action=createthemebkp">Theme Backup erstellen</a></li>
					<li class="uploads"><strong>/uploads</strong>, Alle Uploads im Verzeichnis wp-content<br />
					<a href="<? bloginfo("siteurl"); ?>/wp-admin/tools.php?page=test&amp;action=createuploadbkp">Upload Backup erstellen</a></li>
					<li class="db"><strong>SQL-Dump</strong>, Datenbank<br />
					<a href="<? bloginfo("siteurl"); ?>/wp-admin/tools.php?page=test&amp;action=createdbbkp">DB Backup erstellen</a></li>
					<li class="test"><strong>Leere Textdatei</strong>, Test für Schreibrechte<br />
					<a href="<? bloginfo("siteurl"); ?>/wp-admin/tools.php?page=test&amp;action=createtestfile">Testdatei erstellen</a></li>
				</ul>
				<p><hr /></p>
				<p>Backup Verzeichnis: <br />
					<em><?=$bkp->backup_dir?></em></p>
				<?
			
			} else echo '<p style="color:crimson;">Backup Verzeichnis konnte nicht erstellt werden. ' . $bkp->backup_dir . '</p>';  ?>

		</div>
	
		<?
	}



}


?>