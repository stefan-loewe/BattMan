<?php

namespace ws\loewe\BattMan\View;

use \ws\loewe\BattMan\Model\Model;
use \ws\loewe\Woody\Components\Controls\EditArea;
use \ws\loewe\Woody\Layouts\GridLayout;
use \ws\loewe\Utils\Geom\Dimension;
use \ws\loewe\Utils\Geom\Point;

class LogView extends View  {
  private $txtLog = null;

  public function __construct(Point $topLeftCorner, Dimension $dimension) {
    parent::__construct($topLeftCorner, $dimension);
  }

  public function initialize() {
    $this->frame->setLayout($layout = new GridLayout(1, 1, 0, 0));

    $this->frame->add($this->txtLog = new EditArea('', new Point(10, 10), new Dimension(5, 5)));

    $layout->layout($this->frame);

    $this->txtLog->setReadOnly(TRUE);
  }

  public function update(Model $currentState) {
    $currentLog = $this->txtLog->getValue();

    $entry = (($currentLog === null) ? '' : PHP_EOL).date('H:i:s');
    $entry .= ', '.$currentState->getPowerStatus();
    $entry .= ', '.$currentState->getBatteryStatus();
    $entry .= ', '.$currentState->getPercentRemaining().'%';
    $entry .= ', '.$currentState->getTimeRemaining();
    $entry .= ', '.$currentState->getTimeOnBattery();

    $this->txtLog->setValue(null);
    $this->txtLog->setValue($currentLog.$entry);

    unset($currentLog);

    unset($entry);
  }

  public function resizeBy(Dimension $delta) {
    $this->frame->resizeBy(new Dimension(0, $delta->height));

    $this->frame->getLayout()->layout($this->frame);

    return $this;
  }
}