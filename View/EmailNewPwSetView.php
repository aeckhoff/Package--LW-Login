<?php

namespace LwLogin\View;

class EmailNewPwSetView extends \LWmvc\View\View
{

    protected $view;
    protected $params;

    public function __construct()
    {
        parent::__construct();
    }

    public function setParams($params)
    {
        $this->params = $params;
    }
    
    public function setLanguage($lang)
    {
        $this->lang = $lang;
    }

    public function render()
    {
        if($this->lang == "de"){            
            $view = new \lw_view(dirname(__FILE__) . '/Templates/de/EmailNewPwSet.phtml');
        }else{
            $view = new \lw_view(dirname(__FILE__) . '/Templates/en/EmailNewPwSet.phtml');
        }
        
        $view->loginUrl = \lw_page::getInstance()->getUrl(array("cmd" => "showLogin"));
        $view->loginname = $this->params;
        return $view->render();
    }

}
