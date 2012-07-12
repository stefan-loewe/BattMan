<?php

namespace BatteryMark\Controls;

use \Woody\Components\Component;
use \Woody\Util\ImageResource;
use \Utils\Geom\Point;
use \Utils\Geom\Dimension;

class Image extends \Woody\Components\Controls\Image {
  public function __construct(ImageResource $imageResource, Point $topLeftCorner, Dimension $dimension, array $stati) {
    parent::__construct($imageResource, $topLeftCorner, $dimension);

    $this->stati = $stati;
  }


  protected function setImage() {
    $bitmap = $this->imageResource->getBitmap();

    $width = $this->imageResource->getDimension()->width;
    $height = $this->imageResource->getDimension()->height;

    wb_draw_rect($bitmap, 0, 0, $width, $height, 0xFFF0F0, TRUE);

    for($x = 0; $x < $width; $x += 20) {
        wb_draw_line($bitmap, $x, 0, $x, $height, 0xFF8050, FALSE);
    }
var_dump($this->stati);
    foreach($this->stati as $index => $status) {
        if(isset($this->stati[$index + 1]))
            wb_draw_line($bitmap,
                ($width / count($this->stati)) * $index,
                $height - $height * ($status / 100),
                ($width / count($this->stati)) * ($index + 1),
                $height - $height * ($this->stati[$index + 1] / 100),
                0x0000FF,
                FALSE);
    }

    wb_set_image($this->controlID, $bitmap);
    wb_destroy_image($bitmap);

    return $this;
  }
}