<?php

namespace BatteryMark\Components\Timer;

use \Woody\Components\Windows\AbstractWindow;
use \Woody\Components\Timer\Timer;

class RunOnceTimer extends Timer {
  
  public function __construct(\Closure $callback, AbstractWindow $window, $interval) {
    parent::__construct($callback, $window, $interval);
  }
  
  public function run() {
    parent::run();
    
    $this->destroy();
  }
}