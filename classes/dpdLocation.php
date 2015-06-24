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

class dpdLocation {
  
  /**
   * Full formatted address (eg: Leuvensesteenweg 350, 3190 Boortmeerbeek, Belgium)
   * @var string 
   */
  public $formatted_address;
  /**
   * House number (eg: 350)
   * @var string 
   */
  public $street_number;
  /**
   * Route (street) (eg: Leuvensesteenweg)
   * @var string 
   */
  public $route;
  /**
   * Locality (City/town/...) (eg: Boortmeerbeek)
   * @var string 
   */
  public $locality;
  /**
   * Postal code (eg: 3190)
   * @var string 
   */
  public $postal_code;
  /**
   * Administrative area level 1 (State/region/...) (eg: Vlaams Gewest)
   * @var string 
   */
  public $administrative_area_level_1;
  /**
   * Administrative area level 2 (County) (eg: Vlaams Brabant)
   * @var string 
   */
  public $administrative_area_level_2;
    /**
   * Administrative area level 3
   * @var string 
   */
  public $administrative_area_level_3;
    /**
   * Administrative area level 4
   * @var string 
   */
  public $administrative_area_level_4;
  /**
   * Country name (eg: Belgium)
   * @var string 
   */
  public $country;
  /**
   * Country iso alpha 2 representation (eg: BE)
   * @var string 
   */
  public $country_A2;
  /**
   * Country iso alpha 3 representation (eg: BEL)
   * @var string 
   */
  public $country_A3;
  /**
   * Country numerical representation (eg: 056)
   * @var string 
   */
  public $country_N;
  /**
   * Google place id (eg: ChIJ2eUgeAK6j4ARbn5u_wAGqWA)
   * @var string 
   */
  public $google_place_id;
  /**
   * Global Location Number (eg: 5400102000086)
   * @var string
   */
  public $GLN;
  /**
   * Longitude (eg: 50.966506)
   * @var float
   */
  public $lng;
  /**
   * Latitude (eg: 4.572303)
   * @var string
   */
  public $lat;
  
  /**
   * @todo verify input
   * @param (string|array) $data Takes a one string address (eg: "leuvensesteenweg 350, 3190 Boortmeerbeek") or an array of field to set the variables above.
   */
  function __construct($data = "") {
    // Save a single line address
    if(is_string($data)){
      $this->formatted_address = $data;
    // Save separate fields
    } elseif (is_array($data)){
      foreach($data as $key => $value){
        if(property_exists($this, $key)){
          $this->$key = $value;
        }
      }
    // No other options, throw exception.
    } else {
      throw new dpdException("Can only take a string or an array as input.");
    }
  }
  
  /**
   * Try to fill in the missing data with whatever data is known.
   * 
   * @todo What with multiple results if query wasn't specific enough?
   * @todo Add GLN parsing
   * @param boolean $destructive Define if the retuned data may overwrite existing values. If false only empty fields will be filled.
   * @return boolean
   */
  public function parseData($destructive = false) {
    // Goolge place id is the most specific data we can use
    if($this->google_place_id) {
      $url = "http://maps.googleapis.com/maps/api/geocode/json?place_id=" . $this->google_place_id;
    // Then an adres containing street, housenumber, postal code and city
    } elseif($formatted_address = $this->getFormattedAddress()) {
      $query = urlencode($formatted_address);
      $url = "http://maps.googleapis.com/maps/api/geocode/json?address=" . $query;
    // To end we try to use geolocation.
    } elseif($this->lat && $this->lng) {
      $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $this->lat .  "," . $this->lng;
    } else {
      throw new dpdException("Not enough data to start parsing.");
    }
    
    // Make the request to the google API
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_RETURNTRANSFER => 1
      ,CURLOPT_CONNECTTIMEOUT => "2"
      ,CURLOPT_TIMEOUT => "2"
      ,CURLOPT_USERAGENT => "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13"
      ,CURLOPT_URL => $url
    ));
    $result = curl_exec($curl);
    
    // If the request returns an error code we throw an exception
    if($errno = curl_errno($curl)) {
      $error_message = curl_strerror($errno);
      throw new dpdException("Could not connect to parser (" . $error_message . ")");
    }
    // If everything went well we can close curl obj.
    curl_close($curl);
    
    // Decode the result.
    $result_array = json_decode($result);
    
    /**
     * Check status of response
     * "OK" indicates that no errors occurred; the address was successfully parsed and at least one geocode was returned.
     * "ZERO_RESULTS" indicates that the geocode was successful but returned no results. This may occur if the geocoder was passed a non-existent address.
     * "OVER_QUERY_LIMIT" indicates that you are over your quota.
     * "REQUEST_DENIED" indicates that your request was denied.
     * "INVALID_REQUEST" generally indicates that the query (address, components or latlng) is missing.
     * "UNKNOWN_ERROR" indicates that the request could not be processed due to a server error. The request may succeed if you try again.
     */
    switch($result_array->status){
      case "ZERO_RESULTS":
        $error_message = "No results with given details.";
        break;
      case "OVER_QUERY_LIMIT":
        $error_message = "Your quota has been exeeded.";
        break;
      case "REQUEST_DENIED":
        $error_message = "Request denied.";
        break;
      case "INVALID_REQUEST":
        $error_message = "Invalid request, probably bad or missing data.";
        break;
      case "UNKNOWN_ERROR":
        $error_message = "Something went wrong";
        break;
    }
    if(isset($error_message)){
      throw new dpdException($error_message);
    }
    
    // Check if we have more than 1 result.
    if(count($result_array->results) > 1) {
      throw new dpdException("More then one result, please try to specify your query.");
    }
    
    // Save the parsed address details
    foreach($result_array->results[0]->address_components as $result_data){
      foreach($result_data->types as $type){
        if( property_exists($this, $type) && ( empty($this->$type) || $destructive ) ) {
          $this->$type = $result_data->long_name;
          if($type == "country") {
            $this->country_A2 = $result_data->short_name;
          }
        }
      }
    }
    // Save the formatted address
    if( empty($this->formatted_address) || $destructive )  {
      $this->formatted_address = $result_array->results[0]->formatted_address;
    }
    // Save the longitude
    if( empty($this->lng) || $destructive )  {
      $this->lng = $result_array->results[0]->geometry->location->lng;
    }
    // Save the latitude
    if( empty($this->lat) || $destructive )  {
      $this->lat = $result_array->results[0]->geometry->location->lat;
    }
    
    return true;
  }
  
  /**
   * Return a formatted addres, either the direct value or generate it from the separate fields. 
   * 
   * @return (string|boolean)
   */
  public function getFormattedAddress() {
    // If the formatted address is already set we return it
    if(!empty($this->formatted_address)) {
      return $this->formatted_address;
    // If the separate fields are set, we concate them and return the result.
    } elseif ( $this->route != ""
      && $this->street_number != ""
      && $this->postal_code != ""
      && $this->locality != ""
    ) {
      return $this->route  . " " . $this->street_number . ", " . $this->postal_code . " " . $this->locality;
    // Else we don't have any other way of getting a full address, so we return false.
    } else {
      return false;
    }
  }
}
