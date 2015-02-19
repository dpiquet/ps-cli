<?php
$srcRoot = "src";
$buildRoot = "build";

#delete previously generated phar if exists
if (file_exists($buildRoot.'/ps-cli.phar')) {
	unlink ($buildRoot.'/ps-cli.phar') or die ("Error, could not delete previously created phar ! \n");
}


$phar = new Phar($buildRoot . '/ps-cli.phar', 0, 'ps-cli.phar');

$phar->buildFromDirectory(dirname(__FILE__) . '/src/');

//$phar->setStub( $phar->createDefaultStub("boot_phar.php") );

$phar->setStub(<<<EOS
#!/usr/bin/env php
<?php
Phar::mapPhar();
include 'phar://ps-cli.phar/boot_phar.php';
__HALT_COMPILER();
?>
EOS
);

echo "Generated $buildRoot/ps-cli.phar\n";

?>
