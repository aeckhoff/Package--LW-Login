<?php

namespace LwLogin\View;

class SetNewPwView extends \LWmvc\View\View
{

    protected $view;
    protected $error;
    protected $params;

    public function __construct()
    {
        parent::__construct();        
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }
    
    public function setLanguage($lang)
    {
        $this->lang = $lang;
    }
    
    public function setUseDefaultCss($use)
    {
        $this->useDefaultCss = $use;
    }
    
    public function setUseOnlyPwLost($use)
    {
        $this->useOnlyPwLost = $use;
    }

    public function render()
    {
        if($this->lang == "de"){
            $view = new \lw_view(dirname(__FILE__).'/Templates/de/SetNewPw.phtml');
        }else{
            $view = new \lw_view(dirname(__FILE__).'/Templates/en/SetNewPw.phtml');
        }
        
        $view->useDefaultCss = $this->useDefaultCss;
        $view->useOnlyPwLost = $this->useOnlyPwLost;
        $view->actionUrl = \lw_page::getInstance()->getUrl(array("cmd" => "setNewPw", "hash" => $this->params["hash"]));
        $view->loginUrl = \lw_page::getInstance()->getUrl(array("cmd" => "showLogin"));

        if ($this->errors) {
            $view->errors = $this->errors;
            $view->lang = $this->lang;
        }

        return $view->render();
    }

}
