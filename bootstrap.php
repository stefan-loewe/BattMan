<?php

error_reporting(E_ALL | E_STRICT);

define('WOODY_INSTALLATION_FOLDER', str_replace('\\', '/', realpath(__DIR__.'\..\\woody')));
define('WOODY_SOURCE_FOLDER', WOODY_INSTALLATION_FOLDER.'/source');

// buffer file for the built-in-web server
define('WEB_SERVER_BUFFER', 'buffer.html');

require_once WOODY_INSTALLATION_FOLDER.'/lib/winbinder.php';
require_once WOODY_INSTALLATION_FOLDER.'/lib/fi/freeimage.inc.php';
require_once WOODY_SOURCE_FOLDER.'/Utils/Autoload/Autoloader.inc';


/*$win = wb_create_window(
      null, AppWindow, 'test', 100,
      10, 300, 200, WBC_NOTIFY, WBC_DBLCLICK | WBC_GETFOCUS);
$frm = wb_create_control($win, Frame, '', 0, 0, 250, 150, WBC_NOTIFY, WBC_DBLCLICK | WBC_GETFOCUS);
$scb1 = wb_create_control($win, ScrollBar, 0, 0, 0, 20, 100, WBC_NOTIFY, WBC_DBLCLICK | WBC_GETFOCUS);
$scb2 = wb_create_control($frm, ScrollBar, 0, 0, 0, 100, 20, WBC_NOTIFY, WBC_DBLCLICK | WBC_GETFOCUS);

function main() {
  var_dump('$event');
}

wb_set_handler($win, "main");
wb_set_handler($frm, "main");
wb_main_loop();
die;
*/

$woodyAutoloader = new \Utils\Autoload\Autoloader(WOODY_SOURCE_FOLDER.'/');
spl_autoload_register(array($woodyAutoloader, 'autoload'));

define('APP_ROOT_FOLDER', realpath(__DIR__).'\\');
define('APP_SOURCE_FOLDER', realpath(__DIR__.'\\source').'\\');
$appAutoloader = new \Utils\Autoload\Autoloader(APP_SOURCE_FOLDER);
spl_autoload_register(array($appAutoloader, 'autoload'));

function globalWinBinderEventHandler($windowID, $id, $controlID = 0, $type = 0, $property = 0) {
  //var_dump(date('H:i:s').': calling globalWinBinderEventHandler in '.__FILE__.' at line '.__LINE__);
  //var_dump($windowID.', '.$id.', '.$controlID.', '.$type.', '.$property);

  \Woody\Event\EventFactory::createEvent($windowID, $id, $controlID, $type, $property);
}

$callback = function($errno, $errstr, $errfile, $errline) {
  $errorException = new \ErrorException($errstr, 0, $errno, $errfile, $errline);

  if(strpos($errstr, 'wbIsWBObj:') === 0) {
    throw new Woody\WinBinderErrorException(
      'Error when using WinBinder object - original error message was "'.$errstr.'"',
      0,
      $errorException);
  }
  else {
    throw $errorException;
  }
};

$app = new \BatteryMark\App\BatteryMarkApplication();

$app->start();