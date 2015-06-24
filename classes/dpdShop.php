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

class dpdShop {
  
  /**
   * Possible services
   * By adding up the values (eg $this->services = $this::pickup + $this::return)
   * we can use binary operator & to filter the list very easily
   */
  const pickup   = 0x01;
  const retour   = 0x02; //Return can't be used, would do funny things in php.
  const cod      = 0x04;
  const ident    = 0x08;
  const swap     = 0x10;
  const offline  = 0x20;
  const online   = 0x40;
  
  /**
   * Id used to identify this shop in the order
   * @val string $id
   */
  public $id;
  /**
   * Defines if the shop is active (can be selected)
   * @val boolean $active
   */
  public $active;
  /**
   * (Company) name of the shop.
   * @val string $name
   */
  public $name;
  /**
   * Location of the shop
   * @val dpdLocation $location
   */
  public $location;
  /**
   * Extra description/info about the shop
   * @val string $info
   */
  public $info;
  /**
   * Bussiness hours of the shop
   * @val dpdShopHours $bussiness_hours
   */
  public $business_hours;
  /**
   * Logos used on the locator.
   * @val dpdShopLogo $logo
   */
  public $logo;
  /**
   * An image of the shop
   * @val string $image_url
   */
  public $image_url;
  
  /**
   * A representation of all the (dpd) services this shop offers
   * @val int $services
   */
  private $services;
  
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
  
  /**
   * Add a single service to the shop.
   * @param int $service The constants defined above.
   * @return int
   */
  public function addService($service) {
    // Only add it when it isn't already set.
    if(!$this->hasService($service)) {
      $this->services =+ $service;
    }
    return $this->services;
  }
  /** 
   * Add multiple services to the shop
   * @param int[] $services An array of the constants defined above.
   * @return int
   */
  public function addServices(array $services) {
    foreach($services as $service) {
      $this->addService($service);
    }
    return $this->services;
  }
  
  /**
   * Remove a single service from the shop.
   * @param int $service The constants defined above.
   * @return int
   */
  public function delService($service) {
    if($this->hasService($service)) {
      $this->services =- $service;
    }
    return $this->services;
  }
  /** 
   * Remove multiple services from the shop
   * @param int[] $services An array of the constants defined above.
   * @return int
   */
  public function delServices($services) {
    foreach($services as $service) {
      $this->delService($service);
    }
    return $this->services;
  }
  
  /** 
   * See if the shop provides a certain service
   * @param int $services One of the constants defined above.
   * @return boolean
   */
  public function hasService($service) {
    $result = ($this->services & $service) ? true : false;
    return $result;
  }
  /**
   * Check if the shop provides all the listed services.
   * (loop stops if one of the services isn't provided)
   * @param int[] $services
   * @return boolean
   */
  public function hasServices($services) {
    foreach($services as $service){
      if(!$this->hasService($service)){
        return false;
      }
    }
    return true;
  }

}
