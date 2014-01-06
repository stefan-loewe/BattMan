<?php

namespace ws\loewe\BattMan\Model;

use \ws\loewe\Woody\Util\WinApi\WinApi;

class ObserverCollection extends \Stackable {

  public function __construct($coll) {
    $this->coll = $coll;
  }

  public function attach($view) {
    $this->coll = array_merge($this->coll, array($view));


  }

  public function detach($viewId) {
    //$this->coll[] = $viewId;
  }

  public function notice() {
    foreach($this->coll as $view) {
        var_dump('is null?');
        var_dump(\ws\loewe\BattMan\View\View::$instances);
        var_dump('is null?');
        $view->update();
    }
  }

  public function run() {}
}

class BatteryStateMonitor extends \Worker implements Model {

  public $state              = null;

  private $creationTime       = 0;

  private $timeOnBattery      = 0;

  const AC_OFFLINE            = 0;

  const AC_ONLINE             = 1;

  const NO_BATTERY            = 128;

  const BATTERY_STATE_UNKNOWN = 255;

  const BATTERY_CHARGING      = 8;

  const PERCENTAGE_UNKNOWN    = 255;

  const TIME_UNKNOWN          = -1;

  private $stop               = false;

  private $observers          = null;

  public function __construct($obs) {
    $this->state  = new \ws\loewe\BattMan\Model\SystemPowerStatus();
    WinApi::call('Kernel32::GetSystemPowerStatus', array($this->state));

    $this->observers = $obs;
  }

  public function attach($observer) {
    $this->observers = array_merge($this->observers, array($observer));
  }

  public function detach($observer) {
    $this->observers->detach($observer);
  }

  public function notice() {
    var_dump(count($this->observers));
    foreach($this->observers as $observer) {
      $observer->update($this);
    }
  }

  public function run() {
    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors', true);

    require APP_INSTALL_DIR.'./vendor/autoload.php';
    require APP_INSTALL_DIR.'./vendor/ws/loewe/Woody/lib/winbinder.php';
    //require APP_INSTALL_DIR.'./vendor/ws/loewe/Woody/lib/fi/freeimage.inc.php';

    $autoloaderBattMan = new \ws\loewe\Utils\Autoload\Autoloader(APP_INSTALL_DIR.'/source/', 'php');
    spl_autoload_register(array($autoloaderBattMan, 'autoload'));
    $autoloaderWoody = new \ws\loewe\Utils\Autoload\Autoloader(APP_INSTALL_DIR.'/vendor/ws/loewe/Woody/source/', 'php');
    spl_autoload_register(array($autoloaderWoody, 'autoload'));
    $autoloaderUtils = new \ws\loewe\Utils\Autoload\Autoloader(APP_INSTALL_DIR.'/vendor/ws/loewe/Utils/src/', 'php');
    spl_autoload_register(array($autoloaderUtils, 'autoload'));

    $this->timeOnBattery = 0;
    $this->creationTime  = time();

    while(!$this->stop) {
      $this->state  = new \ws\loewe\BattMan\Model\SystemPowerStatus();
      WinApi::call('Kernel32::GetSystemPowerStatus', array($this->state));
      $this->notice();
      var_dump('running');
      sleep(1);
    }
  }

  public function stop() {
    $this->stop = true;
  }

  public function getPowerStatus() {
    if($this->state->ACLineStatus === self::AC_OFFLINE) {
      return 'no';
    }

    else if($this->state->ACLineStatus === self::AC_ONLINE) {
      return 'yes';
    }

    return 'unknown';
  }

  public function getBatteryStatus() {
    if($this->state->BatteryFlag === self::BATTERY_STATE_UNKNOWN) {
      return 'unknown';
    }

    else if($this->state->BatteryFlag === self::NO_BATTERY) {
      return 'no battery available';
    }

    else if(($this->state->BatteryFlag & self::BATTERY_CHARGING) === self::BATTERY_CHARGING) {
      return 'yes';
    }

    return 'no';
  }

  public function getPercentRemaining() {
    if($this->state->BatteryLifePercent === self::PERCENTAGE_UNKNOWN) {
      return 'unknown';
    }

    return $this->state->BatteryLifePercent;
  }

  public function getTimeRemaining() {
    if($this->state->BatteryLifeTime === self::TIME_UNKNOWN) {
      return 0;
    }

    return $this->state->BatteryLifeTime;
  }

  public function getTimeOnBattery() {
    return $this->timeOnBattery;
  }

  public function getCreationTime() {
    return $this->creationTime;
  }

  public function __toString() {
    $result = PHP_EOL.'connected to power: '.$this->getPowerStatus()
      .PHP_EOL.'battery available:  '.$this->getBatteryStatus()
      .PHP_EOL.'remaining battery:  '.$this->getPercentRemaining()
      .PHP_EOL.'remaining runtime:  '.$this->getTimeRemaining()
      .PHP_EOL.'time on battery:    '.$this->getTimeOnBattery()
      .PHP_EOL.'time started:       '.$this->getCreationTime();

    return $result;
  }
}
