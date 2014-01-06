<?php

use \ws\loewe\Utils\Autoload\Autoloader;
use \ws\loewe\Woody\Event\EventInfo;
use \ws\loewe\Woody\Event\EventFactory;
use \ws\loewe\Woody\Components\Component;

error_reporting(E_ALL | E_STRICT);

const APP_INSTALL_DIR = __DIR__;

chdir(APP_INSTALL_DIR);

require_once APP_INSTALL_DIR.'./vendor/autoload.php';
require_once APP_INSTALL_DIR.'./vendor/ws/loewe/Woody/lib/winbinder.php';
require_once APP_INSTALL_DIR.'./vendor/ws/loewe/Woody/lib/fi/freeimage.inc.php';

$autoloader = new Autoloader(APP_INSTALL_DIR.'./source/', 'php');
spl_autoload_register(array($autoloader, 'autoload'));

function globalWinBinderEventHandler($windowID, $id, $controlID = 0, $type = 0, $property = 0) {
  $eventInfo = new EventInfo($windowID, $id, Component::getComponentByID($controlID), $type, $property);

  foreach(EventFactory::createEvent($eventInfo) as $event) {
    if($event != null) {
      $event->dispatch();
    }
  }
}


$batteryMonitor = new \ws\loewe\BattMan\Model\BatteryStateMonitor(array());
$batteryMonitor->start();

$app = new ws\loewe\BattMan\App\BattManApplication($batteryMonitor);



$app->start();

$batteryMonitor->stop();