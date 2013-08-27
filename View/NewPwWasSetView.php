<?php

namespace LwLogin\View;

class NewPwWasSetView extends \LWmvc\View\View
{

    protected $view;
    protected $error;
    protected $params;

    public function __construct()
    {
        parent::__construct();        
    }
    
    public function setLanguage($lang)
    {
        $this->lang = $lang;
    }
    
    public function setUseOnlyPwLost($use)
    {
        $this->useOnlyPwLost = $use;
    }
    
    public function setUseDefaultCss($use)
    {
        $this->useDefaultCss = $use;
    }

    public function render()
    {
        if($this->lang == "de"){
            $view = new \lw_view(dirname(__FILE__).'/Templates/de/NewPwWasSet.phtml');
        }else{
            $view = new \lw_view(dirname(__FILE__).'/Templates/en/NewPwWasSet.phtml');
        }

        $view->useDefaultCss = $this->useDefaultCss;
        $view->useOnlyPwLost = $this->useOnlyPwLost;
        $view->loginUrl = \lw_page::getInstance()->getUrl(array("cmd" => "showLogin"));

        return $view->render();
    }

}
