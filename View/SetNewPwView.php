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
        $this->view = new \lw_view(dirname(__FILE__).'/Templates/SetNewPw.phtml');
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function render()
    {
        $this->view->actionUrl = \lw_page::getInstance()->getUrl(array("cmd" => "setNewPw", "hash" => $this->params["hash"]));
        $this->view->loginUrl = \lw_page::getInstance()->getUrl(array("cmd" => "showLogin"));

        if (!empty($this->error)) {
            $this->view->error = $this->error;
        }
        else {
            $this->view->error = false;
        }

        return $this->view->render();
    }

}
