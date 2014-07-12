<?php

/**
 * @fileOverview domainr-php
 * A PHP client library for Domai.nr API's
 *
 * @author Kyle Florence
 * @website http://github.com/kflorence/domainr-php
 * @version 0.1.1
 *
 * Copyright (c) 2014 Kyle Florence
 * Dual licensed under the MIT and GPL licenses.
 */

class Domainr {

  // Base URL for domai.nr API calls
  const BASE_URI = "https://domai.nr/api";
  
  // Client ID. See https://github.com/kflorence/domainr-php/issues/2
  const CLIENT_ID = "php_kflorence";

  // Contains all the valid methods for each API
  private $apis = array(
    "json" => array("search", "info"),
    "register" => array()
  );

  // Singleton instance
  private static $instance;

  // Creates or returns our singleton instance
  public static function instance() {
    if (!self::$instance) {
      self::$instance = new Domainr();
    }

    return self::$instance;
  }

  /**
   * Wrapper method for JSON API calls.
   *
   * @param String $method The method to call
   * @param Array $params Parameters to pass to the method
   */
  public function json($method, $params = array()) {
    if (in_array($method, $this->apis["json"])) {
      return $this->callAPI("/json/" . $method, $params);
    } else {
      throw new Exception("Method '" . $method . "' does not exist is the json API.");
    }
  }

  /**
   * Wrapper method for register API calls.
   *
   * @param Array $params Parameters to pass to the register API
   */
  public function register($params = array()) {
    return $this->callAPI("/register", $params);
  }

  /**
   * Utility method for API calls.
   *
   * @param $url The URL to the API and method
   * @param $params The parameters to pass to the method
   */
  private function callAPI($url, $params = array()) {
    $response = null;
    
    // Add client_id to params.
    $params["client_id"] = self::CLIENT_ID;

    // Build full URL
    $url = self::BASE_URI . $url . "?" . http_build_query($params);

    // Initialize CURL request
    if ($ch = curl_init($url)) {
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

      // Get results
      $result = curl_exec($ch);
      $result_info = curl_getinfo($ch);

      // Handle response based on HTTP status code
      switch($result_info["http_code"]) {
        case 0: {
          throw new Exception("Request timed out: " . $url);
          break;
        }
        case 200: {
          $response = json_decode($result);
          break;
        }
        case 404: {
          throw new Exception("Requested API call was not found: " . $url);
          break;
        }
      }
    } else {
      throw new Exception("CURL could not be initialized!");
    }

    return $response;
  }
}

