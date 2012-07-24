<?php

namespace BatteryMark\App;

use \Woody\App\Application;
use \Woody\Layouts\GridLayout;
use \Woody\Components\Windows\ResizableWindow;
use \Woody\Components\Controls\EditBox;
use \Woody\Components\Controls\Label;
use \Woody\Components\Controls\Frame;
use \Woody\Components\Controls\Image;
use \Woody\Components\Controls\ProgressBar;
use \Woody\Components\Timer\Timer;
use \Woody\Event\WindowResizeAdapter;
use \Woody\Event\WindowResizeEvent;
use \Woody\Util\ImageResource;
use \Utils\Geom\Point;
use \Utils\Geom\Dimension;

class BatteryMarkApplication extends Application {

  private $status       = null;

  private $stati        = array();

  private $frmTextView  = null;
  private $graphView = null;
  private $barPower     = null;

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
      $this->imgLoadGraph->resizeBy(new Dimension($delta->width, $delta->height));
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
    $this->frmTextView = new Frame('', new Point(5, 5), new Dimension(300, 100));
    $this->window->getRootPane()->add($this->frmTextView);

    $this->frmTextView->setLayout($layout = new GridLayout(4, 2, 0, 0));

    $this->frmTextView->add(new Label('connected to power:', new Point(5, 5), new Dimension(15, 15)));
    $this->frmTextView->add($this->txtConnectedToAC = new EditBox('', new Point(25, 5), new Dimension(15, 15)));

    $this->frmTextView->add(new Label('battery being charged:', new Point(5, 25), new Dimension(15, 15)));
    $this->frmTextView->add($this->txtIsCharging = new EditBox('', new Point(25, 25), new Dimension(15, 15)));

    $this->frmTextView->add(new Label('remaining battery life:', new Point(5, 45), new Dimension(15, 15)));
    $this->frmTextView->add($this->txtPercentLeft = new EditBox('', new Point(25, 45), new Dimension(15, 15)));

    $this->frmTextView->add(new Label('remaining battery time:', new Point(5, 65), new Dimension(15, 15)));
    $this->frmTextView->add($this->txtTimeLeft = new EditBox('', new Point(25, 65), new Dimension(15, 15)));

    $layout->layout($this->frmTextView);

    $this->txtConnectedToAC->setReadOnly(TRUE);
    $this->txtIsCharging->setReadOnly(TRUE);
    $this->txtPercentLeft->setReadOnly(TRUE);
    $this->txtTimeLeft->setReadOnly(TRUE);
  }

  private function initGraphView() {
    $this->graphView = new \BatteryMark\View\GraphView();

    $this->window->getRootPane()->add($this->graphView->getFrame());
  }

  private function init() {

    $this->initTextView();

    $this->barPower = new ProgressBar(new Point(305, 605), new Dimension(700, 25));
    $this->window->getRootPane()->add($this->barPower);
    $this->barPower->setRange(0, 100);

    $this->initGraphView();

    $frm = new Frame('', new Point(5, 225), new Dimension(200, 200));
    $this->window->getRootPane()->add($frm);


    $this->modelUpdateTimer = new Timer(function() {

      $this->status = new \BatteryMark\Model\BatteryState();

      $this->barPower->setProgress($this->status->getPercentRemaining());

      $this->stati[] = $this->status->getPercentRemaining();

      $this->txtConnectedToAC->setValue($this->status->getPowerStatus());
      $this->txtIsCharging->setValue($this->status->getBatteryStatus());
      $this->txtPercentLeft->setValue($this->status->getPercentRemaining());
      $this->txtTimeLeft->setValue($this->status->getTimeRemaining());

      $this->graphView->remove($this->imgLoadGraph);

      $this->graphView->update($this->status);
    }, $this->window, 1000);

    $this->modelUpdateTimer->start();
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