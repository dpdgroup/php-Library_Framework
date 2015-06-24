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

class dpdShopLogo {
  
  /**
   * Logo url when shop is active
   * @var string $active
   */
  public $active;
  /**
   * Logo url when shop is inactive
   * @var string $inactive
   */
  public $inactive;
  /**
   * Logo shadow url
   * @var string $shadow
   */
  public $shadow;
  
  /**
   * @param array $data
   * @return dpdShopLogo
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
