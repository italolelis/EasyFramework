<?php

namespace Easy\View;

use Easy\View\HelperCollection;

class Helper
{

    /**
     * The View object
     * @var View 
     */
    protected $view;

    /**
     * Collection of Helpers
     * @var HelperCollection 
     */
    protected $Helpers;

    public function __construct(HelperCollection $helpers)
    {
        $this->Helpers = $helpers;
        $this->view = $helpers->getView();
    }

    public function __get($helper)
    {
        return $this->view->{$helper};
    }

}