<?php

namespace ws\loewe\BattMan\View;

use ws\loewe\BattMan\App\BattManApplication;
use ws\loewe\BattMan\Model\Model;
use ws\loewe\Utils\Geom\Dimension;
use ws\loewe\Utils\Geom\Point;
use ws\loewe\Woody\Components\Controls\Checkbox;
use ws\loewe\Woody\Components\Controls\EditArea;
use ws\loewe\Woody\Components\Controls\Label;
use ws\loewe\Woody\Dialog\FileSystem\FileSaveDialog;
use ws\loewe\Woody\Event\ActionAdapter;

class LogView extends View  {
  /**
   * the label of the checkbox for file logging
   *
   * @var Label
   */
  private $lblDoLogToFile = null;

  /**
   * the checkbox for file logging
   *
   * @var Checkbox
   */
  private $chkDoLogToFile = null;

  /**
   * the text area where to log is shown
   *
   * @var EditArea
   */
  private $txtLog         = null;

  /**
   * path to the file where to log to
   *
   * @var string
   */
  private $logFile        = null;

  public function __construct(Point $topLeftCorner, Dimension $dimension) {
    parent::__construct($topLeftCorner, $dimension);
  }

  public function initialize() {
    $this->frame->add($this->lblDoLogToFile = new Label('log to file:', Point::createInstance(10, 15), Dimension::createInstance(100, 15)));
    $this->frame->add($this->chkDoLogToFile = new Checkbox(0, Point::createInstance(152, 10), Dimension::createInstance(25, 25)));

    $this->frame->add($this->txtLog = new EditArea('', Point::createInstance(10, 40), Dimension::createInstance(280, 240)));
    $this->txtLog->setReadOnly(TRUE);

    $this->chkDoLogToFile->addActionListener(new ActionAdapter(function($event) {
      if(!$this->chkDoLogToFile->isChecked()) {
        return;
      }

      $dialog = new FileSaveDialog('Please enter the file name where to store the log.');
      $dialog->open();

      // set log file if user provided a file via the dialog
      if(($selection = $dialog->getSelection()) != null) {
        $this->logFile = $selection;
      }

      // with no file selected, reset checkbox to unchecked state
      else {
        $this->chkDoLogToFile->setChecked(FALSE);
      }
    }));
  }

  public function update(Model $currentState) {
    $currentLog = $this->txtLog->getValue();

    $entry = (($currentLog === null) ? '' : PHP_EOL).date('H:i:s');
    $entry .= ', '.$currentState->getPowerStatus();
    $entry .= ', '.$currentState->getBatteryStatus();
    $entry .= ', '.$currentState->getPercentRemaining().'%';
    $entry .= ', '.BattManApplication::formatSeconds($currentState->getTimeRemaining());
    $entry .= ', '.BattManApplication::formatSeconds($currentState->getTimeOnBattery());

    $this->txtLog->setValue(null);
    $this->txtLog->setValue($currentLog.$entry);

    if($this->chkDoLogToFile->isChecked() && $this->logFile != null) {
      file_put_contents($this->logFile, trim($entry).PHP_EOL, FILE_APPEND);
    }

    unset($currentLog);

    unset($entry);
  }

  public function resizeBy(Dimension $delta) {
    // no grid layout, so we manually resize the frame and the text area where to the log is shown
    $this->frame->resizeBy(Dimension::createInstance(0, $delta->height));
    $this->txtLog->resizeBy(Dimension::createInstance(0, $delta->height));

    return $this;
  }
}