<?php

namespace LwLogin\View;

class LoginView extends \LWmvc\View\View
{
    protected $config;
    protected $error;

    public function __construct()
    {
        parent::__construct();
    }

    public function setErrors($error)
    {
        $this->error = $error;
    }
    
    public function setLanguage($lang)
    {
        $this->lang = $lang;
    }
    
    public function setUseDefaultCss($use)
    {
        $this->useDefaultCss = $use;
    }

    public function render()
    {
        if($this->lang == "de"){
            $view = new \lw_view(dirname(__FILE__).'/Templates/de/Login.phtml');
        }else{
            $view = new \lw_view(dirname(__FILE__).'/Templates/en/Login.phtml');
        }
        
        $view->useDefaultCss = $this->useDefaultCss;
        $view->actionUrl = \lw_page::getInstance()->getUrl(array("cmd" => "Login"));
        $view->pwlostUrl = \lw_page::getInstance()->getUrl(array("cmd" => "showPwLost"));

        if ($this->error) {
            $view->lang = $this->lang;
            $view->error = $this->error;
        }

        return $view->render();
    }

}
