<?php

namespace ws\loewe\BattMan\Model;

use \ws\loewe\Woody\Util\WinApi\WinApi;

class BatteryState implements Model {

  private $state              = null;

  private $creationTime       = 0;

  private $timeOnBattery      = 0;

  const AC_OFFLINE            = 0;

  const AC_ONLINE             = 1;

  const NO_BATTERY            = 128;

  const BATTERY_STATE_UNKNOWN = 255;

  const BATTERY_CHARGING      = 8;

  const PERCENTAGE_UNKNOWN    = 255;

  const TIME_UNKNOWN          = -1;

  public function __construct(self $previousStatus = null) {
    $this->initialize();

    $this->creationTime = time();

    if($previousStatus === null) {
      $this->timeOnBattery = 0;
    }
    else {
      $this->timeOnBattery = $previousStatus->timeOnBattery;

      if($this->state->ACLineStatus === self::AC_OFFLINE) {
        $this->timeOnBattery += $this->creationTime - $previousStatus->creationTime;
      }
    }
  }

  private function initialize() {
    $this->state  = new SystemPowerStatus();

    WinApi::call('Kernel32::GetSystemPowerStatus', array($this->state));
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
      return 0;
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
}
