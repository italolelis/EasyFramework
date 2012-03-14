<?php

class GithubHttp {

    /**
     * Api Version 3
     * @var string 
     */
    protected $url = "https://api.github.com/repos/";

    public function get($url) {
        $response = $this->doCurlCall($this->url . $url);
        return json_decode($response);
    }

    /**
     * Send a request to the server, receive a response
     *
     * @param  string   $path          Request url
     * @param  array    $parameters    Parameters
     * @param  string   $httpMethod    HTTP method to use
     * @param  array    $options       Request options
     *
     * @return string   HTTP response
     */
    private function doCurlCall($url, $curlOptions = array()) {
        $curlOptions += array(
            CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        );

        $curl = curl_init();
        curl_setopt_array($curl, $curlOptions);
        return curl_exec($curl);
    }

}