<?php

class EasyException extends Exception {

    protected $error;
    protected $details;

    public function getError() {
        return $this->error;
    }

    public function getDetails() {
        return $this->details;
    }

    function __construct($error, $details = null, $message = null, $code = null, $previous= null) {
        parent::__construct($message, $code, $previous);
        $this->error = $error;
        $this->details = $details;
    }

}

class MissingControllerException extends EasyException {
    
}

class MissingActionException extends EasyException {
    
}

class MissingComponentException extends EasyException {
    
}

class MissingViewException extends EasyException {
    
}

class MissingModelException extends EasyException {
    
}

class NoPermissionException extends EasyException {
    
}

class ComponentException extends Exception {
    
}

class InvalidLoginException extends ComponentException {
    
}

?>
