<?php
/**
 * Main library, will dispatch commands to the others.
 * 
 * @author     Michiel Van Gucht
 * @version    0.0.1
 * @copyright  2015 Michiel Van Gucht
 * @license    LGPL
 */

defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
$dir_path = dirname(__FILE__);

foreach (glob($dir_path . DS . "classes" . DS . "*.php") as $filename)
{
  require_once($filename);
}

class dpdLibrary {
  
  /**
   * Get all the libraries that were uploaded to the server
   * @return array 
   */
  static function getLibraries() {
    $dir_path = dirname(__FILE__);
    $result = array();
    foreach (glob($dir_path . DS . "libraries" . DS . "*.php") as $file_path)
    {
      // Require the file just to be sure
      require_once($file_path);
      
      // Get the file name (without extension .php)
      $class_name = basename($file_path, ".php");
      
      // Save it  to the result
      $result[$class_name::UID] = $class_name;
    }
    return $result;
  }
  
  /**
   * Get the configuration fields required for each (or specific) libraries
   * @param array $libraries an array of library UIDs
   * @return array UID => dpdConfiguration[]
   */
  static function getConfiguration($libraries = false) {
    $selected = self::loadLibraries($libraries);
    $result = array();
    foreach($selected as $UID => $library_name) {
      $result[$UID] = $library_name::getConfiguration();
    }
    return $result;
  }
  
  /**
   * Get the services of each (or specific) library
   * @param array $libraries an array of library UIDs
   * @return array UID => dpdService[]
   */
  static function getServices($libraries = false) {
    $selected = self::loadLibraries($libraries);
    $result = array();
    foreach($selected as $UID => $library_name) {
      $result[$UID] = $library_name::getServices();
    }
    return $result;
  }
  
  /**
   * Simple construct
   * @param stdClass $config Contains all the values set by the configuration fields
   * @param dpdCache $cache A platform specific cache object.
   * @return dpdLibrary
   */
  public function __construct($config, dpdCache $cache) {
    $this->config = $config;
    $this->cache = $cache;
  }
  
  // Reminder: I didn't make this static because it needs the Config and Cache objects.
  /**
   * Get shops for all or specific libraries
   * @param int $limit Amount of shops returned (per library)
   * @param array $libraries array of libary UIDs
   * @param dpdService|array $service the service(s)
   * @result array[UID] => dpdShop[]
   */
  public function getShops(dpdLocation $location, $limit = 10, $services = null) {
    $result = array();
    
    $grouped_services;
    if($services == null) {
      $grouped_services = $this->getServices();
    } else {
      $grouped_services = array();
      foreach($services as $service) {
        $grouped_services[$service->parentId][] = $service;
      }
    }
    
    foreach($grouped_services as $UID => $services){
      $selected = $this->loadLibraries(array($UID));
      
      foreach($selected as $UID => $library_name) {
        $class = new $library_name($this->config, $this->cache);
        $result[$UID] = $class->getShops($location, $limit, $services);
      }
    }

    return $result;
  }
  
  /**
   * Get a label for a certain order
   * To define what Library is used we look at the parent UID of the dpdService object in the dpdOrder object
   * @param dpdOrder $order Order will be validated by the validate function of the dpdService object
   * @param $format The format of the label (dpdLabel constants)
   * @return dpdLabel
   */
  public function getLabel(dpdOrder $order, $format = dpdLabel::pdf) {
    $selected = $this->loadLibraries(array($order->service->parentId));
    $library_name = $selected[$order->service->parentId];
    $class = new $library_name($this->config, $this->cache);
    $result = $class->getLabel($order, $format);
    return $result;
  }
  
  /**
   * Get labels for multiple orders (mixed lirbary)
   * @param dpdOrder[] $orders
   * @param $format dpdLabel constants
   * @result dpdLabel[]
   */
  public function getLabels(array $orders, $format = dpdLabel::pdf) {
    $result = array();
    foreach ($orders as $order) {
      $label = $this->getLabel($order, $format);
      if($label) {
        $result[] = $label;
      }
    }
    return $result;
  }
  
  /**
   * Get information about an order
   * @param dpdLabel $label
   * @result dpdEvent[]
   */
  public function getInfo(dpdLabel $label) {
    $selected = $this->loadLibraries(array($label->parentId));
    $library_name = $selected[$label->parentId];
    $class = new $library_name($this->config, $this->cache);
    $result = $class->getInfo($label);
    return $result;
  }
  
  /**
   * Get information about multiple orders
   * Please do not over use this function, could stress the web services
   * @param dpdLabel[] $labels
   * @result array[dpdEvent[]]
   */
  public function getInfos(array $labels) {
    $result = array();
    foreach ($labels as $label) {
      $info = $this->getInfo($label);
      if($info) {
        $result[] = $info;
      }
    }
    return $result;
  }
  
  /**
   * Load the selected libaries
   * @param array $libaries An array of library UIDs
   * @return array[ UID => classname ]
   */
  private static function loadLibraries($libraries = false) {
    $selected = array();
    if(!$libraries) {
      $selected = self::getLibraries();
    }
    if(is_array($libraries)) {
      $all_libraries = self::getLibraries();
      foreach($libraries as $UID) {
        $selected[$UID] = $all_libraries[$UID];
      }
    }
    $dir_path = dirname(__FILE__);
    foreach($selected as $library_name) {
      require_once($dir_path . DS  . "libraries" . DS . $library_name . ".php");
    }
    return $selected;
  }
}