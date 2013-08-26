<?php

namespace LwLogin\Controller;

class Login extends \LWmvc\Controller\Controller
{

    protected $request;
    protected $config;
    protected $authObject = false;
    protected $LwLoginConnectionObject = false;
    protected $LwLoginTemplateObject;

    public function __construct($cmd, $oid)
    {
        parent::__construct($cmd, $oid);
        $this->request = \lw_registry::getInstance()->getEntry("request");
        $this->config = \lw_registry::getInstance()->getEntry("config");
        $objectResponse = \LWmvc\Model\CommandDispatch::getInstance()->execute("LwLogin", "TemplateObject", 'getTemplateObject', array(), array());
        $this->LwLoginTemplateObject = $objectResponse->getDataByKey('templateObject');
    }

    public function setLwLoginConnectionObject(\LwLogin\Model\Interfaces\ConnectionObjectInterface $object)
    {
        $this->LwLoginConnectionObject = $object;
    }
    
    public function setLwLoginTemplateObject($object)
    {
        $this->LwLoginTemplateObject = $object;
    }

    public function setAuthObject(\LwLogin\Model\Interfaces\AuthObjectInterface $object)
    {
        $this->authObject = $object;
    }

    public function execute()
    {
        if(!$this->LwLoginConnectionObject){
            throw new \LwLogin\Model\Exceptions\MissingLwLoginConnectionObjectException();
        }
        
        if(!$this->authObject){
            throw new \LwLogin\Model\Exceptions\MissingLwLoginAuthObjectException();
        }
        
        $method = $this->getCommand() . "Action";
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        else {
            throw new \LWmvc\Model\ControllerMethodNotFoundException($this->getCommand());
        }
    }

    public function showLoginAction($error = false)
    {
        if (!$this->authObject->isLoggedIn()) {
            $formView = $this->LwLoginTemplateObject->getViewByName("LoginView");
            $formView->setErrors($error);
            return $this->returnRenderedView($formView);
        }
        else {
            $view = $this->LwLoginTemplateObject->getViewByName("LogoutView");
            return $this->returnRenderedView($view);
        }
    }

    public function LoginAction()
    {
        $loginname = $this->request->getAlnum("loginname");
        $loginpass = $this->request->getRaw("loginpass");

        $response = $this->LwLoginConnectionObject->CheckLogin($loginname, $loginpass);

        if ($response->getParameterByKey('loginOK')) {
            $userData = $response->getDataByKey("userData");
            $this->authObject->login($userData);
            return $response;
        }

        return $this->showLoginAction(true);
    }

    public function LogoutAction()
    {
        if ($this->authObject->isLoggedIn()) {
            $this->authObject->logout();
            $view = $this->LwLoginTemplateObject->getViewByName("LogoutConfirmedView");
            return $this->returnRenderedView($view);
        }
        else {
            return $this->buildReloadResponse(array("cmd" => "showLogin"));
        }
    }

    public function showPwLostAction()
    {
        $pwLostView = $this->LwLoginTemplateObject->getViewByName("PwLostView");
        return $this->returnRenderedView($pwLostView);
    }

    public function pwLostRequestAction()
    {
        $userIdentifier = $this->request->getRaw("userIdentifier");
        $hash = sha1("Logic-Works GmbH" . date("YmdHis") . rand(0, 10000));

        $response = $this->LwLoginConnectionObject->PwLostRequest($userIdentifier, $hash);

        if ($response->getParameterByKey("accounts")) {
            $params = array();
            $accounts = $response->getDataByKey("accounts");

            foreach ($accounts as $acc) {
                $params[] = array("loginname" => $acc["loginname"], "id" => $acc["id"], "hash" => $acc["hash"]);
            }

            return $this->sendEmail($accounts[0]["email"], $params);
        }

        return $this->buildReloadResponse(array("cmd" => "showLogin"));
    }

    public function pwLostAction()
    {
        $userIdHashArray = explode("_", $this->request->getRaw("hash"));

        $response = $this->LwLoginConnectionObject->IsCombinationOfIdAndHashValid($userIdHashArray[0], $userIdHashArray[1]);

        if ($response->getParameterByKey("idAndHashCombination")) {
            $view = $this->LwLoginTemplateObject->getViewByName("SetNewPwView");
            $view->setParams(array("hash" => $this->request->getRaw("hash")));
            return $this->returnRenderedView($view);
        }

        $errorView = $this->LwLoginTemplateObject->getViewByName("PwLostErrorView");
        return $this->returnRenderedView($errorView);
    }

    public function setNewPwAction()
    {
        $userIdHashArray = explode("_", $this->request->getRaw("hash"));

        $response = $this->LwLoginConnectionObject->SetNewPassword($userIdHashArray[0], $userIdHashArray[1], $this->request->getPostArray());

        if ($response->getParameterByKey("newPwSet")) {
            return $this->sendEmail($response->getDataByKey("email"), $response->getDataByKey("loginname"), true);
        }
        else {
            $view = $this->LwLoginTemplateObject->getViewByName("SetNewPwView");
            $view->setError($response->getDataByKey("error"));
            $view->setParams(array("hash" => $this->request->getRaw("hash")));
            return $this->returnRenderedView($view);
        }
    }

    private function sendEmail($email, $params, $bool = false)
    {
        $mailer = new \LwMailer\Controller\LwMailer($this->config["mailConfig"], $this->config);

        if (!$bool) {
            $EmailView = $this->LwLoginTemplateObject->getViewByName("EmailView");
            $EmailView->setParams($params);
            $content = $EmailView->render();
            $subject = "Password Lost";
        }
        else {
            $EmailView = $this->LwLoginTemplateObject->getViewByName("EmailNewPwSetView");
            $EmailView->setParams($params);
            $content = $EmailView->render();
            $subject = "New Password";
        }
        
        $mailInformationArray = array(
            "toMail" => $email,
            "subject" => $subject,
            "message" => $content
        );
        
        $mailer->sendMail($mailInformationArray);
        return $this->buildReloadResponse(array("cmd" => "showLogin"));
    }

}
