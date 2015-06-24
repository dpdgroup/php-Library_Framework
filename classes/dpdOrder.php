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

class dpdOrder {

  /**
   * @var dpdContact $sender
   */
  public $reference;
  /**
   * @var dpdContact $sender
   */
  public $sender;
  /**
   * @var dpdContact $receiver
   */
  public $receiver;
  /**
   * @var dpdParcel[] $parcels
   */
  public $parcels;
  /**
   * @var dpdService $service
   */
  public $service;
  /**
   * @var dpdShop $shop
   */
  public $shop;
  
  
  /**
   * @param array $data
   * @return dpdConfiguration
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
