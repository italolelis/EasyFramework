<?php

class EasyException extends Exception {

    protected $details;

    public function getDetails() {
        return $this->details;
    }

    function __construct($details = null, $message = null) {
        parent::__construct($message);
        $this->details = $details;
    }

}

class MissingControllerException extends EasyException {

    function __construct($details = null, $message = "controller") {
        parent::__construct($details, $message);
    }

}

class MissingActionException extends EasyException {

    function __construct($details = null, $message = "action") {
        parent::__construct($details, $message);
    }

}

class MissingComponentException extends EasyException {

    function __construct($details = null, $message = "component") {
        parent::__construct($details, $message);
    }

}

class MissingViewException extends EasyException {

    function __construct($details = null, $message = "view") {
        parent::__construct($details, $message);
    }

}

class MissingModelException extends EasyException {

    function __construct($details = null, $message = "model") {
        parent::__construct($details, $message);
    }

}

class NoPermissionException extends EasyException {

    function __construct($details = null, $message = "permission") {
        parent::__construct($details, $message);
    }

}

class ComponentException extends Exception {
    
}

class InvalidLoginException extends ComponentException {
    
}

?>
