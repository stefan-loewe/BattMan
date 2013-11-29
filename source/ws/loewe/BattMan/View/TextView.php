<?php

namespace ws\loewe\BattMan\View;

use ws\loewe\BattMan\App\BattManApplication;
use ws\loewe\BattMan\Model\Model;
use ws\loewe\Utils\Geom\Dimension;
use ws\loewe\Utils\Geom\Point;
use ws\loewe\Woody\Components\Controls\EditBox;
use ws\loewe\Woody\Components\Controls\Label;
use ws\loewe\Woody\Layouts\GridLayout;

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
    $this->txtTimeLeft->setValue(BattManApplication::formatSeconds($currentState->getTimeRemaining()));
    $this->txtTimeOnBattery->setValue(BattManApplication::formatSeconds($currentState->getTimeOnBattery()));
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