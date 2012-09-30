<?php

require_once './vendor/autoload.php';
require_once './vendor/ws/loewe/Woody/lib/winbinder.php';
require_once './vendor/ws/loewe/Woody/lib/fi/freeimage.inc.php';

use \ws\loewe\Utils\Autoload\Autoloader;
use \ws\loewe\Woody\Event\EventInfo;
use \ws\loewe\Woody\Event\EventFactory;
use \ws\loewe\Woody\Components\Component;

error_reporting(E_ALL | E_STRICT);


$autoloader = new Autoloader('./source/', 'php');

spl_autoload_register(array($autoloader, 'autoload'));

function globalWinBinderEventHandler($windowID, $id, $controlID = 0, $type = 0, $property = 0) {
  $eventInfo = new EventInfo($windowID, $id, Component::getComponentByID($controlID), $type, $property);
  
  foreach(EventFactory::createEvent($eventInfo) as $event) {
    if($event != null) {
      $event->dispatch();
    }
  }
}

$app = new ws\loewe\BattMan\App\BattManApplication();
$app->start();