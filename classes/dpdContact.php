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

class dpdContact {
  
  /**
   * @var string $first_name
   */
  public $first_name;
  /**
   * @var string $last_name
   */
  public $last_name;
  /**
   * @var string $phone
   */
  public $phone;
  /**
   * @var string $mobile
   */
  public $mobile;
  /**
   * @var string $email
   */
  public $email;
  /**
   * @var dpdLocation $location
   */
  public $location;
  /**
   * @var string $language
   */
  public $language;
  
  
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
