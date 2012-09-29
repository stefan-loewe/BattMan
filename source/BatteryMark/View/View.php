<?php

namespace BatteryMark\View;

use \BatteryMark\Model\Model;
use \Woody\Components\Controls\Frame;
use \Utils\Geom\Point;
use \Utils\Geom\Dimension;

abstract class View {

  protected $frame = null;

  public function __construct(Point $topLeftCorner, Dimension $dimension) {
    $this->frame = new Frame('', $topLeftCorner, $dimension);
  }

  abstract public function initialize();

  abstract public function update(Model $model);

  public function getFrame() {
    return $this->frame;
  }

  public function resizeBy(Dimension $delta) {
    return $this;
  }
}