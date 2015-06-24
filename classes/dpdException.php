<?php
/**
* An extended Exception that enables us to add remote error reporting in the constructor.
* 
* @author     Michiel Van Gucht
* @version    0.0.1
* @copyright  2015 Michiel Van Gucht
* @license    LGPL
*/
class dpdException extends Exception
{
  /**
  * @todo add remote error reportin to constructor :)
  * @return dpdException
  */
  public function __construct($message, $code = 0, Exception $previous = null) {
      parent::__construct($message, $code, $previous);
  }
}
