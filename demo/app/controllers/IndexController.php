<?php

class IndexController extends AppController {

    /**
     * Used to tell the framework that in this controller we aren't using any model class
     * @var array 
     */
    public $uses = array();
    public $modelClass = false;

    function index() {
        //Passing the $var var to the view
        $this->var = "Hello world from controller";
    }

}

?>
