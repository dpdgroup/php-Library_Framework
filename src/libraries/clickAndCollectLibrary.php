<?php
/**
* 
* 
* @author     Michiel Van Gucht
* @version    0.0.1
* @copyright  2015 Michiel Van Gucht
* @license    LGPL
*/

defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);

$dir_path = dirname(__FILE__);

require_once($dir_path . DS . ".." . DS . "interfaces" . DS . "dpdLibraryInterface.php");

class clickAndCollectLibrary implements dpdLibraryInterface{
  
  /**
   * Unique identifier for the class.
   */
  const UID = "CC";
  
  /**
   * @param stdObject $config The actual configuration.
   * @param dpdCache $cache A simple cache object to save and retreive data.
   * @return dpdLibraryInterface
   */
  public function __construct($config, dpdCache $cache){
    $this->config = $config;
    $this->cache = $cache;
  }
  
  /**
  * Get the configuration fields needed for the library/api to work.
  * eg: 
  *   Delicom API needs delisID, password
  *   Cloud services need different tokens.
  * These configuration fields will be show in the modules configuration
  * @return dpdConfiguration[]
  */
  static function getConfiguration() {
    $result = array();
    $result[] = new dpdConfiguration( array(
      "label" => "Shop List"
      ,"name" => "shop_list"
      ,"type" => "file"
      ,"validate" => function($var){return true;}
    ));
    return $result;
  }
  
  /**
  * Get the service that the shipper can use
  * eg: Classic, Predict, Pickup ...
  * These services will define what is visible in the checkout
  * @return dpdService[]
  */
  static function getServices(){
    $result = array();
    $result[] = new dpdService( array(
      "parentId" => self::UID
      ,"label" => "Click & Collect"
      ,"description" => "Can't be home? Pick up your parcel at one of our stores."
      ,"name" => "click_collect" 
      ,"type" => dpdService::parcelshop
      ,"validate" => function($order){return true;}
    ));
    return $result;
  }
  
  /**
  * Get a list of parcelshops close to a given location.
  * This function should use the address details or the geolocation from the dpdLocation object.
  * TIP: If possible map the address to geolocation for an optimal location lookup.
  * @param dpdLocation $location location to look up.
  * @param integer $limit the maximum amount of shops to return
  * @param dpdService[] $services This filters the result to certain services (defined by getServices)
  * @return (dpdShop[]|false)
  */
  public function getShops(dpdLocation $location, $limit = 10, array $services = null) {
    $result = array();
    
    // Needed to define a new shop
    $shopHours = new dpdShopHours();
    $shopHours->addBlock(dpdShopHours::monday, "0700","1200");
    $shopHours->addBlock(dpdShopHours::monday, "1300","1800");
    $shopHours->addBlock(dpdShopHours::tuesday, "0700","1200");
    $shopHours->addBlock(dpdShopHours::tuesday, "1300","1800");

    $pickupLogo = new dpdShopLogo(array(
      "active" => "https://..."
      ,"inactive"  => "https://..."
      ,"shaddow" => "https://..."
    ));

    $result[] = new dpdShop(array(
      "parentId" => self::UID
      ,"id" => "001"
      ,"active" => true
      ,"name" => "My Test Shop"
      ,"location" => new dpdLocation(array(
        "route" => "churchstreet"
        ,"street_number" => "5A"
        ,"locality" => "Brussels"
        ,"postal_code" => "3190"
        ,"country" => "BE"
        ,"lng" => "4.3753899"
        ,"lat" => "50.854975"
      ))
      ,"business_hours" => $shopHours 
      ,"logo" => $pickupLogo
      ,"services" => array(
        dpdShop::pickup
        ,dpdShop::retour  
      )
    ));
    
    return $result;
  }
  
  /**
  * Get label(s) for a single order.
  * 
  * @param dpdOrder $order order details te be used.
  * @return dpdLabel
  */
  public function getLabel(dpdOrder $order, $format = dpdLabel::pdf) {
    return false;
  }
  
  /**
  * Get labels for multiple orders.
  * 
  * @param dpdOrder[] $order an array of dpdOrder objects.
  * @return dpdLabel[]
  */
  public function getLabels(array $orders, $format = dpdLabel::pdf) {
    return false;
  }
  
  /**
  * Get T&T for a Label/Label Number
  * 
  * @param dpdLabel $label
  * @return dpdTracking
  */
  public function getInfo(dpdLabel $label) {
    return false;
  }
}
