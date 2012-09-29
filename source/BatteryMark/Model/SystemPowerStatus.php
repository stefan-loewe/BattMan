<?php

namespace BatteryMark\Model;

use Woody\Util\WinApi\Structure;

class SystemPowerStatus extends Structure {
  /**
   * @var Byte
   */
  public $ACLineStatus = null;

  /**
   * @var Byte
   */
  public $BatteryFlag = null;

  /**
   * @var Byte
   */
  public $BatteryLifePercent = null;

  /**
   * @var Byte
   */
  public $Reserved1 = null;

  /**
   * @var Dword
   */
  public $BatteryLifeTime = null;

  /**
   * @var Dword
   */
  public $BatteryFullLifeTime = null;

  /**
   * This method acts as the constructor of the class.
   */
  public function __construct() {}
}