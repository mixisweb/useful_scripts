<?php
// set backup filename
$archiveName = 'backup_'.date('d_m_Y');

// define some basics
ini_set('max_execution_time', 600);
ini_set('memory_limit','1024M');
$rootPath =realpath(dirname(__FILE__));

// If a file with that name already exists delete it
if(file_exists($rootPath.'/'.$archiveName)){
	unlink($rootPath.'/'.$archiveName);
	echo '<p>Delete and create.</p>';
}

// initialize the ZIP archive
$zip = new ZipArchive;
$zip->open($archiveName, ZipArchive::CREATE);

// create recursive directory iterator
$files = new RecursiveIteratorIterator (new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);

// let's iterate
foreach ($files as $name => $file) {
	$filePath = $file->getRealPath();
	$zip->addFile($filePath);
}

// close the zip file
if (!$zip->close()) {
	echo '<p>There was a problem writing the ZIP archive.</p>';
} else {
	echo '<p>Successfully created the ZIP Archive!</p>';
}
?>
