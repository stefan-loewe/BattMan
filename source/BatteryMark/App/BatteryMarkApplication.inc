<?php

namespace BatteryMark\App;

use \BatteryMark\Model\BatteryState;
use \BatteryMark\View\TextView;
use \BatteryMark\View\GraphView;
use \Woody\App\Application;
use \Woody\Components\Windows\ResizableWindow;
use \Woody\Components\Controls\ProgressBar;
use \Woody\Components\Timer\Timer;
use \Woody\Event\WindowResizeAdapter;
use \Woody\Event\WindowResizeEvent;
use \Utils\Geom\Point;
use \Utils\Geom\Dimension;

class BatteryMarkApplication extends Application {

  private $status       = null;

  private $textView     = null;
  private $graphView    = null;
  private $barPower     = null;
  
  private $updateTimer  = null;

  /**
   * This method acts as the constructor of the class.
   */
  public function __construct() {
    $this->window = new ResizableWindow('BatteryMark', new Point(50, 50), new Dimension(1024, 700));

    $this->window->create(null);

    $this->window->addWindowResizeListener(new WindowResizeAdapter(function(WindowResizeEvent $event) {
      $delta = $event->getDeltaDimension();

      /*root pane as frame still not implemented correctly
        -> consider to make dependency of Window to Frame (root-panle) explicit,
        by pasing the frame to the constructor of the window -> NO !!!

      --> add layout to frames aware, esp. on-resize!
*/

      $this->graphView->resizeBy(new Dimension($delta->width, $delta->height));
      $this->barPower->moveBy(new Dimension(0, $delta->height))->resizeBy(new Dimension($delta->width, 0));
    }));

    $this->shutdownTimer = new Timer($this->getShutdownCallback(), $this->window, 1000);

    $this->shutdownTimer->start();
  }

  // the callback that actually closes the window
  private function getShutdownCallback() {
    return function() {
      if(!$this->isRunning) {
        $this->shutdownTimer->destroy();
        $this->window->destroy();
      }
    };
  }

  /**
   * This method starts the application.
   *
   * @return \BatteryMark\App\BatteryMarkApplication $this
   */
  public function start() {
    $this->isRunning = TRUE;

    $this->init();

    $this->window->startEventHandler();

    return $this;
  }

  private function initTextView() {
    $this->textView = new TextView();

    $this->window->getRootPane()->add($this->textView->getFrame());
    $this->textView->initialize();
  }

  private function initGraphView() {
    $this->graphView = new GraphView();

    $this->window->getRootPane()->add($this->graphView->getFrame());
    $this->graphView->initialize();
  }

  private function init() {
    $this->initTextView();
    $this->initGraphView();

    $this->barPower = new ProgressBar(new Point(305, 605), new Dimension(700, 25));
    $this->window->getRootPane()->add($this->barPower);
    $this->barPower->setRange(0, 100);

    $this->updateTimer = new Timer(function() {
      $this->status = new BatteryState();
      
      $this->textView->update($this->status);
      $this->graphView->update($this->status);

      $this->barPower->setProgress($this->status->getPercentRemaining());
    }, $this->window, 1000);

    $this->updateTimer->start();
    
    
    $slider1 = new \Woody\Components\Controls\Slider(new Point(10, 110), new Dimension(290, 25));
    $this->window->getRootPane()->add($slider1);

    $chkStayAwake = new \Woody\Components\Controls\Checkbox(0, new Point(10, 140), new Dimension(25, 25));
    $this->window->getRootPane()->add($chkStayAwake);
    
    $timer = new Timer(function() use ($chkStayAwake) {
      if($chkStayAwake->isChecked()) {
        $libUser  = wb_load_library('Kernel32');
        $function = wb_get_function_address('SetThreadExecutionState', $libUser);
        wb_call_function($function, array(/*ES_DISPLAY_REQUIRED:=*/2));
      }
    }, $this->window, 55000);

    $timer->start();

    $this->timers = array();
    $slider1->setRange(0, 20)->setValue(10)->addActionListener(new \Woody\Event\ActionAdapter(function($event) {

      if(isset($this->timers[$event->getSource()->getID()]) && $this->timers[$event->getSource()->getID()]->isRunning()) {
        var_dump("destroy");
        $this->timers[$event->getSource()->getID()]->destroy();
      }

      $source = $event->getSource();
      $timer = new Timer(function() use ($source) {
        var_dump("exec");
        exec(('powershell -executionPolicy Unrestricted -Command "function set-monitorBrightness {  [CmdletBinding()] param ( [ValidateRange(0,100)] [int]$brightness ) $monitors = Get-WmiObject -Namespace root\wmi -Class WmiMonitorBrightnessMethods; foreach ($monitor in $monitors){ $monitor.WmiSetBrightness(5, $brightness) } }; set-monitorBrightness '.($source->getValue() * 5).';" < NUL'));
        $this->timers[$source->getID()]->destroy();
      }, $this->window, 1000);

      $timer->start();
      $this->timers[$event->getSource()->getID()] = $timer;
    }));
  }

  /**
   * This method stops the application.
   *
   * @return \BatteryMark\App\BatteryMarkApplication $this
   */
  public function stop() {
    $this->isRunning = FALSE;
  }
}