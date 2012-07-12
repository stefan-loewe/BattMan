<?php

namespace BatteryMark\App;

use \Woody\App\Application;
use \Woody\Layouts\GridLayout;
use \Woody\Components\Windows\ResizableWindow;
use \Woody\Components\Controls\EditBox;
use \Woody\Components\Controls\Label;
use \Woody\Components\Controls\Frame;
use \BatteryMark\Controls\Image;
use \Woody\Components\Timer\Timer;
use \Utils\Geom\Point;
use \Utils\Geom\Dimension;

use \Woody\Components\Controls\Scrollbar;
use \Woody\Components\Controls\Slider;

class BatteryMarkApplication extends Application {

  private $frmTextView = null;
  private $frmGraphView = null;
  /**
   * This method acts as the constructor of the class.
   */
  public function __construct() {
    $this->window = new ResizableWindow('BatteryMark', new Point(50, 50), new Dimension(1024, 700));

    $this->shutdownTimer = new Timer($this->getShutdownCallback(), $this->window, 1000);

    $this->window->create(null);

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

    $this->frmTextView->setLayout($layout = new GridLayout(6, 2, 0, 10));
    $this->frmTextView->add(new Label('connected to power:', new Point(5, 5), new Dimension(15, 15)));
    $this->frmTextView->add($this->boxPower = new EditBox('label1', new Point(25, 5), new Dimension(15, 15)));

    $this->frmTextView->add(new Label('battery being charged:', new Point(5, 25), new Dimension(15, 15)));
    $this->frmTextView->add($this->boxCharging = new EditBox('label2', new Point(25, 25), new Dimension(15, 15)));

    $this->frmTextView->add(new Label('remaining battery life:', new Point(5, 45), new Dimension(15, 15)));
    $this->frmTextView->add($this->boxPercentLeft = new EditBox('label3', new Point(25, 45), new Dimension(15, 15)));

    $this->frmTextView->add(new Label('remaining battery time:', new Point(5, 65), new Dimension(15, 15)));
    $this->frmTextView->add($this->boxTimeLeft = new EditBox('label4', new Point(25, 65), new Dimension(15, 15)));

    $layout->layout($this->frmTextView);

    $this->frmTextView->add(new Scrollbar(new Point(5, 9), new Dimension(15, 188)));
    $this->frmTextView->add(new Slider(new Point(25, 9), new Dimension(15, 188)));

    wb_set_handler($this->frmTextView->getControlID(), 'globalWinBinderEventHandler');

    $tab = wb_create_control($this->window->getControlID(), TabControl, '', 10, 400, 100, 100);

    wb_set_handler($tab, 'globalWinBinderEventHandler');

    wb_create_control($tab, ScrollBar, '', 10, 10, 50, 20, 123);
    wb_create_control($tab, EditBox, '', 10, 50, 50, 20, 456);
  }

  public function init() {

    $this->initTextView();

    $this->frmGraphView = new Frame('', new Point(205, 5), new Dimension(800, 600));
    $this->window->add($this->frmGraphView);

    $this->stati = array();

    $res = \Woody\Util\ImageResource::create(new Dimension(2000, 590));
    $res->drawRectangle(new Point(0, 0), new Dimension(2000, 590), 0xDDFFFF, TRUE, null, null);

    for($x = 0; $x < 2000; $x += 20) {
        $res->drawLine(new Point($x, 0), new Point($x, 590), 0xFF8050, FALSE, null, null, null);
    }

    foreach($this->stati as $index => $status) {
      if(isset($this->stati[$index + 1])) {
        $res->drawLine(new Point((2000 / count($this->stati)) * $index, 590 - 590 * ($status / 100)),
          new Point((2000 / count($this->stati)) * ($index + 1), 590 - 590 * ($this->stati[$index + 1] / 100)),
          0x0000FF,
          FALSE,
          null,
          null,
          null);
      }
    }
$this->xOffset = 5;
    $this->imgLoadGraph = new \Woody\Components\Controls\Image($res, new Point(5, 8), new Dimension(2000, 580));
    $this->frmGraphView->add($this->imgLoadGraph);
    $this->frmGraphView->add($scroll = new Scrollbar(new Point($this->xOffset, 582), new Dimension(790, 15)));

    $scroll->addActionListener(new \Woody\Event\ActionAdapter(function($event) {
      $src = $event->getSource();
      $offset = $src->getOffset();

      $this->xOffset = 5 - $offset;

      $this->imgLoadGraph->moveTo(new Point(200-$offset, 5));

      var_dump($this->xOffset);
    }));

    $this->status = null;

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

        $this->stati[] = $this->status->BatteryLifePercent;
        //$this->stati[] = rand(0, 100);
        if(count($this->stati) > 20)
            array_shift($this->stati);

        $this->boxPower->setValue($this->status->ACLineStatus);
        $this->boxCharging->setValue($this->status->BatteryFlag);
        $this->boxPercentLeft->setValue($this->status->BatteryLifePercent);
//now + time aqdd -> diff => result
    //    $period = new \DateInterval('P0Y0DT0H0M'.$this->status->BatteryLifeTime.'S');
  //      $this->boxTimeLeft->setValue($period->format('%H:%I:%S'));

        $this->frmGraphView->remove($this->imgLoadGraph);

        $res = \Woody\Util\ImageResource::create(new Dimension(2000, 590));
        $res->drawRectangle(new Point(0, 0), new Dimension(2000, 590), 0xDDFFFF, TRUE, null, null);

        for($x = 0; $x < 2000; $x += 20) {
            $res->drawLine(new Point($x, 0), new Point($x, 590), 0xFF8050, FALSE, null, null, null);
        }

        foreach($this->stati as $index => $status) {
          if(isset($this->stati[$index + 1])) {
            $res->drawLine($p1 = new Point($index, 550 - 550 * ($status / 100) + 10),
              $p2 = new Point(($index + 1), 550 - 550 * ($this->stati[$index + 1] / 100) + 10),
              0x0000FF,
              FALSE,
              null,
              null,
              null);
          }
        }
//var_dump('$this->xOffset = '.$this->xOffset);
        $this->imgLoadGraph = new \Woody\Components\Controls\Image($res, new Point($this->xOffset, 8), new Dimension(2000, 580));
        $this->frmGraphView->add($this->imgLoadGraph);
//wb_set_visible($this->frmTextView->getControlID(), false);
//wb_set_visible($this->frmTextView->getControlID(), true);


    }, $this->window, 1000);

    //$this->modelUpdateTimer->start();
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