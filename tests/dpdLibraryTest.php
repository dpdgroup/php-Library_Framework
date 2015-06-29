<?php

/* foreach(glob("../src/libraries/*.php") as $file) {
  require_once($file);
} */

defined("DS") ? null : define("DS", DIRECTORY_SEPARATOR);
$dir_path = dirname(__FILE__);
defined("SRC_PATH") ? null : define("SRC_PATH", $dir_path . DS . ".." . DS . "src" . DS);

class dpdLibraryTest extends PHPUnit_Framework_TestCase {
  
  /**
   * Test if all libraries have an UID set (and if it is unique)
   */
  public function testAllUIDsSet() { 
    $UIDs = array();
    foreach (glob(SRC_PATH . "libraries" . DS . "*.php") as $file_path)
    {
      // Require the file just to be sure
      require_once($file_path);
      
      // Get the file name (without extension .php)
      $class_name = basename($file_path, ".php");
      
      // Check if the UID is defined
      $this->assertTrue(defined($class_name . "::UID"), "UID is not set for " . $class_name);
      
      // Check if it unique (amongst installed libraries)
      $this->assertNotContains($class_name::UID, $UIDs, "There are duplicate library UIDs! (" . $class_name::UID . ")");
      
      $UIDs[] = $class_name::UID;
    }
  }
  
  /**
   * @depends testAllUIDsSet
   */
  public function testGetLibraries() {
    $this->assertInternalType("array", dpdLibrary::getLibraries());
  }
  
  /**
   * @depends testGetLibraries
   */
  public function testLoadLibraries() {
    $libraries = dpdLibrary::getLibraries();
    
    foreach($libraries as $class_name) {
      $this->assertFileExists(SRC_PATH . "libraries" . DS . $class_name . ".php");
    } 
  }
  
  /**
   * @depends testLoadLibraries
   */
  public function testGetConfiguration() {
    $UIDconfiguration = dpdLibrary::getConfiguration();
    $this->assertInternalType("array", $UIDconfiguration);
    foreach($UIDconfiguration as $configuration) {
      foreach($configuration as $config) {
        $this->assertInstanceOf("dpdConfiguration", $config);
      }
    }
  }
  
  /**
   * @depends testLoadLibraries
   */
  public function testGetServices() {
    $UIDservices = dpdLibrary::getServices();
    $this->assertInternalType("array", $UIDservices);
    foreach($UIDservices as $services) {
      $this->assertInternalType("array", $services);
      foreach($services as $service) {
        $this->assertInstanceOf("dpdService", $service);
        $this->assertAttributeNotEmpty("parentId", $service);
        $this->assertAttributeNotEmpty("name", $service);
        $this->assertAttributeNotEmpty("type", $service, "type: " . $service->type);
      }
    }
  }
  
  /**
   * Tests shouldn't be done directly on API, so I'm not sure (yet) on how to write the tests below.
   * @depends testGetServices
   */
  public function testGetShops() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }
  
  public function testGetLabel() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }
  
  public function testGetLabels() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }
  
  public function testGetInfo() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }
  
  public function testGetInfos() {
    $this->markTestIncomplete("This test has not been implemented yet.');
  }
}
