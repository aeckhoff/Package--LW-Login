<?php

namespace LwLogin\View;

class PwLostErrorView extends \LWmvc\View\View
{

    protected $view;
    protected $error;
    protected $formArray;

    public function __construct()
    {
        parent::__construct();
        $this->view = new \lw_view(dirname(__FILE__).'/Templates/PwLostError.phtml');
    }

    public function render()
    {
        $this->view->backUrl = \lw_page::getInstance()->getUrl(array("cmd" => "showLogin"));
        return $this->view->render();
    }

}
