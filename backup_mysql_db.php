<?php 
error_reporting(E_ALL);

define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASSWORD', '');
define('DB_HOST', '');
define('DB_CHARSET', 'utf8');
define("TABLES", '*');

header("Content-type: application/octet-stream");
header('Content-Disposition: attachment; filename=db_'.DB_NAME.'.sql');

$backupDatabase = new Backup_Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$status = $backupDatabase->backupTables(TABLES) ? 'OK' : 'Error';

/**
 * The Backup_Database class
 */
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
		echo $sql;
	}
}
?>
