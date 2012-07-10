<?php

namespace BatteryMark\App;

use \Woody\App\Application;
use \Woody\Components\Windows\ResizableWindow;
use \Woody\Components\Timer\Timer;
use \Utils\Geom\Point;
use \Utils\Geom\Dimension;

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
    $this->frmTextView = new \Woody\Components\Controls\Frame('', new Point(5, 5), new Dimension(200, 200));
    $this->window->add($this->frmTextView);
    
    $this->frmTextView->setLayout($layout = new \Woody\Layouts\GridLayout(6, 2, 0, 10));
    $this->frmTextView->add(new \Woody\Components\Controls\Label('connected to power:', new Point(5, 5), new Dimension(15, 15)));
    $this->frmTextView->add($this->boxPower = new \Woody\Components\Controls\EditBox('label1', new Point(25, 5), new Dimension(15, 15)));
    
    $this->frmTextView->add(new \Woody\Components\Controls\Label('battery being charged:', new Point(5, 25), new Dimension(15, 15)));
    $this->frmTextView->add($this->boxCharging = new \Woody\Components\Controls\EditBox('label2', new Point(25, 25), new Dimension(15, 15)));
    
    $this->frmTextView->add(new \Woody\Components\Controls\Label('remaining battery life:', new Point(5, 45), new Dimension(15, 15)));
    $this->frmTextView->add($this->boxPercentLeft = new \Woody\Components\Controls\EditBox('label3', new Point(25, 45), new Dimension(15, 15)));
    
    $this->frmTextView->add(new \Woody\Components\Controls\Label('remaining battery time:', new Point(5, 65), new Dimension(15, 15)));
    $this->frmTextView->add($this->boxTimeLeft = new \Woody\Components\Controls\EditBox('label4', new Point(25, 65), new Dimension(15, 15)));
    
    $layout->layout($this->frmTextView);
  }
  
  public function init() {
    
    $this->initTextView();
    
    $this->frmGraphView = new \Woody\Components\Controls\Frame('', new Point(205, 5), new Dimension(800, 600));
    $this->window->add($this->frmGraphView);

    $this->stati = array();
      
    $res = new \Woody\Util\ImageResource(null, new Dimension(790, 590));
    $this->imgLoadGraph = new \BatteryMark\Controls\Image($res, new Point(5, 8), new Dimension(790, 590), $this->stati);
    $this->frmGraphView->add($this->imgLoadGraph);
        
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

        $res = new \Woody\Util\ImageResource(null, new Dimension(790, 590));
        $this->imgLoadGraph = new \BatteryMark\Controls\Image($res, new Point(5, 8), new Dimension(790, 590), $this->stati);
        $this->frmGraphView->add($this->imgLoadGraph);
        
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