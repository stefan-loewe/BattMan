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
  private $txtTimeOnBattery = null;

  public function __construct(Point $topLeftCorner, Dimension $dimension) {
    parent::__construct($topLeftCorner, $dimension);
  }

  public function initialize() {
    $this->frame->setLayout($layout = new GridLayout(5, 2, 0, 0));

    $this->frame->add(new Label('connected to power:', new Point(5, 5), new Dimension(15, 15)));
    $this->frame->add($this->txtConnectedToAC = new EditBox('', new Point(25, 5), new Dimension(15, 15)));

    $this->frame->add(new Label('battery being charged:', new Point(5, 25), new Dimension(15, 15)));
    $this->frame->add($this->txtIsCharging = new EditBox('', new Point(25, 25), new Dimension(15, 15)));

    $this->frame->add(new Label('remaining battery life:', new Point(5, 45), new Dimension(15, 15)));
    $this->frame->add($this->txtPercentLeft = new EditBox('', new Point(25, 45), new Dimension(15, 15)));

    $this->frame->add(new Label('remaining battery time:', new Point(5, 65), new Dimension(15, 15)));
    $this->frame->add($this->txtTimeLeft = new EditBox('', new Point(25, 65), new Dimension(15, 15)));

    $this->frame->add(new Label('time running on battery:', new Point(5, 65), new Dimension(15, 15)));
    $this->frame->add($this->txtTimeOnBattery = new EditBox('', new Point(25, 65), new Dimension(15, 15)));

    $layout->layout($this->frame);

    $this->txtConnectedToAC->setReadOnly(TRUE);
    $this->txtIsCharging->setReadOnly(TRUE);
    $this->txtPercentLeft->setReadOnly(TRUE);
    $this->txtTimeLeft->setReadOnly(TRUE);
    $this->txtTimeOnBattery->setReadOnly(TRUE);
  }

  public function update(Model $currentState) {
    $this->txtConnectedToAC->setValue($currentState->getPowerStatus());
    $this->txtIsCharging->setValue($currentState->getBatteryStatus());
    $this->txtPercentLeft->setValue($currentState->getPercentRemaining());
    $this->txtTimeLeft->setValue($currentState->getTimeRemaining());

    $date = new \DateTime();
    $date->add(new \DateInterval('PT'.($currentState->getTimeOnBattery()).'S'));
    $this->txtTimeOnBattery->setValue($date->diff(new \DateTime())->format('%H:%I:%S'));
/*
    if($currentState->getPowerStatus() === 'offline') {
      $timeOnBattery = $this->txtTimeOnBattery->getValue();
      if($timeOnBattery === null) {
        $timeOnBattery = -1;
      }

      $date = new \DateTime();
      $date->add(new \DateInterval('PT'.($timeOnBattery + 1).'S'));

      $this->txtTimeOnBattery->setValue($date->diff(new \DateTime())->format('%H:%I:%S'));
    }
*/
  }
}