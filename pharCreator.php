<?php

/**
 * This function collects files from the given source directory, and add the files to an array, where the key is the
 * same as source file name, but "moved" from the source directory to the target directory, while the value really
 * points to the source file.
 *
 * @param string $sourceDirectory the directory to search for files, without trailing slash
 * @param string $targetDirectory the directory to which the the source files should be "moved", without trailing slash
 * @param array $files the collection where to add the files
 * @return array $files the collection where the files were added
 */
function collectFiles($sourceDirectory, $targetDirectory, array $files) {
  $directoryIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDirectory));
  foreach($directoryIterator as $sourceFile) {
    if(strpos($sourceFile, '\\.svn\\') !== FALSE
      || strpos($sourceFile, '\\test\\') !== FALSE
      || strpos($sourceFile, '\\build\\') !== FALSE) {
      continue;
    }
    $files[$targetDirectory.str_replace($sourceDirectory, '', $sourceFile->getPathname())] = $sourceFile->getPathname();
  }

  return $files;
}

try {

  if(file_exists('BattMan.phar')) {
    unlink('BattMan.phar');
  }

  // collect the files from the woody and BatteryMark directory, and put them in Woody adnd BatteryMark directory
  // in the phar archive
  if(false) {
    $files = array();
    $files = collectFiles('D:\\workspace\\programming\\PHP\\woody', 'Woody', $files);
    $files = collectFiles('D:\\workspace\\programming\\PHP\\BatteryMark', 'BatteryMark', $files);

    $iterator = new ArrayIterator($files);
    $baseDir  = null;
  }

  // collect all the files from one directory (needs manual svn export before)
  else if(false) {
    $baseDir  = 'D:\\workspace\\programming\\PHP\\BM';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
  }
  
  else {
    
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\EventFactory.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Utils\DataStructures\RingBuffer.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Utils\Geom\Dimension.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Utils\HTTP\HttpGetRequest.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Utils\HTTP\HttpRequest.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Component.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Windows\AbstractWindow.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\System\WindowConstraints.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\WindowResizeListener.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Controls\Frame.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Container.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Layouts\GridLayout.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Layouts\Layout.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\WindowCloseAdapter.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\WindowCloseListener.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\ActionListener.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\FocusListener.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\KeyListener.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\MouseListener.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Utils\Geom\Point.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\IComponent.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Controls\Actionable.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Controls\Control.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Controls\Tab.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Timer\Timer.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\WinBinderException.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Timer\TimerAlreadyRunningException.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Timer\TimerNotRunningException.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\MouseEvent.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\Event.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Utils\Common\ValueObject.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\FocusEvent.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\KeyEvent.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\ActionEvent.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\WindowResizeEvent.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\WindowCloseEvent.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\EventInfo.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Utils\Autoload\Autoloader.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Utils\Autoload\SourceFileNotFoundException.inc';
$files[] = 'C:\workspace\programming\PHP\BatteryMark\source\BatteryMark\App\BatteryMarkApplication.inc';
$files[] = 'C:\workspace\programming\PHP\BatteryMark\source\BatteryMark\Model\BatteryState.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Util\WinApi\WinApi.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Util\WinApi\Structure.inc';
$files[] = 'C:\workspace\programming\PHP\BatteryMark\source\BatteryMark\Model\Model.inc';
$files[] = 'C:\workspace\programming\PHP\BatteryMark\source\BatteryMark\Model\SystemPowerStatus.inc';
$files[] = 'C:\workspace\programming\PHP\BatteryMark\source\BatteryMark\View\TextView.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Controls\EditBox.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Controls\EditField.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Controls\Label.inc';
$files[] = 'C:\workspace\programming\PHP\BatteryMark\source\BatteryMark\View\View.inc';
$files[] = 'C:\workspace\programming\PHP\BatteryMark\source\BatteryMark\View\GraphView.inc';
$files[] = 'C:\workspace\programming\PHP\BatteryMark\source\BatteryMark\Components\Timer\RunOnceTimer.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Controls\Image.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Util\Image\ImageResource.inc';
$files[] = 'C:\workspace\programming\PHP\BatteryMark\source\BatteryMark\View\ControlView.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Controls\Checkbox.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\ActionAdapter.inc';
$files[] = 'C:\workspace\programming\PHP\BatteryMark\source\BatteryMark\View\LogView.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Controls\EditArea.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\App\Application.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Windows\ResizableWindow.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Components\Controls\ProgressBar.inc';
$files[] = 'C:\workspace\programming\PHP\woody\source\Woody\Event\WindowResizeAdapter.inc';

$sourceFiles = array();
    foreach($files as $file) {
      $path = str_replace('C:\workspace\programming\PHP\woody', 'Woody', $file);
      $path = str_replace('C:\workspace\programming\PHP\BatteryMark', 'BatteryMark', $path);
      echo "\n".$path;
      $sourceFiles[$path] = $file;
    }
    
    $sourceFiles['Woody\lib\winbinder.php'] = 'C:\workspace\programming\PHP\woody\lib\winbinder.php';
    $sourceFiles['Woody\lib\wb_windows.inc.php'] = 'C:\workspace\programming\PHP\woody\lib\wb_windows.inc.php';
    $sourceFiles['Woody\lib\wb_resources.inc.php'] = 'C:\workspace\programming\PHP\woody\lib\wb_resources.inc.php';
    $sourceFiles['Woody\lib\wb_generic.inc.php'] = 'C:\workspace\programming\PHP\woody\lib\wb_generic.inc.php';
    $sourceFiles['Woody\lib\fi\freeimage.inc.php'] = 'C:\workspace\programming\PHP\woody\lib\fi\freeimage.inc.php';
    
    $sourceFiles['Woody\source\Woody\Util\WinApi\Types\Byte.inc'] = 'C:\workspace\programming\PHP\woody\source\Woody\Util\WinApi\Types\Byte.inc';
    $sourceFiles['Woody\source\Woody\Util\WinApi\Types\Dword.inc'] = 'C:\workspace\programming\PHP\woody\source\Woody\Util\WinApi\Types\Dword.inc';
    var_dump($sourceFiles);
    
    
    $iterator = new ArrayIterator($sourceFiles);
    $baseDir  = null;
  }
  
  $phar = new Phar('BattMan.phar', 0, 'BattMan.phar');
  $phar->buildFromIterator($iterator, $baseDir);

  $phar->setStub('<?php
  error_reporting(E_ALL | E_STRICT);

  Phar::mapPhar(\'BattMan.phar\');

  require_once \'phar://BattMan.phar/Woody/lib/winbinder.php\';
  require_once \'phar://BattMan.phar/Woody/lib/fi/freeimage.inc.php\';
  require_once \'phar://BattMan.phar/Woody/source/Utils/Autoload/Autoloader.inc\';

  $woodyAutoloader = new \Utils\Autoload\Autoloader(\'phar://BattMan.phar/Woody/source/\');
  spl_autoload_register(array($woodyAutoloader, \'autoload\'));

  $appAutoloader = new \Utils\Autoload\Autoloader(\'phar://BattMan.phar/BatteryMark/source/\');
  spl_autoload_register(array($appAutoloader, \'autoload\'));

  function globalWinBinderEventHandler($windowID, $id, $controlID = 0, $type = 0, $property = 0) {
    \Woody\Event\EventFactory::createEvent(new \Woody\Event\EventInfo($windowID, $id, $controlID, $type, $property));
  }

  $app = new \BatteryMark\App\BatteryMarkApplication();

  $app->start();

  __HALT_COMPILER();
  ');
}
catch (Exception $e) {
    echo 'Write operations failed on phar: ', $e;
}
