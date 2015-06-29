<?php
/**
* This interface will enable the dpd libaries to cache some data.
* The class generated from this interface is platform dependend.
* If no cache is possible create a class returning false for all functions.
* 
* @author     Michiel Van Gucht
* @version    0.0.1
* @copyright  2015 Michiel Van Gucht
* @license    LGPL
*/

interface dpdCacheInterface {
  /**
  * Get a previously cached value back.
  * 
  * @param string $name
  * @return mixed
  */
  public function __get($name);
  
  /**
  * Cache a specific value with a name
  * 
  * @param string $name
  * @param mixed $object
  * @return mixed
  */
  public function __set($name, $object);
}
