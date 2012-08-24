
<?php

try {
  $phar = new Phar('BattMan.phar', 0, 'BattMan.phar');
  $phar->buildFromIterator(
      new RecursiveIteratorIterator(
       new RecursiveDirectoryIterator('D:\\workspace\\programming\\PHP\\BM')),
      'D:\\workspace\\programming\\PHP\\BM\\');



$phar->setStub('<?php
error_reporting(E_ALL | E_STRICT);

Phar::mapPhar(\'BattMan.phar\');

require_once \'phar://BattMan.phar/Woody/lib/winbinder.php\';
require_once \'phar://BattMan.phar/Woody/lib/fi/freeimage.inc.php\';
require_once \'phar://BattMan.phar/Woody/source/Utils/Autoload/Autoloader.inc\';

$woodyAutoloader = new \Utils\Autoload\Autoloader(\'phar://BattMan.phar/Woody/source/\');
spl_autoload_register(array($woodyAutoloader, \'autoload\'));

$appAutoloader = new \Utils\Autoload\Autoloader(\'phar://BattMan.phar/BatteryMark/source/\');
spl_autoload_register(array($appAutoloader, \'autoload\'));

function globalWinBinderEventHandler($windowID, $id, $controlID = 0, $type = 0, $property = 0) {
  \Woody\Event\EventFactory::createEvent($windowID, $id, $controlID, $type, $property);
}

$app = new \BatteryMark\App\BatteryMarkApplication();

$app->start();

__HALT_COMPILER();
');}

catch (Exception $e) {
    echo 'Write operations failed on phar: ', $e;
}

/*
<?php
  error_reporting(E_ALL | E_STRICT);

  require_once \'../Woody/lib/winbinder.php\';
  require_once \'../Woody/lib/fi/freeimage.inc.php\';
  require_once \'../Woody/source/Utils/Autoload/Autoloader.inc\';

  $woodyAutoloader = new \Utils\Autoload\Autoloader(\'../Woody/source/\');
  spl_autoload_register(array($woodyAutoloader, \'autoload\'));

  $appAutoloader = new \Utils\Autoload\Autoloader(\'./source/\');
  spl_autoload_register(array($appAutoloader, \'autoload\'));

  function globalWinBinderEventHandler($windowID, $id, $controlID = 0, $type = 0, $property = 0) {
    \Woody\Event\EventFactory::createEvent($windowID, $id, $controlID, $type, $property);
  }

  $app = new \BatteryMark\App\BatteryMarkApplication();

  $app->start();

  HALT_COMPILER(); ?>
 */