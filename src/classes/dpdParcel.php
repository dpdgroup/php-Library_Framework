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

class dpdParcel {
  
  /**
   * Weight in Kg
   * @var float $weight
   */
  public $weight;
  /**
   * length in cm
   * @var float $length
   */
  public $length;
  /**
   * width in cm
   * @var float $width
   */
  public $width;
  /**
   * height in cm
   * @var float $height
   */
  public $height;
  /**
   * value (for COD)
   * @var float $value
   */
  public $value;
  /**
   * valuta (eg: EUR)
   * @var string $valuta
   */
  public $valuta;
  
  
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
