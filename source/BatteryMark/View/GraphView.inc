<?php

namespace BatteryMark\View;

use \BatteryMark\Model\Model;
use \Utils\Geom\Dimension;
use \Utils\Geom\Point;
use \Woody\Components\Controls\Image;
use \Woody\Util\ImageResource;

class GraphView extends View  {

  private $frmDimension = null;

  private $imgGraph     = null;

  private $imgDimension = null;

  private $states       = null;

  private $bitmap = null;

  public function __construct(Point $topLeftCorner, Dimension $dimension) {
    parent::__construct($topLeftCorner, $dimension);

    $this->frmDimension = $dimension;
    $this->imgInset     = new Dimension(-10, -20);
    $this->imgDimension = $this->frmDimension->resizeBy($this->imgInset);
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
    $this->bitmap = ImageResource::create($this->frmDimension->resizeBy($this->imgInset));
    $this->bitmap->drawRectangle(new Point(0, 0), $this->frmDimension->resizeBy($this->imgInset), 0xFFFFFF);

    for($x = 0; $x < $this->frame->getDimension()->width; $x += 10) {
      $source = new Point($x, 0);
      $target = new Point($x, $this->frmDimension->height - $this->imgInset->height);
      $this->bitmap->drawLine($source, $target, 0xFFC0A0);
    }

    return $this->bitmap;
  }

  public function update(Model $currentState) {
    $this->bitmap         = $this->initializeBitmap();

    $this->states[] = $currentState;

    $imageDimension = $this->frmDimension->resizeBy($this->imgInset);
    $stepWidth      = 1;//$imageDimension->width / (count($this->states) + 1);
    $stepHeight     = $imageDimension->height / 100;
    $increment      = ceil(count($this->states) / $imageDimension->width);

    $index = 1;
    for($i = 0; $i < count($this->states) - $increment; $i = $i + $increment) {
      $sourceState = $this->states[$i];
      $targetState = $this->states[$i + $increment];
      $source = new Point(($index - 1) * $stepWidth, $stepHeight * (100 - $sourceState->getPercentRemaining()));
      $target = new Point($index * $stepWidth, $stepHeight * (100 - $targetState->getPercentRemaining()));

      $this->bitmap->drawLine($source, $target, 0x0000FF, 1);
      $index++;
    }

    // force repaint of frame, image
    $this->frame->remove($this->imgGraph);
    $this->imgGraph = new Image($this->bitmap, new Point(5, 15), $imageDimension);
    $this->frame->add($this->imgGraph);
  }

  public function resizeBy(Dimension $delta) {
    $this->imgGraph->hide();
    $this->frame->resizeBy(new Dimension($delta->width, $delta->height));

    $this->frmDimension = $this->frmDimension->resizeBy(new Dimension($delta->width, $delta->height));

    return $this;
  }
}