<?php

namespace BatteryMark\Model;

class SystemPowerStatus {

  /**
   * @var Byte
   */ 
  public $ACLineStatus = null;
  
  /**
   * @var Byte
   */ 
  public $BatteryFlag = null;
  
  /**
   * @var Byte
   */ 
  public $BatteryLifePercent = null;
  
  /**
   * @var Byte
   */ 
  public $Reserved1 = null;
  
  /**
   * @var Dword
   */ 
  public $BatteryLifeTime = null;
  
  /**
   * @var Dword
   */ 
  public $BatteryFullLifeTime = null;
  
  public function __construct() {
  }
  
  public function pack() {
    $packString = '';
    
    foreach($this->getTypeMapping() as $property => $type) {
      $typeClassName  = "\\Woody\\Util\\WinAPI\\Types\\".$type;
      $typeClass      = new $typeClassName();
      
      $packString = $packString.pack($typeClass->getPackString(), $typeClass->getLength());
    }
    
    return $packString;
  }
  
  public function unpack($pack) {
    $unpackString = '';
    foreach($this->getTypeMapping() as $propertyName => $type) {$t = unpack('Ca/Cb/Cc/Cd/Le/Lf', $pack);
      
      $typeClassName  = "\\Woody\\Util\\WinApi\\Types\\".$type;
      $typeClass      = new $typeClassName();
      
      $unpackString = $unpackString.$typeClass->getPackString().$propertyName.'/';
    }
    
    $object = unpack($unpackString, $pack);
    
    foreach($object as $key => $value) {
      $this->$key = $value;
    }
  }
  
  private function getTypeMapping() {
    $reflectionClass  = new \ReflectionClass($this);
    $properties       = $reflectionClass->getProperties();

    $mapping = array();
    
    foreach ($properties as $property) {
      $mapping[$property->getName()] = $this->extractType($property->getDocComment());
    }
    
    return $mapping;
  }
  
  private function extractType($docComment) {
    $lines = explode("\n", $docComment);
    
    foreach($lines as $line) {
      if(strpos(trim($line), '* @var') === 0) {
        return trim(str_replace('* @var', '', $line));
      }
    }
    
    return 'void';
  }
}