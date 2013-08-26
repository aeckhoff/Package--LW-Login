<?php

namespace LwLogin\View;

class LogoutConfirmedView extends \LWmvc\View\View
{

    protected $view;
    protected $config;
    protected $entity;

    public function __construct()
    {
        parent::__construct();
        $this->view = new \lw_view(dirname(__FILE__).'/Templates/LogoutConfirmed.phtml');
    }

    public function render()
    {
        $this->view->loginUrl = \lw_page::getInstance()->getUrl(array("cmd" => "showLogin"));
        return $this->view->render();
    }

}
