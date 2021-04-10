<?php

function delTree($dir) {
   $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
      (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
  }



try{
    delTree(__DIR__."/extracted");
}
catch(Error){}
const PHAR = 'phar.phar';
@unlink(PHAR);
$phar = new Phar(PHAR, 0, PHAR);
// add all files in the project
#$phar->buildFromDirectory(__DIR__ . '/vendor', '/^.*(src|lib|psr|autoload|composer).*\.(php|json)$/');
$phar->buildFromDirectory(__DIR__);
#$phar->setStub($phar->createDefaultStub('main.php', 'main.php'));
$phar->setStub(file_get_contents("main.php"));

$phar->extractTo(__DIR__."/extracted");
?>
