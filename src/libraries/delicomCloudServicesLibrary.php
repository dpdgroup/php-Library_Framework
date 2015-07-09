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

class delicomCloudServicesLibrary implements dpdLibraryInterface {
  
  /**
   * Unique identifier for the class.
   */
  const UID = "DCS";
  
  /**
   * @param stdObject $config The actual configuration.
   * @param dpdCache $cache A simple cache object to save and retreive data.
   * @return delicomCloudServicesLibrary
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
      "label" => "Partner Credentials"
      ,"type" => "label"
      ,"children" => array(
        new dpdConfiguration( array(
          "label" => "Name"
          ,"name" => "pc_name" 
          ,"type" => "text"
          ,"validate" => function($var){return is_string($var);}
        ))
        ,new dpdConfiguration( array(
          "label" => "Token"
          ,"name" => "pc_token" 
          ,"type" => "text"
          ,"validate" => function($var){return is_string($var);}
        ))
      )
    ));
    $result[] = new dpdConfiguration( array(
      "label" => "User Credentials"
      ,"type" => "label"
      ,"children" => array(
        new dpdConfiguration( array(
          "label" => "Cloud User ID"
          ,"name" => "uc_cloud_user_id" 
          ,"type" => "text"
          ,"validate" => function($var){return is_string($var);}
        ))
        ,new dpdConfiguration( array(
          "label" => "Token"
          ,"name" => "uc_token" 
          ,"type" => "text"
          ,"validate" => function($var){return is_string($var);}
        ))
      )
    ));
    $result[] = new dpdConfiguration( array(
      "label" => "Server"
      ,"type" => "label"
      ,"children" => array(
        new dpdConfiguration( array(
          "label" => "Live"
          ,"name" => "server" 
          ,"type" => "option"
          ,"value" => "1"
        ))
        ,new dpdConfiguration( array(
          "label" => "Stage"
          ,"name" => "server" 
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
    if($service =! null 
      && $service->type != dpdService::parcelshop) {
      return false;
    }
    */
    
    if(empty($location->lng) || empty($location->lat)) {
      $location->parseData();
    }

    $client = new SoapClient("https://cloud-stage.dpd.com/services/v1/DPDCloudService.asmx?wsdl", array('trace' => 1));
    
    $shopFinder = $client->getParcelShopFinder(array(
      "getParcelShopFinderRequest" => array(
        "Version" => "100"
        ,"Language" => "en_EN"
        ,"PartnerCredentials" => array(
          "Name" => $this->config->pc_name
          ,"Token" => $this->config->pc_token
        )
        ,"UserCredentials" => array(
          "cloudUserID" => $this->config->uc_cloud_user_id
          ,"Token" => $this->config->uc_token
        )
        ,"MaxReturnValues" => $limit
        ,"SearchMode" => "SearchByGeoData"
        ,"SearchGeoData" => array(
          "Longitude" => $location->lng
          ,"Latitude" => $location->lat
        )
        ,"NeedService" => "ConsigneePickup"
      )
    ));
    
    $result = array();
    
    $pickupLogo = new dpdShopLogo(array(
      "active" => "https://..."
      ,"inactive"  => "https://..."
      ,"shaddow" => "https://..."
    ));
    
