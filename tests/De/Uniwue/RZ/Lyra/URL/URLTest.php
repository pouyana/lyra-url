<?php
/**
 * A test case for the URL library
 * 
 * @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
 * @license MIT
 */

 namespace De\Uniwue\RZ\Lyra\URL;

 class URLTest extends \PHPUnit_Framework_TestCase{

    /**
     * Setup for the test
     */
    public function setUp(){
        global $config;
        $this->config = $config;
        $this->createLogger();
    }

    public function createLogger(){
        $this->logger = new Logger("name");
    }

    public function testLog(){
        $url = new URL(array(), $this->logger);
        $url->log("info", "data is logged");
    }

    public function testParse(){
        $urlObj = new URL(array(), $this->logger);
        $urlString = "http://www.example.com:232/path";
        $result = $urlObj->parse($urlString);
        $toCheck = array(
            'scheme' =>"http",
            'host' =>"www.example.com",
            'port' => "232",
            'path' => "/path",
        );
        $this->assertEquals($toCheck, $result);

        $urlString = "http:/adsadas";
        $result = $urlObj->parse($urlString);
        $this->assertNull($result);
    }

    public function testEncode(){
        $urlObj = new URL(array(), $this->logger);
        $urlString ="http://www.example.com/Hello=? Test";
        $encodedString = $urlObj->encode($urlString);
        $this->assertEquals("http%3A%2F%2Fwww.example.com%2FHello%3D%3F+Test", $encodedString);
    }

    public function testDecode(){
        $urlObj = new URL(array(), $this->logger);
        $urlString ="http%3A%2F%2Fwww.example.com%2FHello%3D%3F+Test";
        $decodedString = $urlObj->decode($urlString);
        $this->assertEquals("http://www.example.com/Hello=? Test", $decodedString);
    }

    public function testCheck(){
        $urlObj = new URL(array(), $this->logger);
        $urlString = "http://www.example.com";
        $result = $urlObj->check($urlString);
        $toCheck = array(
            "code" => 200,
            "redirect" => "http://www.example.com/",
            "url" => "http://www.example.com",
            "proxy" => false,
            "follow" => false,
        );
        
        $this->assertEquals($result, $toCheck);
    }

    public function testCheckWithProxy(){
        $urlObj = new URL($this->config, $this->logger);
        $urlString = "http://www.example.com";        
        $result = $urlObj->check($urlString, true);
        $toCheck = array(
            "code" => 200,
            "redirect" => "http://www.example.com/",
            "url" => "http://www.example.com",
            "proxy" => true,
            "follow" => false,
        );
        $this->assertEquals($result, $toCheck);        
    }

    public function testCheckWithFollow(){
        $urlObj = new URL(array(), $this->logger);
        $urlString = "http://www.example.com";
        $result = $urlObj->check($urlString, false, true);
        $toCheck = array(
            "code" => 200,
            "redirect" => "http://www.example.com/",
            "url" => "http://www.example.com",
            "proxy" => false,
            "follow" => true,
        );
        
        $this->assertEquals($result, $toCheck);        
    }

    public function testCheckWithInvalidURL(){
        $urlObj = new URL(array(), $this->logger);
        $urlString = "http:/www.example.com";
        $result = $urlObj->check($urlString);
        $toCheck = array(
            "code" => 0,
            "redirect" => "http://http:/www.example.com",
            "url" => "http:/www.example.com",
            "proxy" => false,
            "follow" => false,
        );
        
        $this->assertEquals($result, $toCheck);           
    }

    public function testGetProxyType(){
        $configModified = $this->config;
        $configModified["type"] = "http1";
        $urlString = "http:/www.example.com";
        $urlObj = new URL($configModified, $this->logger);
        $result = $urlObj->check($urlString, true);
        $configModified["type"] = "http";
        $urlObj = new URL($configModified, $this->logger);
        $result = $urlObj->check($urlString, true);
        $configModified["type"] = "socks4";
        $urlObj = new URL($configModified, $this->logger);
        $result = $urlObj->check($urlString, true);
        $configModified["type"] = "socks4a";
        $urlObj = new URL($configModified, $this->logger);
        $result = $urlObj->check($urlString, true);
        $configModified["type"] = "socks5";
        $urlObj = new URL($configModified, $this->logger);
        $result = $urlObj->check($urlString, true);
        $configModified["type"] = "socks5h";
        $urlObj = new URL($configModified, $this->logger);
        $result = $urlObj->check($urlString, true);
        $configModified["type"] = "hello";
        $urlObj = new URL($configModified, $this->logger);
        $result = $urlObj->check($urlString, true);
    }

    /**
     * @expectedException \Exception
     */
    public function testCheckWithWrongProxy(){
        $configModified = $this->config;
        $configModified["host"] = null;
        $urlString = "http:/www.example.com";
        $urlObj = new URL($configModified, $this->logger);
        $result = $urlObj->check($urlString, true);   
    }

    public function testCheckWithProxyAuth(){
        $configModified = $this->config;
        $configModified["auth"] = "hello:world";
        $urlString = "http:/www.example.com";
        $urlObj = new URL($configModified, $this->logger);
        $result = $urlObj->check($urlString, true);   
    }
 }