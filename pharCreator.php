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
  if(TRUE) {
    $files = array();
    $files = collectFiles('D:\\workspace\\programming\\PHP\\woody', 'Woody', $files);
    $files = collectFiles('D:\\workspace\\programming\\PHP\\BatteryMark', 'BatteryMark', $files);

    $iterator = new ArrayIterator($files);
    $baseDir  = null;
  }

  // collect all the files from one directory (needs manual svn export before)
  else {
    $baseDir  = 'D:\\workspace\\programming\\PHP\\BM';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
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
    \Woody\Event\EventFactory::createEvent($windowID, $id, $controlID, $type, $property);
  }

  $app = new \BatteryMark\App\BatteryMarkApplication();

  $app->start();

  __HALT_COMPILER();
  ');
}
catch (Exception $e) {
    echo 'Write operations failed on phar: ', $e;
}
