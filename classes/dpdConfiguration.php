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

class dpdConfiguration {
  // @todo Add fieldset support(?)
  
  /**
   * Human readable text.
   * @var string $label
   */
  public $label;
  /**
   * Name that will be used to identify the field.
   * Do not use spaces, perferable all lower caps with _ if needed
   * @var string $name
   */
  public $name;
  /**
   * The input type (text, password, radio, ...)
   * @var string $type
   */
  public $type;
  /**
   * The value if type is an option
   * @var string $value
   */
  public $value;
  /**
   * Children
   * @var array $children
   */
  public $children;
  /**
   * A validation function (anonymous function perfered)
   * @var function $validate
   */
  public $validate;
  
  /**
   * @param array $data
   * @return dpdConfiguration
   */
  public function __construct($data){
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
