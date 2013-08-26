<?php

namespace LwLogin\View;

class PwLostView extends \LWmvc\View\View
{

    protected $view;
    protected $config;
    protected $entity;
    protected $error;

    public function __construct()
    {
        parent::__construct();
        $this->view = new \lw_view(dirname(__FILE__).'/Templates/PwLost.phtml');
    }

    public function setErrors($error)
    {
        $this->error = $error;
    }

    public function render()
    {
        $this->view->actionUrl = \lw_page::getInstance()->getUrl(array("cmd" => "pwLostRequest"));
        $this->view->loginUrl = \lw_page::getInstance()->getUrl(array("cmd" => "showLogin"));

        return $this->view->render();
    }

}
