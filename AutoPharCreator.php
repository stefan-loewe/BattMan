<?php

require '../php_parser/lib/bootstrap.php';

define('APP_ROOT_FOLDER', realpath(__DIR__).'\\');
define('APP_SOURCE_FOLDER', realpath(__DIR__.'\\source').'\\');

define('WOODY_INSTALLATION_FOLDER', str_replace('\\', '/', realpath(__DIR__.'\..\\woody')));
define('WOODY_SOURCE_FOLDER', WOODY_INSTALLATION_FOLDER.'/source');


require_once WOODY_INSTALLATION_FOLDER.'/lib/winbinder.php';
require_once WOODY_INSTALLATION_FOLDER.'/lib/fi/freeimage.inc.php';
require_once WOODY_SOURCE_FOLDER.'/Utils/Autoload/Autoloader.inc';

$woodyAutoloader = new \Utils\Autoload\Autoloader(WOODY_SOURCE_FOLDER.'/');
spl_autoload_register(array($woodyAutoloader, 'autoload'));

$appAutoloader = new \Utils\Autoload\Autoloader(APP_SOURCE_FOLDER);
spl_autoload_register(array($appAutoloader, 'autoload'));

class ClassNameCollector extends PHPParser_NodeVisitorAbstract
{
    public static $allNames = array();
    public $names = null;
    
    public function __construct() {
      $this->names = new \ArrayObject();
    }
  
    public function leaveNode(PHPParser_Node $node) {
        
      if($node instanceof PHPParser_Node_Name) {
        if(!array_key_exists($node->toString(), self::$allNames)) {
          $this->names[] = $node->toString();
          self::$allNames[$node->toString()] = true;
        }
        
      } elseif ($node instanceof PHPParser_Node_Stmt_Class
                  || $node instanceof PHPParser_Node_Stmt_Interface) {
        
        if(!array_key_exists($node->namespacedName->toString(), self::$allNames)) {
          $this->names[] = $node->namespacedName->toString();
          self::$allNames[$node->namespacedName->toString()] = true;
        }
      }
    }
}

function getClasses($sourceFile, $alreadyVisited) {
  $parser = new PHPParser_Parser(new PHPParser_Lexer());

  $traverser     = new PHPParser_NodeTraverser();
  $traverser->addVisitor($nameCollector = new ClassNameCollector()); // we will need resolved names
  $traverser->addVisitor(new PHPParser_NodeVisitor_NameResolver()); // we will need resolved names
  
  try {
      $stmts = $parser->parse(file_get_contents($sourceFile));


      $stmts = $traverser->traverse($stmts);

  } catch (PHPParser_Error $e) {
      echo 'Parse Error: ', $e->getMessage();
  }

  foreach($nameCollector->names as $classNameCandidate) {
    spl_autoload_call($classNameCandidate);
    try {
      $reflector = new ReflectionClass($classNameCandidate);
      echo "\n".'$files[] = \''.$reflector->getFileName().'\';';
      if(strlen($reflector->getFileName()) > 0 && !array_key_exists($reflector->getFileName(), $alreadyVisited)) {
        $alreadyVisited[$reflector->getFileName()] = true;
        getClasses($reflector->getFileName(), $alreadyVisited);
      }
    }
    catch(\ReflectionException $re) {
      //echo "\n".$classNameCandidate.' does not reference a class';
    }
  }
}
$alreadyVisited = array();
getClasses('./bootstrap.php', $alreadyVisited);

die;


class ClassLoader {
  private $bootstrapFile = null;
  
  private $files = null;
  
  public function __construct($bootstrapFile) {
    $this->bootstrapFile = $bootstrapFile;
  }
  
  public function getClassFiles() {
    $this->files = new \ArrayObject();
    
    $todo = new \SplStack();
    $todo->push($this->bootstrapFile);
    
    while($todo->count() > 0) {
      $currentFile = $todo->pop();
      
      $tokens = token_get_all(file_get_contents($currentFile));
      
      foreach($tokens as $token) {
        if(is_array($token))
          echo "\n".token_name($token[0]).' -> '.$token[1];
        
        if($token[0] === T_USE) {
          $this->parseClassNameInUseStatement();
        }
        else if($token[0] === T_DOUBLE_COLON) {
          $this->parseClassNameInStaticContext();
        }
        else if($token[0] === T_NEW) {
          $this->parseClassNameInDynamicContext();
        }
      }
    }
  }
}

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

$c = new ClassLoader('./bootstrap.php');
$c->getClassFiles();

die;
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
