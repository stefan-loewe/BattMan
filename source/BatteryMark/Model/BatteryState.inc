<?php

namespace BatteryMark\Model;

class BatteryState {

  private $state              = null;

  const AC_OFFLINE            = 0;

  const AC_ONLINE             = 1;

  const NO_BATTERY            = 128;

  const BATTERY_STATE_UNKNOWN = 255;

  const BATTERY_CHARGING      = 8;

  const PERCENTAGE_UNKNOWN    = 255;

  const TIME_UNKNOWN          = -1;

  public function __construct() {
    $this->initialize();
  }

  private function initialize() {
    $this->state = $this->initializeBatteryStateStruct();

    \WinAPI::Kernel32()->GetSystemPowerStatus($this->state);
  }

  private function initializeBatteryStateStruct() {
    if(!class_exists('SystemPowerStatus')) {
      \cStruct\cStruct::define('SystemPowerStatus', "
        BYTE  ACLineStatus;
        BYTE  BatteryFlag;
        BYTE  BatteryLifePercent;
        BYTE  Reserved1;
        DWORD BatteryLifeTime;
        DWORD BatteryFullLifeTime;");
    }

    return new \SystemPowerStatus();
  }

  public function getPowerStatus() {
    if($this->state->ACLineStatus === self::AC_OFFLINE) {
      return 'offline';
    }

    else if($this->state->ACLineStatus === self::AC_ONLINE) {
      return 'online';
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
      return 'charging';
    }

    return 'not charging';
  }

  public function getPercentRemaining() {
    if($this->state->BatteryLifePercent === self::PERCENTAGE_UNKNOWN) {
      return 'unknown';
    }

    return $this->state->BatteryLifePercent;
  }

  public function getTimeRemaining() {
    $this->powerStatus = 'unknown';

    if($this->state->BatteryLifeTime === self::TIME_UNKNOWN) {
      return 'unknown';
    }

    $date = new \DateTime();
    $date->add(new \DateInterval('PT'.$this->state->BatteryLifeTime.'S'));

    return $date->diff(new \DateTime())->format('%H:%I:%S');
  }
}