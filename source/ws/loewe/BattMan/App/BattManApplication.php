<?php

namespace ws\loewe\BattMan\App;

use \ws\loewe\BattMan\Model\BatteryState;
use \ws\loewe\BattMan\View\TextView;
use \ws\loewe\BattMan\View\GraphView;
use \ws\loewe\BattMan\View\ControlView;
use \ws\loewe\BattMan\View\LogView;
use \ws\loewe\Woody\App\Application;
use \ws\loewe\Woody\Components\Windows\ResizableWindow;
use \ws\loewe\Woody\Components\Controls\ProgressBar;
use \ws\loewe\Woody\Components\Timer\Timer;
use \ws\loewe\Woody\Event\WindowResizeAdapter;
use \ws\loewe\Woody\Event\WindowResizeEvent;
use \ws\loewe\Utils\Geom\Point;
use \ws\loewe\Utils\Geom\Dimension;

class BattManApplication extends Application {

  private $status               = null;

  private $textView             = null;
  private $graphView            = null;
  private $controlView          = null;
  private $logView              = null;
  private $barPower             = null;

  private $updateTimer          = null;
  private $updateTimerInterval  = 1000;

  private $isRunning            = FALSE;
  private $shutdownTimer        = null;

  /**
   * This method acts as the constructor of the class.
   */
  public function __construct() {
    parent::__construct();

    $this->window = new ResizableWindow('ws\loewe\BattMan', Point::createInstance(50, 50), Dimension::createInstance(825, 525));

    $this->window->create(null);

    $this->window->addWindowResizeListener(new WindowResizeAdapter(function(WindowResizeEvent $event) {
      $delta = $event->getDeltaDimension();

      $this->graphView->resizeBy(Dimension::createInstance($delta->width, $delta->height));
      $this->logView->resizeBy(Dimension::createInstance($delta->width, $delta->height));
      $this->barPower->moveBy(Dimension::createInstance(0, $delta->height))->resizeBy(Dimension::createInstance($delta->width, 0));
    }));

    $this->window->setWindowCloseListener(
            new \ws\loewe\Woody\Event\WindowCloseAdapter(
                    function($event) {
              $event->getSource()->close();
              echo "\nGoodbye";}
                    ));

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
   */
  public function start() {
    $this->isRunning = TRUE;

    $this->init();

    parent::start();
 }

  private function initTextView() {
    $this->textView = new TextView(Point::createInstance(5, 5), Dimension::createInstance(300, 120));

    $this->window->getRootPane()->add($this->textView->getFrame());
    $this->textView->initialize();
  }

  private function initGraphView() {
    $this->graphView = new GraphView(Point::createInstance(305, 5), Dimension::createInstance(500, 450));

    $this->window->getRootPane()->add($this->graphView->getFrame());
    $this->graphView->initialize();
  }

  private function initControlView() {
    $this->controlView = new ControlView(Point::createInstance(5, 130), Dimension::createInstance(300, 50));

    $this->window->getRootPane()->add($this->controlView->getFrame());
    $this->controlView->initialize();
  }

  private function initLogView() {
    $this->logView = new LogView(Point::createInstance(5, 185), Dimension::createInstance(300, 297));

    $this->window->getRootPane()->add($this->logView->getFrame());
    $this->logView->initialize();
  }

  private function init() {
    $this->initTextView();
    $this->initControlView();
    $this->initGraphView();
    $this->initLogView();

    $this->barPower = new ProgressBar(Point::createInstance(305, 465), Dimension::createInstance(500, 25));
    $this->window->getRootPane()->add($this->barPower);
    $this->barPower->setRange(0, 100);

    // make view updates  periodically
    $updateCallback = function() {
      $this->updateViews();
    };
    $this->updateTimer = new Timer($updateCallback, $this->window, $this->updateTimerInterval);
    $this->updateTimer->start();

    $this->updateViews();
  }

  private function updateViews() {
    $this->status = new BatteryState($this->status);

    $this->textView->update($this->status);
    $this->barPower->setProgress($this->status->getPercentRemaining());

    // update log and graph only every 10th time
    if($this->updateTimer->getExecutionCount() % 10 === 0) {
      $this->graphView->update($this->status);
      $this->logView->update($this->status);
    }
  }

  /**
   * This method stops the application.
   *
   * @return \ws\loewe\BattMan\App\ws\loewe\BattManApplication $this
   */
  public function stop() {
    $this->isRunning = FALSE;
  }

  /**
   * This method is a utility method to format an amount of seconds in a human readable way.
   *
   * @param int $seconds the amount of seconds
   * @return string the amount of seconds formatted in a human readable way
   */
  public static function formatSeconds($seconds) {
    $date = new \DateTime();
    $date->add(new \DateInterval('PT'.$seconds.'S'));

    return $date->diff(new \DateTime())->format('%H:%I:%S');
  }
}