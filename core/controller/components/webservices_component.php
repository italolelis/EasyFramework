<?php

App::import("Lib", "nusoap/nusoap");

/**
 * Webservice provider class
 */
class WebServicesComponent extends Component {

    public function getWebServiceClient($endpoint, $wsdl = false, $proxyhost = false, $proxyport = false, $proxyusername = false, $proxypassword = false, $timeout = 0, $response_timeout = 30, $portName = '') {
        return new soapclient($endpoint, $wsdl, $proxyhost, $proxyport, $proxyusername, $proxypassword, $timeout, $response_timeout, $portName);
    }

    public function getWebServiceServer() {
        return new soap_server();
    }

}

?>
