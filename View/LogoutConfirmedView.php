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
            $view = new \lw_view(dirname(__FILE__).'/Templates/de/LogoutConfirmed.phtml');
        }else{
            $view = new \lw_view(dirname(__FILE__).'/Templates/en/LogoutConfirmed.phtml');
        }
        
        $view->useDefaultCss = $this->useDefaultCss;
        $view->loginUrl = \lw_page::getInstance()->getUrl(array("cmd" => "showLogin"));
        return $view->render();
    }

}
