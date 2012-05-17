<?php

class HomeController extends AppController {

    /**
     * Used to tell the framework that in this controller we aren't using any model class
     * @var array 
     */
    public $uses = array();

    function index() {
        //Passing the $var var to the view
        $this->var = "Hello world from controller";
    }

}