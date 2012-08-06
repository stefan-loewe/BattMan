<?php

namespace BatteryMark\View;

use \BatteryMark\Model\BatteryState;
use \Woody\Components\Controls\EditArea;
use \Woody\Components\Controls\Frame;
use \Woody\Layouts\GridLayout;
use \Utils\Geom\Dimension;
use \Utils\Geom\Point;

class LogView {

  private $frame        = null;
  private $frmDimension = null;
  
  private $txtLog       = null;

  public function __construct() {
    $this->frmDimension = new Dimension(300, 470);

    $this->frame        = new Frame('', new Point(5, 165), $this->frmDimension);
  }

  public function initialize() {
    //$this->frame->setLayout($layout = new GridLayout(1, 1, 0, 0));
remove purging ... keep all data ... maybe let that bemodel ..? or both
    $this->frame->add($this->txtLog = new EditArea('', new Point(10, 10), new Dimension(280, 450)));
    //$layout->layout($this->frame);

    $this->txtLog->setReadOnly(TRUE);
  }

  public function update(BatteryState $currentState) {
    $currentLog = $this->txtLog->getValue();
    
    $entry = (($currentLog === null) ? '' : PHP_EOL).date('H:i:s');
    $entry .= ', '.$currentState->getPowerStatus();
    $entry .= ', '.$currentState->getBatteryStatus();
    $entry .= ', '.$currentState->getPercentRemaining().'%';
    $entry .= ', '.$currentState->getTimeRemaining();
    
    $this->txtLog->setValue($currentLog.$entry);
  }

  public function getFrame() {
    return $this->frame;
  }

  public function resizeBy(Dimension $delta) {
    var_dump($delta->height);
    $this->frame->resizeBy(new Dimension(0, $delta->height));
    return $this;
  }
}