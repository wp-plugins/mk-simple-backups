<?php


/*
 *		backupHandler
 *		April 2014
 *		Author: Michael Kühni, michaelkuehni.ch
 */


class backupHandler
{
	
	public $backups_exist = false;
	public $backup_directory_exists = false;
	public $backup_dir = "wp-content/z_backups";
	public $backup_dir_weburl = "";
	public $upload_dir = "";
	private $sep = "-";
	
	function __construct() {
		
		// get weburl
		$this->backup_dir_weburl = get_bloginfo("siteurl") . "/" . $this->backup_dir;
		
		// get wp upload dir
		$this->upload_dir = wp_upload_dir();
		
		// add ABSPATH to backup_dir
		$this->backup_dir = ABSPATH . $this->backup_dir;
		
		// create backup directory if it doesnt exist
		if(!is_dir($this->backup_dir)) @wp_mkdir_p( $this->backup_dir);
		// check if backup directory exists
		if(is_dir($this->backup_dir)) $this->backup_directory_exists = true;
		
		// attempt to create htaccess which prevents directory browsing
		if(!is_file($this->backup_dir . "/.htaccess")) {
			@touch($this->backup_dir . "/.htaccess");
			if(is_file($this->backup_dir . "/.htaccess")) {
				
				$a = fopen($this->backup_dir . "/.htaccess", "w" );
				$s = fwrite($a, "# disable directory browsing
Options All -Indexes");
				fclose($a);
			}
		}
		
		// check if there are already backups
		$files_in_backup_dir = $this->getBackupList( array(".htaccess") );
		if(count($files_in_backup_dir ) > 0 && $files_in_backup_dir != false) $this->backups_exist = true;
		
		
	}
	
	
	/*
	 *		getBackupList()
	 *		get a list of files within the backup directory
	 *		
	 *		parameters:
	 *			$explude = array containing files to be excluded from the list
	 *		returns files found on success and false if the directory is empty
	 */
	function getBackupList( $exclude=array() ) {
		
		// files to be excluded from listing
		$exclude = array_merge(array(
			"..", "."
		), $exclude);
		
		$files = array();
		$tmp = scandir($this->backup_dir);
		
		foreach($tmp AS $e) {
			if(!in_array($e, $exclude) && !is_dir($this->backup_dir . "/" . $e)) {
				$filename = substr($e, strrchr( $e , "/" ));
				$files[$filename] = $e;
			}
		}
		
		if(count($files) == 0 && $files != false) return false;
		else return $files;
		
	}
	
	
	/*
	 *		createThemeBackup()
	 *		attempts to back up the active theme into a zip file
	 *		
	 *		expects no parameters
	 *		returns true/false depening on success of backup
	 */
	function createThemeBackup() {
		
		$active_theme = wp_get_theme();
		$active_theme_base_dir = get_stylesheet_directory();
		
		// scan template directory
		$files = $this->scanDirectory( $active_theme_base_dir );
		
		// check for parent theme
		if($active_theme->get("Template") != "") {
			
			// get parent directory & scan files
			$parent_theme_base_dir = get_template_directory();
			$parent_files = $this->scanDirectory($parent_theme_base_dir);
			
			// merge child & parent theme files
			$files = array_merge($files, $parent_files);
			
		}
		
		// get theme-folder name
		$theme_folder_name = substr($active_theme_base_dir, strrpos( $active_theme_base_dir , "/")+1);
		$backup_filename = $this->getBackupName("theme_" . $theme_folder_name, ".zip");
		$backup_destination = $this->backup_dir . "/" . $backup_filename;
		
		// create zip
		$s = $this->createZip( $backup_destination, $files);
		
		if($s) {
			$this->backups_exist = true;
			return $backup_filename;
		} else return false;
		
	}
	
	
	/*
	 *		createZip()
	 *		attempts to create a zip Archive
	 *		
	 *		@param $destination = path to the zip to be created
	 *		@param $files_to_add = array containing the files to be added
	 *		returns true/false depending on success of zip creation
	 */
	public function createZip( $destinaton, $files_to_add ) {
		
		if(is_file($destination)) {
			return false;
		}
		
		$zip = new ZipArchive();
		if($zip->open( $destinaton,false ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
			return false;
		}
		
		foreach($files_to_add as $file) {
			$zip->addFile($file,$file);
		}
		//debug
		//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
		
		//close the zip -- done!
		$zip->close();
		
		return true;
	}
	
	
	/*
	 *		createUploadBackup()
	 *		attempts to back up uploads into a zip file
	 *		
	 *		expects no parameters
	 *		returns true/false depening on success of backup
	 */
	function createUploadBackup() {
		
		// get uploads directory from wp
		$upload_dir = $this->upload_dir["basedir"];
		
		$files = $this->scanDirectory( $upload_dir );
		
		// get theme-folder name
		$backup_filename = $this->getBackupName("uploads" , ".zip");
		$backup_destination = $this->backup_dir . "/" . $backup_filename;
		
		// create zip
		$s = $this->createZip($backup_destination, $files);
		
		if($s) {
			$this->backups_exist = true;
			return $backup_filename;
		} else return false;
		
		
	}
	
	
	
	/*
	 *		scanDirectory()
	 *		scans a directory including it's subdirectories
	 *		
	 *		@param $path = string containing the path to be scanned
	 *		returns an array containing the files within the folder or false, if the path does not point to a directory
	 */
	function scanDirectory( $path ) {
		
		// create array for files and folders
		$tmp_files = array();
		$tmp_folders = array();
		
		// add base dir to $tmp_folders
		$tmp_folders[] = $path;
		
		// scan 
		if(is_dir($path)) {
			for($i = 0; $i <= count($tmp_folders); $i++) {
				
				$tmp_dir = $tmp_folders[$i];
			
				$tmp_content = @scandir($tmp_dir);
				if(!empty($tmp_content)) {
					foreach($tmp_content AS $e) {
				
						if($e != "." && $e != "..") {
							$tmp_src = $tmp_dir . "/" . $e;
				
							// check if its a folder or a file
							if(is_dir($tmp_src)) {
								$tmp_folders[] = $tmp_src;
							} else if(file_exists($tmp_src)) {
								$tmp_files[] = $tmp_src;
							}
						}
					}
				}			
			}
			
			return $tmp_files;
		} else return false;
		
		
		
	}
	
	
	
	/*
	 *		createDBBackup()
	 *		attempts to create a complete SQL Dump for Tables within the DB used by Wordpress
	 *		
	 *		expects no parameters
	 *		Returns true/false depending on succes with the backup
	 */
	function createDBBackup() {
		
		// access $wpdb object which is the favored way of interacting with wp-database atm
		// http://codex.wordpress.org/Class_Reference/wpdb
		global $wpdb;
		
		
		// start textstring which will contain all of our sql-dump finally
		$db_backup_string = "/*-------------------------------------
DB Backup " .  date("d.m.Y H:i") . " - " . DB_NAME . "
" . get_bloginfo("name") . "
-------------------------------------*/
";
	
		// fetch all Tables and save them into $tables
		$tables = array();
		$tmp = $wpdb->get_results( 'SHOW TABLES', "ARRAY_N" );
		foreach($tmp AS $tablename) {
			$tables[] = $tablename[0];
		}
	
	
		// cycle through tables
		// get CREATE info and CONTENT for all $tables
		foreach($tables AS $t) {
		
			// get the CREATE call
			$tmp = $wpdb->get_results( 'SHOW CREATE TABLE ' . $t, "ARRAY_N" );
			$create_s = $tmp[0][1];
		
			// get columns
			// unused, columns are in the CREATE info
			/*$columns = array();
			$tmp = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $t, "ARRAY_N" );
			foreach($tmp AS $t) { $columns[] = $t[0]; }*/
		
			// get data & prepare vars
			$content_s = "";
			$rows = $wpdb->get_results( 'SELECT * FROM `' . $t . '`', "ARRAY_N" );
		
			// cycle through data
			foreach($rows AS $r) {
			
				// $values will contain all columns
				$values = array();
			
				// cycle through columns
				foreach($r AS $c) {
				
					// add escape slashes and remove unnecessary bakcslashes in linebreak code
					$c = addslashes($c);
					$c = ereg_replace("\n","\\n",$c);
					$values[] = "'" . $c . "'";
				}
			
				// forge $values into SQL INSERT Statement and append this to $content_s
				$content_s .= "INSERT INTO '$t' VALUES(" . implode(", ", $values) . ")
";

			}
		
		
			// with some general structure, add table-specific backup to your 
			$db_backup_string .= '/* - - - - - - - - - - - - - - - -
begin ' . $t . '
- - - - - - - - - - - - - - - -*/
' . $create_s . '

/* content ' . $t . '*/
' . $content_s . '

/* - - - - - - - - - - - - - - - -
end ' . $t . '
- - - - - - - - - - - - - - - -*/

';
		}	
	
	
		// get filename from date and time parameters
		$backup_filename = $this->getBackupName("db_" . DB_NAME, ".sql");
	
		// create file
		$s = @touch($this->backup_dir . "/" . $backup_filename);
		
		
		if($s) {
			
			// file exists, set backups_exist to true
			$this->backups_exist = true;
			
			// write content
			file_put_contents($this->backup_dir . "/" . $backup_filename, $db_backup_string);
			
			if($s) return $backup_filename;
			else return false;
		}
		else return false;
		
	}
	
	
	
	/*
	 *		createTestFile()
	 *		creates an empty txt-File within the Backup Directory
	 *		
	 *		expects no parameters
	 *		Returns true/false depending on succes with file creation
	 */
	function createTestFile() {

		$backup_filename = $this->getBackupName("test", ".txt");
		
		// attempt to create file
		$s = @touch($this->backup_dir . "/" . $backup_filename);
		
		if($s) {
			$this->backups_exist = true;
			return $backup_filename;
		}
		else return false;
	}
	
	
	/*
	 *		getBackupName()
	 *		generates a backup Filename with date() elements and $prefix and $suffix
	 *		
	 *		parameters:
	 *			$prefix = string which will be added before the date-part, defaults to "bkp"
	 *			$suffix = string which will be added after the date-part, default to empty
	 *		Returns generated String
	 */
	public function getBackupName( $prefix="bkp", $suffix="") {
	
		$backup_filename = date("d" . $this->sep . "m" . $this->sep . "y" . $this->sep . "H" . $this->sep . "i" . $this->sep . "s");
		
		if(strlen($prefix) > 0) $backup_filename = $prefix . $this->sep . $backup_filename;
		if(strlen($suffix) > 0) $backup_filename = $backup_filename . $suffix;
		
		return $backup_filename;		
	}
	
	
	
	
	/*
	 *		flushBackupDir()
	 *		attempts to delete all files within the backup directory
	 *		
	 *		expects no parameters
	 *		return true on success, array with remaining filenames on error
	 */
	function flushBackupDir() {
				
		// only proceed, if Backups exist
		if($this->backups_exist) {
			
			// prepare vars
			$errors = array();
			$files = array();
			
			// get fresh list of files and cycle them
			$files = $this->getBackupList();
			
			$s = $this->removeFiles( $files );
			
			if($s) return true;
			else return false;
			
		}
		
	}
	
	/*
	 *		createUploadBackupByDB()
	 *		attempts to backup original media files by querying db for attachments
	 *
	 *		expects no parameters
	 *		returns backup name or false, depending on success of backup
	 */
	function createUploadBackupByDB() {
		
		global $wpdb;
		
		// get & define path & filenames
		$uri_prefix = $this->upload_dir["baseurl"];
		$upload_dir = $this->upload_dir["basedir"];
		$backup_name = $this->getBackupName("uploads_bydb", ".zip");
		
		// fetch attachments from DB
		$attachments = array();
		$tmp = $wpdb->get_results( "SELECT `guid`, `post_type` FROM `wp_posts` WHERE `post_type`='attachment'", "ARRAY_A" );
		foreach($tmp AS $row) {
			
			$file_path = $upload_dir . substr($row["guid"], strlen($uri_prefix));
			
			if(is_file($file_path)) $attachments[] = $file_path;
			
		}
		
		$s = $this->createZip( $this->backup_dir . "/" . $backup_name, $attachments );
		
		if($s) {
			$this->backups_exist = true;
			return $backup_name;
		}
		else return false;
		
	}
	
	/*
	 *		removeFiles( $files )
	 *		Removes files contained in $files, file-path will be prepended with $backup_dir to make sure only files in the bkp-dir are deleted
	 *
	 *		@param $files = array() containing files to be removed
	 *		returns true on success, $errors containing files not removed on failure
	 */
	function removeFiles( $files ) {
		
		foreach($files AS $name=>$src ) {
			
			// remove
			$s = unlink($this->backup_dir . "/" . $src);
			// if can't remove, add filename to errors-array
			if($s == false) $errors[] = $name;
		}
		
		// if there are errors (filenames that didn't get removed) return array with those
		if(count($errors) > 0) return $errors;
		else {
			$this->backups_exist = false;
			return true;
		}
		
	}
	
	
	
	

}



?>