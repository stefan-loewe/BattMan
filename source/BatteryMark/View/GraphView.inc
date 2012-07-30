<?php

namespace BatteryMark\View;

use \Woody\Components\Controls\Frame;
use \Woody\Components\Controls\Image;
use \Woody\Util\ImageResource;
use \Utils\Geom\Point;
use \Utils\Geom\Dimension;

class GraphView {

  private $frame        = null;

  private $frmDimension = null;

  private $imgGraph     = null;

  private $imgDimension = null;

  private $states       = null;

  public function __construct() {
    $this->frmDimension = new Dimension(700, 600);
    $this->imgInset     = new Dimension(-10, -20);
    $this->imgDimension = $this->frmDimension->resizeBy($this->imgInset);

    $this->frame        = new Frame('', new Point(305, 5), $this->frmDimension);
  }

  public function initialize() {
    $this->initializeGraphImage($this->initializeBitmap());

    $this->states = new \ArrayObject();
  }

  private function initializeGraphImage($bitmap) {
    $this->imgGraph = new Image($bitmap, new Point(5, 15), $this->frmDimension->resizeBy($this->imgInset));

    $this->frame->add($this->imgGraph);
  }

  private function initializeBitmap() {
    $bitmap = ImageResource::create($this->frmDimension->resizeBy($this->imgInset));
    $bitmap->drawRectangle(new Point(0, 0), $this->frmDimension->resizeBy($this->imgInset), 0xFFFFFF);

    for($x = 0; $x < $this->frame->getDimension()->width; $x += 10) {
      $source = new Point($x, 0);
      $target = new Point($x, $this->frmDimension->height - $this->imgInset->height);
      $bitmap->drawLine($source, $target, 0xFFC0A0);
    }

    return $bitmap;
  }

  public function update(\BatteryMark\Model\BatteryState $currentState) {
    $this->purge();

    //$this->states[] = mt_rand(0, 100);
    $this->states[] = $currentState;

    $bitmap         = $this->initializeBitmap();

    $imageDimension = $this->frmDimension->resizeBy($this->imgInset);
    $stepWidth      = $imageDimension->width / (count($this->states) + 1);
    $stepHeight     = $imageDimension->height / 100;

    foreach($this->states as $index => $currentState) {
      if($index > 0) {
        //$source = new Point(($index - 1) * $stepWidth, $stepHeight * (100 - $previousState));
        //$target = new Point($index * $stepWidth, $stepHeight * (100 - $currentState));

        $source = new Point(($index - 1) * $stepWidth, $stepHeight * (100 - $previousState->getPercentRemaining()));
        $target = new Point($index * $stepWidth, $stepHeight * (100 - $currentState->getPercentRemaining()));

        $bitmap->drawLine($source, $target, 0x0000FF, 1);
      }

      $previousState = $currentState;
    }

    $this->frame->remove($this->imgGraph);
    $this->imgGraph = new Image($bitmap, new Point(5, 15), $imageDimension);
    $this->frame->add($this->imgGraph);
  }

  public function getFrame() {
    return $this->frame;
  }

  private function purge() {
    if($this->states->count() < 50) {
      return;
    }

    $temp = new \ArrayObject();

    foreach($this->states as $index => $entry) {
      if($index % 2 === 0) {
        $temp[] = $entry;
      }
    }

    $this->states = $temp;
  }

  public function resizeBy(Dimension $delta) {
    $this->imgGraph->hide();
    $this->frame->resizeBy(new Dimension($delta->width, $delta->height));

    $this->frmDimension = $this->frmDimension->resizeBy(new Dimension($delta->width, $delta->height));
    
    return $this;
  }
}