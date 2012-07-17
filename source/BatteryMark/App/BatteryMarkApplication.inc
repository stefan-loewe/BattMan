<?php

namespace BatteryMark\App;

use \Woody\App\Application;
use \Woody\Layouts\GridLayout;
use \Woody\Components\Windows\ResizableWindow;
use \Woody\Components\Controls\EditBox;
use \Woody\Components\Controls\Label;
use \Woody\Components\Controls\Frame;
use \Woody\Components\Controls\Image;
use \Woody\Components\Timer\Timer;
use \Woody\Util\ImageResource;
use \Utils\Geom\Point;
use \Utils\Geom\Dimension;
use \Woody\Event\WindowResizeAdapter;
use \Woody\Event\WindowResizeEvent;

class BatteryMarkApplication extends Application {

  private $frmTextView  = null;

  private $frmGraphView = null;

  private $status       = null;

  private $stati        = array();

  /**
   * This method acts as the constructor of the class.
   */
  public function __construct() {
    $this->window = new ResizableWindow('BatteryMark', new Point(50, 50), new Dimension(1024, 700));

    $this->window->create(null);

    $this->window->addWindowResizeListener(new WindowResizeAdapter(function(WindowResizeEvent $event) {
      $delta = $event->getDeltaDimension();
/*
      window dimension is not updated yet on window resize ... do so, and call event handler
      implement this properly, than implement spefic handling here properly

      also, root pane as frame still not implemented!
*/
      $this->frmGraphView->resizeBy(new Dimension($delta->width, $delta->height));
      $this->imgLoadGraph->resizeBy(new Dimension($delta->width, $delta->height));

      $this->frmGraphView->remove($this->imgLoadGraph);
      $this->initGraphImage($this->plot($this->getGraphBitmap()));
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
    $this->frmTextView = new Frame('', new Point(5, 5), new Dimension(200, 200));
    $this->window->add($this->frmTextView);

    $this->frmTextView->setLayout($layout = new GridLayout(6, 2, 0, 0));

    $this->frmTextView->add(new Label('connected to power:', new Point(5, 5), new Dimension(15, 15)));
    $this->frmTextView->add($this->boxPower = new EditBox('label1', new Point(25, 5), new Dimension(15, 15)));

    $this->frmTextView->add(new Label('battery being charged:', new Point(5, 25), new Dimension(15, 15)));
    $this->frmTextView->add($this->boxCharging = new EditBox('label2', new Point(25, 25), new Dimension(15, 15)));

    $this->frmTextView->add(new Label('remaining battery life:', new Point(5, 45), new Dimension(15, 15)));
    $this->frmTextView->add($this->boxPercentLeft = new EditBox('label3', new Point(25, 45), new Dimension(15, 15)));

    $this->frmTextView->add(new Label('remaining battery time:', new Point(5, 65), new Dimension(15, 15)));
    $this->frmTextView->add($this->boxTimeLeft = new EditBox('label4', new Point(25, 65), new Dimension(15, 15)));

    $layout->layout($this->frmTextView);

    wb_set_handler($this->frmTextView->getControlID(), 'globalWinBinderEventHandler');
  }

  private function initGraphView() {
    $this->frmGraphView = new Frame('', new Point(205, 5), new Dimension(800, 600));
    $this->window->add($this->frmGraphView);

    wb_set_handler($this->frmGraphView->getControlID(), 'globalWinBinderEventHandler');

    $this->initGraphImage($this->getGraphBitmap());
  }

  private function initGraphImage($bitmap) {
    var_dump(time().': redraw');
    $dim = $this->frmGraphView->getDimension();
    $this->imgLoadGraph = new Image($bitmap, new Point(5, 15), new Dimension($dim->width - 10, $dim->height - 20));
    $this->frmGraphView->add($this->imgLoadGraph);
  }

  private function getGraphBitmap() {
    $dim = $this->frmGraphView->getDimension();
    $bitmap = ImageResource::create(new Dimension($dim->width - 10, $dim->height - 20));
    $bitmap->drawRectangle(new Point(0, 0), new Dimension($dim->width - 10, $dim->height - 20), 0xFFFFFF, TRUE, null, null);

    for($x = 0; $x < $this->frmGraphView->getDimension()->width; $x += 10) {
      $bitmap->drawLine(new Point($x, 0), new Point($x, $dim->height - 20), 0xFFC0A0, FALSE, null, null, null);
    }

    return $bitmap;
  }

  private function plot($res) {
    foreach($this->stati as $index => $status) {
      if(isset($this->stati[$index + 1])) {
        $width = $this->imgLoadGraph->getDimension()->width / (count($this->stati) + 1);

        $height = $this->imgLoadGraph->getDimension()->height;

        $source = new Point($index * $width, $height - $height * ($status / 100));
        $target = new Point(($index + 1) * $width, $height - $height * ($this->stati[$index + 1] / 100));
        $res->drawLine($source,
          $target,
          0x0000FF,
          FALSE,
          null,
          null,
          null);
      }
    }

    return $res;
  }

  private function init() {
    $this->initTextView();
    $this->initGraphView();


    // hve that as xml !?!?!
    \cStruct\cStruct::define('SystemPowerStatus', "
      BYTE  ACLineStatus;
      BYTE  BatteryFlag;
      BYTE  BatteryLifePercent;
      BYTE  Reserved1;
      DWORD BatteryLifeTime;
      DWORD BatteryFullLifeTime;");

    $this->status = new \SystemPowerStatus();

    $this->modelUpdateTimer = new Timer(function() {
        \WinAPI::Kernel32()->GetSystemPowerStatus($this->status);

        $this->stati[] = mt_rand(0, 100);//$this->status->BatteryLifePercent;

        $this->boxPower->setValue($this->status->ACLineStatus);
        $this->boxCharging->setValue($this->status->BatteryFlag);
        $this->boxPercentLeft->setValue($this->status->BatteryLifePercent);

        $this->frmGraphView->remove($this->imgLoadGraph);

        $this->initGraphImage($this->plot($this->getGraphBitmap()));
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