<?php

/**
 * Webservice provider class
 */
class WebserviceComponent extends Component {

    function getService($url) {
        return file_get_contents($url);
    }

}

?>
