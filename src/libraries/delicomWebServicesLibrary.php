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

foreach (glob($dir_path . DS . "DWS" . DS . "*.php") as $filename)
{
  require_once($filename);
}

class delicomWebServicesLibrary implements dpdLibraryInterface {
  
  /**
   * Unique identifier for the class.
   */
  const UID = "DWS";

  /**
   * @param stdObject $config The actual configuration.
   * @param dpdCache $cache A simple cache object to save and retreive data.
   * @return delicomWebServicesLibrary
   */
  public function __construct($config, dpdCache $cache) {
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
      "label" => "DelisID"
      ,"name" => "delis_id" 
      ,"type" => "text"
      ,"validate" => function($var){return (is_string($var) && strlen($var) == 8);}
    ));
    $result[] = new dpdConfiguration( array(
      "label" => "Password"
      ,"name" => "delis_password" 
      ,"type" => "password"
      ,"validate" => function($var){return is_string($var);}
    ));
    $result[] = new dpdConfiguration( array(
      "label" => "Server"
      ,"type" => "label"
      ,"children" => array(
        new dpdConfiguration( array(
          "label" => "Live"
          ,"name" => "delis_server" 
          ,"type" => "option"
          ,"value" => "1"
        ))
        ,new dpdConfiguration( array(
          "label" => "Stage"
          ,"name" => "delis_server" 
          ,"type" => "option"
          ,"value" => "0"
        ))
      )
    ));
    $result[] = new dpdConfiguration( array(
      "label" => "Time Logging"
      ,"type" => "label"
      ,"children" => array(
        new dpdConfiguration( array(
          "label" => "On"
          ,"name" => "time_logging" 
          ,"type" => "option"
          ,"value" => "1"
        ))
        ,new dpdConfiguration( array(
          "label" => "Off"
          ,"name" => "time_logging" 
          ,"type" => "option"
          ,"value" => "0"
        ))
      )
    ));
    
    return $result;
  }
  
  /**
   * Get the service that the shipper can use
   * eg: Classic, Predict, Pickup ...
   * These services will define what is visible in the checkout
   * @todo
   * @return dpdService[]
   */
  static function getServices(){
    $result = array();
    $result[] = new dpdService( array(
      "parentId" => self::UID
      ,"label" => "Home With Predict"
      ,"description" => "Get your parcel delivered at your place, we'll notify you in the morning when we are commming by."
      ,"name" => "home_predict" 
      ,"type" => dpdService::classic
      ,"validate" => function($order){return true;}
    ));
    $result[] = new dpdService( array(
      "parentId" => self::UID
      ,"label" => "Pickup"
      ,"description" => "Can't be home? Let us delivery your parcel in one of our Pickup points."
      ,"name" => "pickup" 
      ,"type" => dpdService::parcelshop
      ,"validate" => function($order){return true;}
    ));
    $result[] = new dpdService( array(
      "parentId" => self::UID
      ,"label" => "Pickup Return"
      ,"description" => "Something wrong? Not what you hoped for? Return in via one of our Pickup points"
      ,"name" => "retour" 
      ,"type" => dpdService::retour
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
    /*
    if(! ($service->type == dpdService::parcelshop 
      || $service->type == dpdService::retour )) {
      return false;
    }
    */
    
    if(empty($location->lng) || empty($location->lat)) {
      $location->parseData();
    }
    $login = $this->getLogin();
    $shopFinder = new DpdParcelShopFinder($login);
    
    $shopFinder->search(array(
      "long" => $location->lng
      ,"lat" => $location->lat
    ));
    
    $result = array();
    
    $pickupLogo = new dpdShopLogo(array(
      "active" => "https://..."
      ,"inactive"  => "https://..."
      ,"shaddow" => "https://..."
    ));
    
    if(count($shopFinder->results) == 0) {
      return false;
    }
    
    foreach($shopFinder->results as $shop){
      $newShop = new dpdShop(array(
        "id" => $shop->parcelShopId
        ,"active" => true
        ,"name" => $shop->company
        ,"location" => new dpdLocation(array(
          "route" => $shop->street
          ,"street_number" => $shop->houseNo
          ,"locality" => $shop->city
          ,"postal_code" => $shop->zipCode
          ,"country_N" => $shop->countryN
          ,"country" => $shop->isoAlpha2
          ,"lng" => $shop->longitude
          ,"lat" => $shop->latitude
        ))
        ,"business_hours" => new dpdShopHours()
        ,"logo" => $pickupLogo
      ));
      
      foreach($shop->openingHours as $day){
        $name;
        switch($day->weekday) {
          case "Monday":
            $name = dpdShopHours::monday;
            break;
          case "Tuesday":
            $name = dpdShopHours::tuesday;
            break;
          case "Wednesday":
            $name = dpdShopHours::wednesday;
            break;
          case "Thurday":
            $name = dpdShopHours::thursday;
            break;
          case "Friday":
            $name = dpdShopHours::friday;
            break;
          case "Saturday":
            $name = dpdShopHours::saturday;
            break;
          case "Sunday":
            $name = dpdShopHours::sunday;
            break;
        }
        if(!empty($day->openMorning)) {
          $open = str_replace(":", "", $day->openMorning);
          if(!empty($day->closeMorning)) {
            $close = str_replace(":", "", $day->closeMorning);
          } elseif(!empty($day->closeAfternoon)) {
            $close = str_replace(":", "", $day->closeAfternoon);
          }
          $newShop->business_hours->addBlock($name, $open, $close);
        }
        if(!empty($day->openAfternoon)) {
          $open = str_replace(":", "", $day->openAfternoon);
          if(!empty($day->closeAfternoon)) {
            $close = str_replace(":", "", $day->closeAfternoon);
          }
          $newShop->business_hours->addBlock($name, $open, $close);
        }
      }
      
      if($shop->pickupByConsigneeAllowed)
        $newShop->addService(dpdShop::pickup);
      if($shop->returnAllowed)
        $newShop->addService(dpdShop::retour);
      if($shop->prepaidAllowed)
        $newShop->addService(dpdShop::online);
      if($shop->cashOnDeliveryAllowed)
        $newShop->addService(dpdShop::cod);
      
      $result[] = $newShop;
    }
    
    return $result;
  }
  
  /**
   * Get label(s) for a single order.
   * 
   * @param dpdOrder $order order details te be used.
   * @return (dpdLabel|false)
   */
  public function getLabel(dpdOrder $order, $format = dpdLabel::pdf){
    if($format != dpdLabel::pdf){
      return false;
    }
  
    $login = $this->getLogin();
    $shipment = new DpdShipment($login);
    
    // TODO: add check for parseData(); 
    
    // General call
    $shipment->request = array(
      "order" => array(
        "generalShipmentData" => array(
          "mpsCustomerReferenceNumber1" => $order->reference
          ,"sendingDepot" => $login->depot
          ,"product" => "CL"
          ,"sender" => array(
            "name1" => $order->sender->first_name . " " . $order->sender->last_name
            ,"street" => $order->sender->location->route
            ,"houseNo" => $order->sender->location->street_number
            ,"country" => $order->sender->location->country_A2
            ,"zipCode" => $order->sender->location->postal_code
            ,"city" => $order->sender->location->locality
          )
          ,"recipient" => array(
            "name1" => $order->receiver->first_name . " " . $order->sender->last_name
            ,"street" => $order->receiver->location->route
            ,"houseNo" => $order->receiver->location->street_number
            ,"country" => $order->receiver->location->country_A2
            ,"zipCode" => $order->receiver->location->postal_code
            ,"city" => $order->receiver->location->locality
          )
        )
        ,"productAndServiceData" => array(
          "orderType" => "consignment"
        )
      )
    );
    
    // Add parcel data
    foreach($order->parcels as $parcel) {
      $shipment->request["order"]["parcels"][] = array(
        "weight" => $parcel->weight * 10
      );
    }
    
    // Additional parcelshop data
    if($order->service->type == dpdService::parcelshop){
      $shipment->request['order']['productAndServiceData']['parcelShopDelivery'] = array(
        'parcelShopId' => $order->shop->id
        ,'parcelShopNotification' => array(
          'channel' => 1
          ,'value' => $order->receiver->email
          ,'language' => $order->receiver->language
        )
      );
    }
    
    // Additional predict data.
    if($order->service->name == "home_predict") {
      $shipment->request['order']['productAndServiceData']['predict'] = array(
        'channel' => 1
        ,'value' => $order->receiver->email
        ,'language' => $order->receiver->language
      );
    }
    
    $shipment->send(); // TODO: add try catch block.
    
    if(isset($shipment->result->orderResult->parcellabelsPDF)) {
      $label = new dpdLabel();
      $label->number = $shipment->result->orderResult->shipmentResponses->parcelInformation->parcelLabelNumber;
      $label->binary = $shipment->result->orderResult->parcellabelsPDF;
      
      return $label;
    }  
      
    return false;
  }
  
  /**
   * Get labels for multiple orders.
   * 
   * @param dpdOrder[] $order an array of dpdOrder objects.
   * @return (dpdLabel[]|false)
   */
  public function getLabels(array $orders, $format = dpdLabel::pdf) {
    if($format != dpdLabel::pdf){
      return false;
    }
    
    $result = array();
    foreach($orders as $order) {
      $label = $this->getLabel($order, $format);
      if($label) {
        $result[] = $label;
      }
    }
    return $result;
  }
  
  /**
   * Get T&T for a Label/Label Number
   * 
   * @param dpdLabel $label
   * @return (dpdEvent|false)
   */
  public function getInfo(dpdLabel $label) {
    $login = $this->getLogin();
    $lifeCycle = new DpdParcelLifeCycle($login);
    
    $lifeCycle->search(array(
      "parcellabelnumber" => $label->number
    ));
    
    if(!empty($lifeCycle->results[$label->number])){
      $result = array();
      foreach($lifeCycle->results[$label->number]->statusInfo as $status) {
        $event = new dpdEvent();
        switch($status->status) {
          case "ACCEPTED":
            $event->status = dpdEvent::sent;
            break;
          case "AT_SENDING_DEPOT":
            $event->status = dpdEvent::transit;
            break;
          case "ON_THE_ROAD":
            $event->status = dpdEvent::transit;
            break;
          case "AT_DELIVERY_DEPOT":
            $event->status = dpdEvent::delivery;
            break;
          case "DELIVERED":
            $event->status = dpdEvent::delivered;
            break;
        }
        $event->description = $status->description->content->content;
        if(isset($status->date->content)){
          $time_string = $status->date->content; // 01/06/2012, 02:09 
          $event->time = DateTime::createFromFormat ( "d/m/Y, H:i " , $time_string );
        }
        $result[] = $event;
      }
      return $result;
    }
    
    return false;
  }
  
  private function getLogin() {
    // Get the current configuration.
    $delisID = $this->config->delis_id;
    $password = $this->config->delis_password;
    $server_url = ( $this->config->delis_server == 1 ) ? "https://public-ws.dpd.com/services/" : "https://public-ws-stage.dpd.com/services/" ;
    $time_logging =  $this->config->time_logging == 1;
    
    // Check if the login was cached.
    if($this->cache->login) {
      $login = $this->cache->login;
      // If it was cached we check if the settings were the same
      if( $login->delisId == $delisID
        && $login->url == $server_url
        && $login->timeLogging == $time_logging) {
        return $login;
      }
    }
    // If it wasn't cached, or settings are changed, we create a new login
    $login = new DpdLogin($delisID, $password, $server_url, $time_logging);
    // TODO add a try catch.
    
    // Cache it.
    $this->cache->login = $login;
    // And return
    return $login;
  }
}
