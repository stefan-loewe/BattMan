<?php

namespace BatteryMark\App;

use \BatteryMark\Model\BatteryState;
use \BatteryMark\View\TextView;
use \BatteryMark\View\GraphView;
use \BatteryMark\View\ControlView;
use \BatteryMark\View\LogView;
use \Woody\App\Application;
use \Woody\Components\Windows\ResizableWindow;
use \Woody\Components\Controls\ProgressBar;
use \Woody\Components\Timer\Timer;
use \Woody\Event\WindowResizeAdapter;
use \Woody\Event\WindowResizeEvent;
use \Utils\Geom\Point;
use \Utils\Geom\Dimension;

class BatteryMarkApplication extends Application {

  private $status               = null;

  private $textView             = null;
  private $graphView            = null;
  private $controlView          = null;
  private $logView              = null;
  private $barPower             = null;

  private $updateTimer          = null;
  private $updateTimerInterval  = 10000;

  /**
   * This method acts as the constructor of the class.
   */
  public function __construct() {
    parent::__construct();

    $this->window = new ResizableWindow('BatteryMark', new Point(50, 50), new Dimension(825, 525));

    $this->window->create(null);

    $this->window->addWindowResizeListener(new WindowResizeAdapter(function(WindowResizeEvent $event) {
      $delta = $event->getDeltaDimension();

      $this->graphView->resizeBy(new Dimension($delta->width, $delta->height));
      $this->logView->resizeBy(new Dimension($delta->width, $delta->height));
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
    $this->textView = new TextView(new Point(5, 5), new Dimension(300, 120));

    $this->window->getRootPane()->add($this->textView->getFrame());
    $this->textView->initialize();
  }

  private function initGraphView() {
    $this->graphView = new GraphView(new Point(305, 5), new Dimension(500, 450));

    $this->window->getRootPane()->add($this->graphView->getFrame());
    $this->graphView->initialize();
  }

  private function initControlView() {
    $this->controlView = new ControlView(new Point(5, 130), new Dimension(300, 50));

    $this->window->getRootPane()->add($this->controlView->getFrame());
    $this->controlView->initialize();
  }

  private function initLogView() {
    $this->logView = new LogView(new Point(5, 185), new Dimension(300, 297));

    $this->window->getRootPane()->add($this->logView->getFrame());
    $this->logView->initialize();
  }

  private function init() {
    $this->initTextView();
    $this->initControlView();
    $this->initGraphView();
    $this->initLogView();

    $this->barPower = new ProgressBar(new Point(305, 465), new Dimension(500, 25));
    $this->window->getRootPane()->add($this->barPower);
    $this->barPower->setRange(0, 100);

    $this->update();

    $this->updateTimer = new Timer(function() {
      $this->update();
    }, $this->window, $this->updateTimerInterval);

    $this->updateTimer->start();
  }

  private function update() {
    $this->status = new BatteryState($this->status);

    $this->textView->update($this->status);
    $this->graphView->update($this->status);
    $this->logView->update($this->status);

    $this->barPower->setProgress($this->status->getPercentRemaining());
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