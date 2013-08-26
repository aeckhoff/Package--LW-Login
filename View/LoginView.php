<?php

namespace LwLogin\View;

class LoginView extends \LWmvc\View\View
{

    protected $view;
    protected $config;
    protected $entity;
    protected $error;

    public function __construct()
    {
        parent::__construct();
        $this->view = new \lw_view(dirname(__FILE__).'/Templates/Login.phtml');
    }

    public function setErrors($error)
    {
        $this->error = $error;
    }

    public function render()
    {
        $this->view->actionUrl = \lw_page::getInstance()->getUrl(array("cmd" => "Login"));
        $this->view->pwlostUrl = \lw_page::getInstance()->getUrl(array("cmd" => "showPwLost"));

        if ($this->error) {
            $this->view->error = true;
        }
        else {
            $this->view->error = false;
        }

        return $this->view->render();
    }

}
