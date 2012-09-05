<?php

use \Woody\Event\EventInfo;
use \Woody\Event\EventFactory;
use \Woody\Event\EventDispatcher;

error_reporting(E_ALL | E_STRICT);

define('APP_ROOT_FOLDER', realpath(__DIR__).'\\');
define('APP_SOURCE_FOLDER', realpath(__DIR__.'\\source').'\\');

define('WOODY_INSTALLATION_FOLDER', str_replace('\\', '/', realpath(__DIR__.'\..\\woody')));
define('WOODY_SOURCE_FOLDER', WOODY_INSTALLATION_FOLDER.'/source');


require_once WOODY_INSTALLATION_FOLDER.'/lib/winbinder.php';
require_once WOODY_INSTALLATION_FOLDER.'/lib/fi/freeimage.inc.php';
require_once WOODY_SOURCE_FOLDER.'/Utils/Autoload/Autoloader.inc';

$woodyAutoloader = new \Utils\Autoload\Autoloader(WOODY_SOURCE_FOLDER.'/');
spl_autoload_register(array($woodyAutoloader, 'autoload'));

$appAutoloader = new \Utils\Autoload\Autoloader(APP_SOURCE_FOLDER);
spl_autoload_register(array($appAutoloader, 'autoload'));

function globalWinBinderEventHandler($windowID, $id, $controlID = 0, $type = 0, $property = 0) {
  $events = EventFactory::createEvent($eventInfo = new EventInfo($windowID, $id, $controlID, $type, $property));
  foreach($events as $event) {
    if($event != null) {
      //EventDispatcher::dispatchEvent($eventInfo, $event);
      $event->dispatch();
    }
  }
}

$app = new \BatteryMark\App\BatteryMarkApplication();
$app->start();