<?php

namespace LwLogin\View;

class EmailView extends \LWmvc\View\View
{

    protected $view;
    protected $params;

    public function __construct()
    {
        parent::__construct();
        $this->view = new \lw_view(dirname(__FILE__).'/Templates/Email.phtml');
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function render()
    {

        $urlArray = array();
        foreach ($this->params as $param) {
            $urlArray[$param["loginname"]] = \lw_page::getInstance()->getUrl(array("cmd" => "pwLost", "hash" => urlencode($param["id"] . "_" . $param["hash"])));
        }

        $this->view->array = $urlArray;

        return $this->view->render();
    }

}