    if($shopFinder->getParcelShopFinderResult->ResultCounter == 0) {
      return false;
    }
    // @todo: fix issue when limit is set to 1 !!
    foreach($shopFinder->getParcelShopFinderResult->ParcelShopList->ParcelShop as $shop){
      $newShop = new dpdShop(array(
        "parentId" => self::UID
        ,"id" => $shop->ParcelShopID
        ,"active" => true
        ,"name" => $shop->ShopAddress->Company
        ,"location" => new dpdLocation(array(
          "route" => $shop->ShopAddress->Street
          ,"street_number" => $shop->ShopAddress->HouseNo
          ,"locality" => $shop->ShopAddress->City
          ,"postal_code" => $shop->ShopAddress->ZipCode
          ,"country_A2" => $shop->ShopAddress->Country
          ,"lng" => $shop->GeoData->Longitude
          ,"lat" => $shop->GeoData->Latitude
        ))
        ,"business_hours" => new dpdShopHours()
        ,"logo" => $pickupLogo
      ));

      foreach($shop->OpeningHoursList->OpeningHours as $day){
        $name;
        switch($day->WeekDay) {
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

        if( is_array($day->OpenTimeList->OpenTimeType) ) {
          foreach($day->OpenTimeList->OpenTimeType as $block) {
            $start = str_replace(":", "", $block->TimeFrom);
            $end = str_replace(":", "", $block->TimeEnd);
            $newShop->business_hours->addBlock($name, $start, $end);
          }
        } else {
          $start = str_replace(":", "", $day->OpenTimeList->OpenTimeType->TimeFrom);
          $end = str_replace(":", "", $day->OpenTimeList->OpenTimeType->TimeEnd);
          $newShop->business_hours->addBlock($name, $start, $end);
        }
      }
      
      foreach($shop->ShopServiceList->ShopService as $service) {
        switch($service) {
          case "PickupByConsignee":
            $newShop->addService(dpdShop::pickup);
            break;
          case "ReturnService":
            $newShop->addService(dpdShop::retour);
            break;
          case "PrepaidService":
            $newShop->addService(dpdShop::online);
            break;
        }
      }
      
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
    
    $request = array(
      "setOrderRequest" => array(
        "Version" => "100"
        ,"Language" => "en_EN"
        ,"PartnerCredentials" => array(
          "Name" => $this->config->pc_name
          ,"Token" => $this->config->pc_token
        )
        ,"UserCredentials" => array(
          "cloudUserID" => $this->config->uc_cloud_user_id
          ,"Token" => $this->config->uc_token
        )
        ,"OrderAction" => "startOrder"
        ,"OrderSettings" => array(
          "ShipDate" => date("Y-m-d")
          ,"LabelSize" => "PDF_A6"
          ,"LabelStartPosition" => "UpperLeft"
        )
      )
    );
    
    
    // Add parcel data
    foreach($order->parcels as $parcel) {
       $orderData = array(
        "OrderData" => array(
          "ShipAddress" => array(
            "Company" => $order->receiver->company
            ,"Name" => $order->receiver->first_name . " " .$order->receiver->last_name 
            ,"Street" => $order->receiver->location->route
            ,"HouseNo" => $order->receiver->location->street_number
            ,"Country" => $order->receiver->location->country_A2
            ,"ZipCode" => $order->receiver->location->postal_code
            ,"City" => $order->receiver->location->locality
          )
          ,"ParcelShopID" => 0
        )
      );
      $orderData["OrderData"]["ParcelData"] = array(
        "ShipService" => "Classic"
        ,"Weight" => $parcel->weight
        ,"Content" => "100020020"
        ,"YourInternalID" => 123
        ,"Reference1" => $order->reference
      );
      $request["setOrderRequest"]["OrderDataList"] = $orderData;
    }
    
    $client = new SoapClient("https://cloud-stage.dpd.com/services/v1/DPDCloudService.asmx?wsdl", array('trace' => 1));
    $shipment = $client->setOrder($request);
    
    if(isset($shipment->setOrderResult->LabelResponse)) {
      $label = new dpdLabel();
      $label->parentId = self::UID;
      $label->number = $shipment->setOrderResult->LabelResponse->LabelDataList->LabelData->ParcelNo;
      $label->binary = $shipment->setOrderResult->LabelResponse->LabelPDF;
      
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
    
    $client = new SoapClient("https://cloud-stage.dpd.com/services/v1/DPDCloudService.asmx?wsdl", array('trace' => 1));
    
    $lifeCycle = $client->getParcelLifeCycle(array(
      "getParcelLifeCycleRequest" => array(
        "Version" => "100"
        ,"Language" => "en_EN"
        ,"PartnerCredentials" => array(
          "Name" => $this->config->pc_name
          ,"Token" => $this->config->pc_token
        )
        ,"UserCredentials" => array(
          "cloudUserID" => $this->config->uc_cloud_user_id
          ,"Token" => $this->config->uc_token
        )
        ,"ParcelNo" => $label->number
      )
    ));
    
    if(!empty($lifeCycle->getParcelLifeCycleResult->ParcelLifeCycleData->statusInfo)){
      $result = array();
      foreach($lifeCycle->getParcelLifeCycleResult->ParcelLifeCycleData->statusInfo as $status) {
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
}
