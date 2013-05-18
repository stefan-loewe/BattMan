<?php

namespace ws\loewe\BattMan\View;

use \ws\loewe\BattMan\Model\Model;
use \ws\loewe\Woody\Components\Controls\Checkbox;
use \ws\loewe\Woody\Components\Controls\EditArea;
use \ws\loewe\Woody\Components\Controls\Label;
use \ws\loewe\Woody\Event\ActionAdapter;
use \ws\loewe\Utils\Geom\Dimension;
use \ws\loewe\Utils\Geom\Point;

class LogView extends View  {
  private $lblDoLogToFile = null;
  private $chkDoLogToFile = null;
  private $txtLog         = null;

  public function __construct(Point $topLeftCorner, Dimension $dimension) {
    parent::__construct($topLeftCorner, $dimension);
    
    if(file_exists('./battman.log')) {
      unlink('./battman.log');
    }
  }

  public function initialize() {
    $this->frame->add($this->lblDoLogToFile = new Label('log to file:', new Point(10, 15), new Dimension(15, 150)));
    $this->frame->add($this->chkDoLogToFile = new Checkbox(0, new Point(152, 10), new Dimension(25, 25)));
    
    $this->lblDoLogToFile->getDimension()->resizeTo(new Dimension(150, 150));
    
    $this->frame->add($this->txtLog = new EditArea('', new Point(10, 40), new Dimension(280, 240)));
    $this->txtLog->setReadOnly(TRUE);
    
    $this->chkDoLogToFile->addActionListener(new ActionAdapter(function($event) {
      if(!$event->getSource()->isChecked() && file_exists('./battman.log')) {
        unlink('./battman.log');
      }
    }));
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
    
    if($this->chkDoLogToFile->isChecked()) {
      file_put_contents('./battman.log', trim($entry).PHP_EOL, FILE_APPEND);
    }

    unset($currentLog);

    unset($entry);
  }

  public function resizeBy(Dimension $delta) {
    $this->frame->resizeBy(new Dimension(0, $delta->height));
    $this->txtLog->resizeBy(new Dimension(0, $delta->height));

    return $this;
  }
}