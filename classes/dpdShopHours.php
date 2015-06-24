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

class dpdShopHours {
  
  const monday    = 0;
  const tuesday   = 1;
  const wednesday = 2;
  const thursday  = 3;
  const friday    = 4;
  const saturday  = 5;
  const sunday    = 6;
  
  public $data;
  
  public function __construct($data = array()){
    if (is_array($data)){ 
      $this->data = $data;
    } else {
      throw new dpdException("Can only take an array as input."); 
    }
  }
  
  /**
   * Add an opening block to the bussines hours
   * @param int $day
   * @param int $start
   * @param int $end
   * @return stdClass[]
   */
  public function addBlock($day, $start, $end) {
    $day_data = isset($this->data[$day]) ? $this->data[$day] : array();
    $pre_data = array();
    for($i = count($day_data) + 1; $i > 0; $i--) {
        if($i == 1 || $start < $day_data[0]->start) {
          $newBlock = new stdClass();
          $newBlock->start = $start;
          $newBlock->end = $end;
          $this->data[$day] = array_merge($pre_data, array($newBlock), $day_data);
        }
        $pre_data[] = array_shift($day_data);
    }
    return $this->data;
  }
  
  /**
   * Remove an opening block from the bussines hours
   * @param int $day
   * @param int $start
   * @param int $end
   * @return stdClass[]
   */
  public function delBlock($day, $start, $end) {
    foreach($this->data[$day] as $key => $block){
      if($block->start == $start
        && $block->end == $end){
        unset($this->data[$day][$key]) ;
        break;
      }
    }
    $this->data[$day] = array_values($this->data[$day]); // Reindex the array
    return $this->data;
  }

}
