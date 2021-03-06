<?php
date_default_timezone_set('Europe/Kiev');
error_reporting(E_ALL);
ini_set('max_execution_time', 3000);
$backup_folder = realpath(dirname(__FILE__));
$log_file = fopen($backup_folder.'/backup_logs.txt', 'a');
$str='';

/* Enter Backup folder information */
$sitename='Enter your site name';
$zip_backup_filename = $backup_folder.'/'.$sitename.'_'.date('d_m_Y').'.zip';
if (file_exists($zip_backup_filename)) unlink($zip_backup_filename);

/* Enter MySQL Database information */
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASSWORD', '');
define('DB_HOST', '');
define('DB_CHARSET', 'utf8');
define("TABLES", '*');
$sql_backup_filename = $backup_folder.'/'.$sitename.'_'.date('d_m_Y').'.sql';
if (file_exists($sql_backup_filename)) unlink($sql_backup_filename);

/* FTP Server Configuration */
$ftp_server = '';
$ftp_file_path='';
$ftp_user='';
$ftp_pass='';

/* Create MySQL database backup */
$backupDatabase = new Backup_Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($sql_text=$backupDatabase->backupTables(TABLES)){
	$str.=date('d-m-Y H:i:s').' Create MySQL database backup '.$sql_backup_filename.PHP_EOL;
}else{
	$str.=date('d-m-Y H:i:s').' Could not create MySQL database backup '.$sql_backup_filename.PHP_EOL;
}
$sql_backup_filename_open = fopen($sql_backup_filename, 'w');
fwrite($sql_backup_filename_open, $sql_text);
fclose($sql_backup_filename_open);

/* Create zip archive */
$zip = new FlxZipArchive;
$res = $zip->open($zip_backup_filename, ZipArchive::CREATE);
if($res ===TRUE) {
	$zip->addDir($backup_folder, basename($backup_folder));
	$zip->close();
	$str.=date('d-m-Y H:i:s').' Create zip archive '.$zip_backup_filename.PHP_EOL;
}else{
	$str.=date('d-m-Y H:i:s').' Could not create a zip archive '.$zip_backup_filename.PHP_EOL;
}

/* FTP upload backup zip archive and MySQL database  */
$ftp_conn = ftp_connect($ftp_server);
if ($ftp_conn!==FALSE){
	$login = ftp_login($ftp_conn, $ftp_user, $ftp_pass);
	if (ftp_put($ftp_conn, $ftp_file_path.$sitename.'_'.date('d_m_Y').'.zip', $zip_backup_filename, FTP_BINARY)){
		$str.=date('d-m-Y H:i:s').' Successfully uploaded '.$zip_backup_filename.PHP_EOL;
		unlink($zip_backup_filename);
		unlink($sql_backup_filename);
		$files = ftp_nlist($ftp_conn, "-t $ftp_file_path");
		$c=count($files);
		for ($i=$c-1;$i>4;$i--){
			ftp_delete($ftp_conn, $files[$i]);
		}
	}else{
		$str.=date('d-m-Y H:i:s').' Error uploading '.$zip_backup_filename.PHP_EOL;
	}
	ftp_close($ftp_conn);
}else{
	$str.=date('d-m-Y H:i:s').' Could not connect to '.$ftp_server.PHP_EOL;
}

/* Class for recursive zip archiving folder */
class FlxZipArchive extends ZipArchive {
    public function addDir($location, $name) {
        $this->addEmptyDir($name);
        $this->addDirDo($location, $name);
    }
    private function addDirDo($location, $name) {
        $name .= '/';
        $location .= '/';
        $dir = opendir ($location);
        while ($file = readdir($dir)){
            if ($file == '.' || $file == '..') continue;
            $do = (filetype( $location . $file) == 'dir') ? 'addDir' : 'addFile';
            $this->$do($location . $file, $name . $file);
        }
    }
}

/* Class for dackup MySQL database */
class Backup_Database {
	var $host = '';
	var $username = '';
	var $passwd = '';
	var $dbName = '';
	var $charset = '';
	function __construct($host, $username, $passwd, $dbName, $charset = 'utf8'){
		$this->host     = $host;
		$this->username = $username;
		$this->passwd   = $passwd;
		$this->dbName   = $dbName;
		$this->charset  = $charset;

		$this->initializeDatabase();
	}
	protected function initializeDatabase(){
		$db = mysql_connect($this->host, $this->username, $this->passwd);
		mysql_select_db($this->dbName, $db);
		if (! mysql_set_charset ($this->charset, $db)){mysql_query('SET NAMES '.$this->charset);}
	}
	public function backupTables($tables = '*'){
		try{
			$tables = array();
			$result = mysql_query('SHOW TABLES');
			while($row = mysql_fetch_row($result)){
				$tables[] = $row[0];
			}
			$sql = 'CREATE DATABASE IF NOT EXISTS '.$this->dbName.";\n\n";
			$sql .= 'USE '.$this->dbName.";\n\n";
			foreach($tables as $table){
				$result = mysql_query('SELECT * FROM '.$table);
				$numFields = mysql_num_fields($result);
				$sql .= 'DROP TABLE IF EXISTS '.$table.';';
				$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
				$sql.= "\n\n".$row2[1].";\n\n";
				for ($i = 0; $i < $numFields; $i++){
					while($row = mysql_fetch_row($result)){
						$sql .= 'INSERT INTO '.$table.' VALUES(';
						for($j=0; $j<$numFields; $j++){
							$row[$j] = addslashes($row[$j]);
							$row[$j] = str_replace("\n","\\n",$row[$j]);
							if (isset($row[$j])){
								$sql .= '"'.$row[$j].'"' ;
							}else{
								$sql.= '""';
							}
							if ($j < ($numFields-1)) $sql .= ',';
						}
						$sql.= ");\n";
					}
				}
				$sql.="\n\n\n";
			}
		}
		catch (Exception $e){
			var_dump($e->getMessage());
			return false;
		}
		return $sql;
	}
}
fwrite($log_file, $str);
fclose($log_file);
?>
