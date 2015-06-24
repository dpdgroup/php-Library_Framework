<?php
/**
 *  
 *
 * 
 * @author     Michiel Van Gucht
 * @version    0.0.1
 * @copyright  2015 Michiel Van Gucht
 * @license    LGPL
 */

require_once("dpdException.php");

class dpdService {

  const classic     = 0;
  const parcelshop  = 1;
  const saturday    = 2;
  const sunday      = 3;
  const same_day    = 4;
  const express     = 5;
  
  /**
   * Parent ID (eg: libraryUID)
   * @var string $parentId
   */
  public $parentId;
  /**
   * Human readable text.
   * @var string $label
   */
  public $label;
  /**
   * Human readable description of the service.
   * @var string $description
   */
  public $description;
  /**
   * Name that will be used to identify the field (id).
   * Do not use spaces, perferable all lower caps with _ if needed
   * @var string $name
   */
  public $name;
  /**
   * The service type (see constants above)
   * @var string $type
   */
  public $type;
  /**
   * A validation function (anonymous function perfered)
   * Used to see if all necesarry data is at hand (will be used on an dpdOrder obj)
   * @var function $validate
   */
  public $validate;
  
  /**
   * @param array $data
   * @return dpdService
   */
  public function __construct($data = array()){
    if (is_array($data)){ 
      foreach($data as $key => $value){ 
        if(property_exists($this, $key)){ 
          $this->$key = $value; 
        } 
      } 
    } else {
      throw new dpdException("Can only take an array as input."); 
    }
  }

}
