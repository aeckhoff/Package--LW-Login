<?php

namespace LwLogin\View;

class EmailView extends \LWmvc\View\View
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
            $view = new \lw_view(dirname(__FILE__).'/Templates/de/Email.phtml');
        }else{
            $view = new \lw_view(dirname(__FILE__).'/Templates/en/Email.phtml');
        }

        $urlArray = array();
        foreach ($this->params as $param) {
            $urlArray[$param["loginname"]] = \lw_page::getInstance()->getUrl(array("cmd" => "pwLost", "hash" => urlencode($param["id"] . "_" . $param["hash"])));
        }

        $view->array = $urlArray;

        return $view->render();
    }

}
