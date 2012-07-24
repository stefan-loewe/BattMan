<?php

namespace BatteryMark\View;

class GraphView {

  public function __construct() {
    $this->frame = new Frame('', new Point(305, 5), new Dimension(700, 600));

    $this->initGraphImage($this->getGraphBitmap());
  }

  private function initGraphImage($bitmap) {
    $dim = $this->graphView->getDimension();
    $this->imgLoadGraph = new Image($bitmap, new Point(5, 15), new Dimension($dim->width - 10, $dim->height - 20));
    $this->graphView->add($this->imgLoadGraph);
  }

  private function getGraphBitmap() {
    $dim = $this->graphView->getDimension();
    $bitmap = ImageResource::create(new Dimension($dim->width - 10, $dim->height - 20));
    $bitmap->drawRectangle(new Point(0, 0), new Dimension($dim->width - 10, $dim->height - 20), 0xFFFFFF, TRUE, null, null);

    for($x = 0; $x < $this->graphView->getDimension()->width; $x += 10) {
      $bitmap->drawLine(new Point($x, 0), new Point($x, $dim->height - 20), 0xFFC0A0, FALSE, null, null, null);
    }

    return $bitmap;
  }

  private function update($res) {
    foreach($this->stati as $index => $status) {
      if(isset($this->stati[$index + 1])) {
        $width = $this->imgLoadGraph->getDimension()->width / (count($this->stati) + 1);

        $height = $this->imgLoadGraph->getDimension()->height;

        $source = new Point($index * $width, $height - $height * ($status / 100));
        $target = new Point(($index + 1) * $width, $height - $height * ($this->stati[$index + 1] / 100));
        $res->drawLine($source,
          $target,
          0x0000FF,
          1,
          null,
          null,
          null);
      }
    }

    return $res;
  }
}