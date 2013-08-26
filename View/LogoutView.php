<?php

namespace LwLogin\View;

class LogoutView extends \LWmvc\View\View
{

    protected $view;
    protected $config;
    protected $entity;

    public function __construct()
    {
        parent::__construct();
        $this->view = new \lw_view(dirname(__FILE__).'/Templates/Logout.phtml');
    }

    public function render()
    {
        $this->view->logoutUrl = \lw_page::getInstance()->getUrl(array("cmd" => "Logout"));
        return $this->view->render();
    }

}
