<?php

namespace Easy\Mvc\Controller\Component;

use Easy\Mvc\Controller\Component;
use Easy\Mvc\Controller\Controller;

class Zendesk extends Component
{

    public $apiKey;
    public $username;
    public $url;
    protected $curl;

    public function get($url, $json, $action)
    {
        $this->curl = curl_init();

        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($this->curl, CURLOPT_USERPWD, $this->username . "/token:" . $this->apiKey);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($this->curl, CURLOPT_VERBOSE, 1);
        curl_setopt($this->curl, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($this->curl, CURLOPT_URL, $this->url . $url);
        curl_setopt($this->curl, CURLOPT_CAINFO, "public/cacert.pem");
        
        switch ($action) {
            case "POST":
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $json);
                break;
            case "GET":
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "GET");
                break;
            case "PUT":
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $json);
                break;
            case "DELETE":
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
        }

        $output = curl_exec($this->curl);
        curl_close($this->curl);
        $decoded = json_decode($output);
        return $decoded;
    }

}
