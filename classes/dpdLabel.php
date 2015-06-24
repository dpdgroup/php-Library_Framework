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

class dpdLabel {

  const pdf = 1;
  const zpl = 2;
  const epl = 3;
  const svg = 4;
  const png = 5;
  const jpg = 6;
  
  /**
   * Human readable text.
   * @var string $label
   */
  public $number;
  /**
   * A binary representation of the label
   * @var string $binary
   */
  public $binary;
  
  /**
   * @param array $data
   * @return dpdLabel
   */
  public function __construct(array $data = array()){
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
