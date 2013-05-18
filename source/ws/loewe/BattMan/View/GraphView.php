<?php

namespace ws\loewe\BattMan\View;

use \ws\loewe\BattMan\App\BattManApplication;
use \ws\loewe\BattMan\Components\Timer\RunOnceTimer;
use \ws\loewe\BattMan\Model\Model;
use \ws\loewe\Utils\Geom\Dimension;
use \ws\loewe\Utils\Geom\Point;
use \ws\loewe\Woody\Components\Controls\Image;
use \ws\loewe\Woody\Util\Image\ImageResource;

class GraphView extends View  {

  /**
   * the dimension of the frame
   *
   * @var Dimension
   */
  private $frmDimension = null;

  /**
   * the inset of the graph
   *
   * @var Dimension
   */
  private $imgInset = null;

  /**
   * the image holding th egraph
   *
   * @var Image
   */
  private $imgGraph     = null;
  
  /**
   * the collection of states
   *
   * @var ArrayObject 
   */
  private $states       = null;

  /**
   * the timer for repainting
   *
   * @var Timer
   */
  private $repaintTimer = null;

  public function __construct(Point $topLeftCorner, Dimension $dimension) {
    parent::__construct($topLeftCorner, $dimension);

    $this->frmDimension = $dimension;
    $this->imgInset     = new Dimension(-10, -20);
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
    return ImageResource::create($this->frmDimension->resizeBy($this->imgInset));
  }

  public function update(Model $currentState) {
    $imageDimension = $this->frmDimension->resizeBy($this->imgInset);

    if($imageDimension->width === 0 || $imageDimension->height === 0) {
      return;
    }

    // only add the current state if collection is empty or last element is not same as current state
    // the latter might happen when adding it repeatedly during resizing
    if($this->states->count() == 0 || $this->states[$this->states->count() - 1] != $currentState) {
      $this->states[] = $currentState;
    }

    $bitmap = $this->initializeBitmap();

    $this->drawBackground($bitmap)
      ->drawGrid($bitmap)
      ->drawTimeLines($bitmap, $imageDimension)
      ->drawChart($bitmap, $imageDimension);

    // force repaint of frame, image
    $this->frame->remove($this->imgGraph);
    $this->imgGraph = new Image($bitmap, new Point(5, 15), $imageDimension);
    $this->frame->add($this->imgGraph);
  }

  private function drawBackground($bitmap) {
    $bitmap->drawRectangle(new Point(0, 0), $this->frmDimension->resizeBy($this->imgInset), 0xFFFFFF);

    return $this;
  }

  private function drawGrid($bitmap) {
    for($x = 0; $x < $this->frame->getDimension()->width; $x += 10) {
      $source = new Point($x, 0);
      $target = new Point($x, $this->frmDimension->height - $this->imgInset->height);
      $bitmap->drawLine($source, $target, 0xFFC0A0);
    }

    return $this;
  }

  private function drawChart($bitmap, $imageDimension) {
    $stepWidth      = 1;//$imageDimension->width / (count($this->states) + 1);
    $stepHeight     = $imageDimension->height / 100;
    $increment      = ceil(count($this->states) / $imageDimension->width);

    $index = 1;
    for($i = 0; $i < count($this->states) - $increment; $i = $i + $increment) {
      $sourceState = $this->states[$i];
      $targetState = $this->states[$i + $increment];
      $source = new Point(($index - 1) * $stepWidth, $stepHeight * (100 - $sourceState->getPercentRemaining()));
      $target = new Point($index * $stepWidth, $stepHeight * (100 - $targetState->getPercentRemaining()));

      $bitmap->drawLine($source, $target, 0x0000FF, 1);
      $index++;
    }

    return $this;
  }

  private function drawTimeLines($bitmap, $imageDimension) {
    $increment = (ceil(count($this->states) / $imageDimension->width));

    for($i = 0; $i < 64; $i++) {
      $xOffset = (90 * $i) / $increment;

      $source = new Point($xOffset, 0);
      $target = new Point($xOffset, $this->frmDimension->height - $this->imgInset->height);
      $bitmap->drawLine($source, $target, 0xFFC0A0, 3);
    }

    return $this;
  }

  public function resizeBy(Dimension $delta) {
    $this->imgGraph->hide();
    $this->frame->resizeBy(new Dimension($delta->width, $delta->height));

    $this->frmDimension = $this->frmDimension->resizeBy(new Dimension($delta->width, $delta->height));

    if($this->repaintTimer !== null && $this->repaintTimer->isRunning()) {
      $this->repaintTimer->destroy();
    }

    $this->repaintTimer = new RunOnceTimer(
      function() {
        $this->update($this->states[$this->states->count() - 1]);
      },
      BattManApplication::getInstance()->getWindow(),
      500);
    $this->repaintTimer->start();

    return $this;
  }
}