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

class dpdEvent {

  const error      = 0;
  const sent       = 1;
  const transit    = 2;
  const delivery   = 3;
  const delivered  = 4;

  /**
   * The time of the event
   * @var int $time
   */
  public $time;
  
  /**
   * Status as defined above.
   */
  public $status;
  
  /**
   * Human readable description.
   */
  public $description;
  
  /**
   * @param array $data
   * @return dpdEvent
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
