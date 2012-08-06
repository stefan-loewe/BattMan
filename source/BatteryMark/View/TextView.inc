<?php

namespace BatteryMark\View;

use \Woody\Components\Controls\Frame;
use \Woody\Layouts\GridLayout;
use \Woody\Components\Controls\EditBox;
use \Woody\Components\Controls\Label;
use \Utils\Geom\Point;
use \Utils\Geom\Dimension;

class TextView {

  private $frame            = null;
  private $frmDimension     = null;
  
  private $txtConnectedToAC = null;
  private $txtIsCharging    = null;
  private $txtPercentLeft   = null;
  private $txtTimeLeft      = null;

  public function __construct() {
    $this->frmDimension = new Dimension(300, 100);

    $this->frame        = new Frame('', new Point(5, 5), $this->frmDimension);
  }

  public function initialize() {
    $this->frame->setLayout($layout = new GridLayout(4, 2, 0, 0));

    $this->frame->add(new Label('connected to power:', new Point(5, 5), new Dimension(15, 15)));
    $this->frame->add($this->txtConnectedToAC = new EditBox('', new Point(25, 5), new Dimension(15, 15)));

    $this->frame->add(new Label('battery being charged:', new Point(5, 25), new Dimension(15, 15)));
    $this->frame->add($this->txtIsCharging = new EditBox('', new Point(25, 25), new Dimension(15, 15)));

    $this->frame->add(new Label('remaining battery life:', new Point(5, 45), new Dimension(15, 15)));
    $this->frame->add($this->txtPercentLeft = new EditBox('', new Point(25, 45), new Dimension(15, 15)));

    $this->frame->add(new Label('remaining battery time:', new Point(5, 65), new Dimension(15, 15)));
    $this->frame->add($this->txtTimeLeft = new EditBox('', new Point(25, 65), new Dimension(15, 15)));

    $layout->layout($this->frame);

    $this->txtConnectedToAC->setReadOnly(TRUE);
    $this->txtIsCharging->setReadOnly(TRUE);
    $this->txtPercentLeft->setReadOnly(TRUE);
    $this->txtTimeLeft->setReadOnly(TRUE);
  }

  public function update(\BatteryMark\Model\BatteryState $currentState) {
    $this->txtConnectedToAC->setValue($currentState->getPowerStatus());
    $this->txtIsCharging->setValue($currentState->getBatteryStatus());
    $this->txtPercentLeft->setValue($currentState->getPercentRemaining());
    $this->txtTimeLeft->setValue($currentState->getTimeRemaining()); 
  }

  public function getFrame() {
    return $this->frame;
  }

  public function resizeBy(Dimension $delta) {
    return $this;
  }
}