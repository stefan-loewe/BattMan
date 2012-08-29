<?php

namespace BatteryMark\View;

use \BatteryMark\Model\Model;
use \Woody\App\Application;
use \Woody\Components\Controls\Checkbox;
use \Woody\Components\Controls\Label;
use \Woody\Components\Timer\Timer;
use \Woody\Event\ActionAdapter;
use \Woody\Layouts\GridLayout;
use \Utils\Geom\Point;
use \Utils\Geom\Dimension;

class ControlView extends View  {
  private $chkDimDisplay  = null;
  private $chkStayAwake   = null;

  public function __construct(Point $topLeftCorner, Dimension $dimension) {
    parent::__construct($topLeftCorner, $dimension);
  }

  public function initialize() {
    $this->frame->setLayout($layout = new GridLayout(2, 2, 0, 0));

    $this->frame->add(new Label('dim display:', new Point(5, 5), new Dimension(15, 15)));
    $this->chkDimDisplay = new Checkbox(0, new Point(10, 110), new Dimension(25, 25));
    $this->frame->add($this->chkDimDisplay);

    $this->frame->add(new Label('stay awake:', new Point(5, 5), new Dimension(15, 15)));
    $this->chkStayAwake = new Checkbox(0, new Point(10, 140), new Dimension(25, 25));
    $this->frame->add($this->chkStayAwake);

    $timerDisplayRequired = new Timer(function() {
      if($this->chkStayAwake->isChecked()) {
        $libKernel32  = wb_load_library('Kernel32');
        $function     = wb_get_function_address('SetThreadExecutionState', $libKernel32);
        wb_call_function($function, array(//ES_SYSTEM_REQUIRED | ES_DISPLAY_REQUIRED:=
          0x00000001 | 0x00000002));
      }
    }, Application::getInstance()->getWindow(), 55555);
    $timerDisplayRequired->start();

    $this->chkDimDisplay->addActionListener(new ActionAdapter(function($event) {
      $value = $event->getSource()->isChecked() ? 0 : 100;
      exec('powershell -executionPolicy Unrestricted -Command "function set-monitorBrightness {  [CmdletBinding()] param ( [ValidateRange(0,100)] [int]$brightness ) $monitors = Get-WmiObject -Namespace root\wmi -Class WmiMonitorBrightnessMethods; foreach ($monitor in $monitors){ $monitor.WmiSetBrightness(5, $brightness) } }; set-monitorBrightness '.$value.';" < NUL');
    }));

    $layout->layout($this->frame);
  }

  public function update(Model $currentState) {
    $this->txtConnectedToAC->setValue($currentState->getPowerStatus());
    $this->txtIsCharging->setValue($currentState->getBatteryStatus());
    $this->txtPercentLeft->setValue($currentState->getPercentRemaining());
    $this->txtTimeLeft->setValue($currentState->getTimeRemaining());
  }
}