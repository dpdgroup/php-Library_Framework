<?php
/**
* Examples
* 
* @author     Michiel Van Gucht
* @version    0.0.1
* @copyright  2015 Michiel Van Gucht
* @license    LGPL
*/
require_once("dpdLibrary.php");

/**
 * Require all special dpd objects
 */ 
foreach (glob("classes/*.php") as $filename)
{
  require_once($filename);
}

/**
 * Mini cache class (Platform depended)
 */
class dpdCache implements dpdCacheInterface {
  public function __get($name) {
    return false;
  }
  
  public function __set($name, $value){
    return false;
  }
}

/**
 * Get libraries example
 */
var_dump(dpdLibrary::getLibraries());

/**
 * Configuration example
 */
var_dump(dpdLibrary::getConfiguration());

/**
 * Get services example
 */
$services = dpdLibrary::getServices();
var_dump($services);

/**
 * Shop example
 */
// Create cache object
$cache = new dpdCache();

// Emulate configuration
$config = new stdClass();
$config->delis_id = "";  // your DelisID
$config->delis_password = ""; // your password
$config->delis_server = 0;
$config->time_logging = 1;
$config->pc_name = "";
$config->pc_token = "";
$config->uc_cloud_user_id = "";
$config->uc_token = "";

// Create library
$main = new dpdLibrary($config, $cache);

// create location object
$location = new dpdLocation(array(
  "street_number" => "350"
  ,"route" => "leuvensesteenweg"
  ,"locality" => "boortmeerbeek"
  ,"postal_code" => "3190"
  ,"country" => "Belgium"
));

// Dump results.
var_dump($main->getShops($services["DCS"][0], $location));

/**
 * Order example
 */
$order = new dpdOrder();
$order->reference = "test";
$order->sender = new dpdContact( array(
  "first_name" => "Michiel"
  ,"last_name" => "Van Gucht"
  ,"location" => new dpdLocation( array(
    "route" => "Egide Walschaertsstraat"
    ,"street_number" => "20"
    ,"locality" => "Mechelen"
    ,"postal_code" => "2800"
    ,"country_A2" => "BE"
  ))
)); 
$order->receiver = new dpdContact( array(
  "first_name" => "Test"
  ,"last_name" => "Van Gucht"
  ,"location" => new dpdLocation( array(
    "route" => "Leuvensesteenweg"
    ,"street_number" => "350"
    ,"locality" => "Boortmeerbeek"
    ,"postal_code" => "3190"
    ,"country_A2" => "BE"
  ))
  ,"email" => "michiel.vangucht@dpd.be"
  ,"language" => "NL"
));
$order->parcels = array(
  new dpdParcel( array(
    "weight" => "10"
  ))
);
$order->service = $services["DCS"][0];

var_dump($order);

var_dump($main->getLabel($order));

/**
 * getInfo example
 */
$label = new dpdLabel(array( 
  "parentId" => "DCS"
  ,"number" => "01305048685208"
));
var_dump($main->getInfo($label));