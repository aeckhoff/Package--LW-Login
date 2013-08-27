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
            $view = new \lw_view(dirname(__FILE__).'/Templates/de/Logout.phtml');
        }else{
            $view = new \lw_view(dirname(__FILE__).'/Templates/en/Logout.phtml');
        }
        
        $view->useDefaultCss = $this->useDefaultCss;
        $view->logoutUrl = \lw_page::getInstance()->getUrl(array("cmd" => "Logout"));
        return $view->render();
    }

}
