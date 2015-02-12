<?php
$srcRoot = "src";
$buildRoot = "build";
  
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
