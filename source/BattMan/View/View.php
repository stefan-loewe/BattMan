<?php

namespace ws\loewe\BattMan\View;

use \ws\loewe\BattMan\Model\Model;
use \ws\loewe\Woody\Components\Controls\Frame;
use \ws\loewe\Utils\Geom\Point;
use \ws\loewe\Utils\Geom\Dimension;

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