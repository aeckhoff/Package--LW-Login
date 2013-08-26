<?php

namespace LwLogin\View;

class EmailNewPwSetView extends \LWmvc\View\View
{

    protected $view;
    protected $params;

    public function __construct()
    {
        parent::__construct();
        $config = \lw_registry::getInstance()->getEntry("config");
        $this->view = new \lw_view(dirname(__FILE__).'/Templates/EmailNewPwSet.phtml');
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function render()
    {
        $this->view->loginUrl = \lw_page::getInstance()->getUrl(array("cmd" => "showLogin"));
        $this->view->loginname = $this->params;
        return $this->view->render();
    }

}
