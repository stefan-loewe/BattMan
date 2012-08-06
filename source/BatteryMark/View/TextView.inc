<?php

namespace BatteryMark\View;

use \BatteryMark\Model\Model;
use \Utils\Geom\Dimension;
use \Utils\Geom\Point;
use \Woody\Components\Controls\EditBox;
use \Woody\Components\Controls\Label;
use \Woody\Layouts\GridLayout;

class TextView extends View {
  private $txtConnectedToAC = null;
  private $txtIsCharging    = null;
  private $txtPercentLeft   = null;
  private $txtTimeLeft      = null;

  public function __construct(Point $topLeftCorner, Dimension $dimension) {
    parent::__construct($topLeftCorner, $dimension);
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

  public function update(Model $currentState) {
    $this->txtConnectedToAC->setValue($currentState->getPowerStatus());
    $this->txtIsCharging->setValue($currentState->getBatteryStatus());
    $this->txtPercentLeft->setValue($currentState->getPercentRemaining());
    $this->txtTimeLeft->setValue($currentState->getTimeRemaining());
  }
}