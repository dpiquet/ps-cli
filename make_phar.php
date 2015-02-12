<?php
$srcRoot = "src";
$buildRoot = "build";
  
$phar = new Phar($buildRoot . "/ps-cli.phar", 
	FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
	"ps-cli.phar");

$phar->buildFromDirectory(dirname(__FILE__) . '/src/');

$phar->setStub( $phar->createDefaultStub("boot_phar.php") );

echo "Generated $buildRoot/ps-cli.phar\n";

?>
