<?php
/**
* This interface is the start for any one that wishes to join in the use of our modules.
* - Checkout
* - Label Generation
*  - Delivery
*  - Returns
* - Track and Trace
* - ...
* 
* @author     Michiel Van Gucht
* @version    0.0.1
* @copyright  2015 Michiel Van Gucht
* @license    LGPL
*/

interface dpdLibraryInterface {
  /**
   * @param stdObject $config The actual configuration.
   * @param dpdCache $cache A simple cache object to save and retreive data.
   * @return dpdLibraryInterface
   */
  public function __construct($config, dpdCache $cache);
  
  /**
  * Get the configuration fields needed for the library/api to work.
  * eg: 
  *   Delicom API needs delisID, password
  *   Cloud services need different tokens.
  * These configuration fields will be show in the modules configuration
  * @return dpdConfiguration[]
  */
  static function getConfiguration();
  
  /**
  * Get the service that the shipper can use
  * eg: Classic, Predict, Pickup ...
  * These services will define what is visible in the checkout
  * @return dpdService[]
  */
  static function getServices();
  
  /**
  * Get a list of parcelshops close to a given location.
  * This function should use the address details or the geolocation from the dpdLocation object.
  * TIP: If possible map the address to geolocation for an optimal location lookup.
  * @param dpdService $service the service that calls for the function
  * @param dpdLocation $location location to look up.
  * @param integer $limit the maximum amount of shops to return
  * @return dpdShop[] 
  */
  public function getShops(dpdService $service, dpdLocation $location, $limit);
  
  /**
  * Get label(s) for a single order.
  * 
  * @param dpdOrder $order order details te be used.
  * @return dpdLabel
  */
  public function getLabel(dpdOrder $order, $format = dpdLabel::pdf);
  
  /**
  * Get labels for multiple orders.
  * 
  * @param dpdOrder[] $order an array of dpdOrder objects.
  * @return dpdLabel[]
  */
  public function getLabels(array $orders, $format = dpdLabel::pdf);
  
  /**
  * Get T&T for a Label/Label Number
  * 
  * @param dpdLabel $label
  * @return dpdTracking
  */
  public function getInfo(dpdLabel $label);
}
