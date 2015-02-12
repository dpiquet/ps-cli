<?php
$srcRoot = "src";
$buildRoot = "build";
  
$phar = new Phar($buildRoot . "/ps-cli.phar", 
	FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
	"ps-cli.phar");



# include all files in the phar archive
function load_dir_files($path) {
	$dir = opendir($path);

	global $srcRoot;

	while(false !== ($cur = readdir($dir))) {

		$key = str_replace( './' . $srcRoot . '/', '', $path . '/' . $cur);

		if($cur == '.' or $cur == '..') {
			continue;
		}

		if(is_dir($path . '/' . $cur)) {
			echo "goin in $path/$cur directory\n";
			load_dir_files($path.'/'.$cur);
		}
		else {
			echo "Adding $path/$cur as $key to phar archive\n";
			$phar[$key] = file_get_contents($path . '/' . $cur);
		}
	}

	return;
}

$phar->startBuffering();

//we must find where we are before loading files
//load_dir_files('./' . $srcRoot);
//
$phar->buildFromDirectory(dirname(__FILE__) . '/src/');

//$phar->setStub( $phar->createDefaultStub("boot_phar.php") );

$phar->setStub( <<<EOF
#!/usr/bin/env php
<?php
Phar::mapPhar();
include 'phar://ps-cli.phar/boot_phar.php';
__HALT_COMPILER();
?>
EOF
);

$phar->stopBuffering();

?>
