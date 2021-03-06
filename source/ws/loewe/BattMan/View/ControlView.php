<?php

namespace ws\loewe\BattMan\View;

use \ws\loewe\BattMan\Model\Model;
use \ws\loewe\Woody\App\Application;
use \ws\loewe\Woody\Components\Controls\Checkbox;
use \ws\loewe\Woody\Components\Controls\Label;
use \ws\loewe\Woody\Components\Timer\Timer;
use \ws\loewe\Woody\Event\ActionAdapter;
use \ws\loewe\Woody\Layouts\GridLayout;
use \ws\loewe\Utils\Geom\Point;
use \ws\loewe\Utils\Geom\Dimension;

class ControlView extends View  {
  private $chkDimDisplay  = null;
  private $chkStayAwake   = null;

  public function __construct(Point $topLeftCorner, Dimension $dimension) {
    parent::__construct($topLeftCorner, $dimension);
  }

  public function initialize() {
    $this->frame->setLayout($layout = new GridLayout(2, 2, 0, 0));

    $this->frame->add(new Label('dim display:', Point::createInstance(5, 5), Dimension::createInstance(15, 15)));
    $this->chkDimDisplay = new Checkbox(0, Point::createInstance(10, 110), Dimension::createInstance(25, 25));
    $this->frame->add($this->chkDimDisplay);

    $this->frame->add(new Label('stay awake:', Point::createInstance(5, 5), Dimension::createInstance(15, 15)));
    $this->chkStayAwake = new Checkbox(0, Point::createInstance(10, 140), Dimension::createInstance(25, 25));
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
  }
}