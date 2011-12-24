<?php

/**
 * Parent class for all of the HTTP related exceptions in CakePHP.
 * All HTTP status/error related exceptions should extend this class so
 * catch blocks can be specifically typed.
 *
 */
if (!class_exists('HttpException')) {

    class HttpException extends RuntimeException {
        
    }

}

/**
 * Represents an HTTP 400 error.
 *
 */
class BadRequestException extends HttpException {

    /**
     * Constructor
     *
     * @param string $message If no message is given 'Bad Request' will be the message
     * @param string $code Status code, defaults to 400
     */
    public function __construct($message = null, $code = 400) {
        if (empty($message)) {
            $message = 'Bad Request';
        }
        parent::__construct($message, $code);
    }

}

/**
 * Represents an HTTP 401 error.
 *
 */
class UnauthorizedException extends HttpException {

    /**
     * Constructor
     *
     * @param string $message If no message is given 'Unauthorized' will be the message
     * @param string $code Status code, defaults to 401
     */
    public function __construct($message = null, $code = 401) {
        if (empty($message)) {
            $message = 'Unauthorized';
        }
        parent::__construct($message, $code);
    }

}

/**
 * Represents an HTTP 403 error.
 *
 */
class ForbiddenException extends HttpException {

    /**
     * Constructor
     *
     * @param string $message If no message is given 'Forbidden' will be the message
     * @param string $code Status code, defaults to 403
     */
    public function __construct($message = null, $code = 403) {
        if (empty($message)) {
            $message = 'Forbidden';
        }
        parent::__construct($message, $code);
    }

}

/**
 * Represents an HTTP 404 error.
 *
 */
class NotFoundException extends HttpException {

    /**
     * Constructor
     *
     * @param string $message If no message is given 'Not Found' will be the message
     * @param string $code Status code, defaults to 404
     */
    public function __construct($message = null, $code = 404) {
        if (empty($message)) {
            $message = 'Not Found';
        }
        parent::__construct($message, $code);
    }

}

/**
 * Represents an HTTP 405 error.
 *
 */
class MethodNotAllowedException extends HttpException {

    /**
     * Constructor
     *
     * @param string $message If no message is given 'Method Not Allowed' will be the message
     * @param string $code Status code, defaults to 405
     */
    public function __construct($message = null, $code = 405) {
        if (empty($message)) {
            $message = 'Method Not Allowed';
        }
        parent::__construct($message, $code);
    }

}

/**
 * Represents an HTTP 500 error.
 *
 */
class InternalErrorException extends HttpException {

    /**
     * Constructor
     *
     * @param string $message If no message is given 'Internal Server Error' will be the message
     * @param string $code Status code, defaults to 500
     */
    public function __construct($message = null, $code = 500) {
        if (empty($message)) {
            $message = 'Internal Server Error';
        }
        parent::__construct($message, $code);
    }

}

class EasyException extends Exception {

    protected $attributes;

    /**
     * Template string that has attributes sprintf()'ed into it.
     *
     * @var string
     */
    protected $_messageTemplate = '';

    public function getAttributes() {
        return $this->attributes;
    }

    function __construct($message, $attr = array(), $code = 500) {
        $this->attributes = $attr;
        if (is_array($message)) {
            eval('$message = sprintf($this->_messageTemplate, "' . implode('","', $message) . '");');
        } else {
            $message = sprintf($this->_messageTemplate, $message);
        }
        parent::__construct($message);
    }

}

/**
 * Missing Controller exception - used when a controller
 * cannot be found.
 *
 */
class MissingControllerException extends EasyException {

    protected $_messageTemplate = 'Controller class %s could not be found.';

    public function __construct($message, $attr = array(), $code = 404) {
        parent::__construct($message, $attr, $code);
    }

}

/**
 * Missing Action exception - used when a controller action
 * cannot be found.
 *
 */
class MissingActionException extends EasyException {

    protected $_messageTemplate = 'Action %s::%s() could not be found.';

    public function __construct($message, $attr = array(), $code = 404) {
        parent::__construct($message, $attr, $code);
    }

}

/**
 * Used when a component cannot be found.
 *
 */
class MissingComponentException extends EasyException {

    protected $_messageTemplate = 'Component class %s could not be found.';

}

/**
 * Used when a view file cannot be found.
 *
 */
class MissingViewException extends EasyException {

    protected $_messageTemplate = 'View file "%s" is missing. The Controller %s has no view for the action %s';

}

/**
 * Exception raised when a Model could not be found.
 *
 */
class MissingModelException extends EasyException {

    protected $_messageTemplate = 'Model %s could not be found.';

}

class NoPermissionException extends EasyException {
    
}

/**
 * Exception class for Cache.  This exception will be thrown from Cache when it
 * encounters an error.
 *
 */
class CacheException extends EasyException {
    
}

/**
 * Exception class for CakeLog.  This exception will be thrown from CakeLog when it
 * encounters an error.
 *
 */
class CakeLogException extends EasyException {
    
}

class ComponentException extends Exception {
    
}

class InvalidLoginException extends ComponentException {
    
}

?>
