<?php

namespace LwLogin\Controller;

class Login extends \LWmvc\Controller\Controller
{

    protected $request;
    protected $config;
    protected $authObject = false;
    protected $LwLoginConnectionObject = false;
    protected $LwLoginTemplateObject;
    protected $lang = "de";
    protected $emailNotificationAfterPasswordChange = false;
    protected $useOnlyPwLost = false;
    protected $useDefaultCss = true;

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
    
    public function sendEmailNotificationAfterSuccessfullyPasswordChange($send)
    {
        $this->emailNotificationAfterPasswordChange = $send;
    }
    
    public function setLanguage($lang)
    {
        $this->lang = $lang;
    }
    
    public function setUseOnlyPwLostFunction($use)
    {
        $this->useOnlyPwLost = $use;
    }
    
    public function setUseDefaultCss($use)
    {
        $this->useDefaultCss = $use;
    }

    public function execute()
    {
        if(!$this->LwLoginConnectionObject){
            throw new \LwLogin\Model\Exceptions\MissingLwLoginConnectionObjectException();
        }
        
        if(!$this->authObject){
            throw new \LwLogin\Model\Exceptions\MissingLwLoginAuthObjectException();
        }
        
        $cmd = $this->getCommand();
        
        if($this->useOnlyPwLost){
            $pwLostCommandsArray = array("showPwLost", "pwLostRequest", "pwLostRequestConfirm", "pwLost", "setNewPw");
            if(!in_array($cmd, $pwLostCommandsArray)){
                $cmd = "showPwLost";
            }
        }
        
        $method = $cmd . "Action";
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
            $formView->setLanguage($this->lang);
            $formView->setUseDefaultCss($this->useDefaultCss);
            return $this->returnRenderedView($formView);
        }
        else {
            $view = $this->LwLoginTemplateObject->getViewByName("LogoutView");
            $view->setLanguage($this->lang);
            $view->setUseDefaultCss($this->useDefaultCss);
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

        return $this->showLoginAction($response->getDataByKey("error"));
    }

    public function LogoutAction()
    {
        if ($this->authObject->isLoggedIn()) {
            $this->authObject->logout();
            $view = $this->LwLoginTemplateObject->getViewByName("LogoutConfirmedView");
            $view->setLanguage($this->lang);
            $view->setUseDefaultCss($this->useDefaultCss);
            return $this->returnRenderedView($view);
        }
        else {
            return $this->buildReloadResponse(array("cmd" => "showLogin"));
        }
    }

    public function showPwLostAction()
    {
        $pwLostView = $this->LwLoginTemplateObject->getViewByName("PwLostView");
        $pwLostView->setLanguage($this->lang);
        $pwLostView->setUseOnlyPwLost($this->useOnlyPwLost);
        $pwLostView->setUseDefaultCss($this->useDefaultCss);
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

            $this->sendEmail($accounts[0]["email"], $params);
        }

        return $this->buildReloadResponse(array("cmd" => "pwLostRequestConfirm"));
    }
    
    public function pwLostRequestConfirmAction()
    {
        $view = $this->LwLoginTemplateObject->getViewByName("PwLostMailWasSentView");
        $view->setLanguage($this->lang);
        $view->setUseOnlyPwLost($this->useOnlyPwLost);
        $view->setUseDefaultCss($this->useDefaultCss);
        return $this->returnRenderedView($view);
    }

    public function pwLostAction($errors = false)
    {
        $userIdHashArray = explode("_", $this->request->getRaw("hash"));

        $response = $this->LwLoginConnectionObject->IsCombinationOfIdAndHashValid($userIdHashArray[0], $userIdHashArray[1]);

        if ($response->getParameterByKey("idAndHashCombination")) {
            $view = $this->LwLoginTemplateObject->getViewByName("SetNewPwView");
            $view->setParams(array("hash" => $this->request->getRaw("hash")));
            $view->setLanguage($this->lang);
            $view->setErrors($errors);
            $view->setUseOnlyPwLost($this->useOnlyPwLost);
            $view->setUseDefaultCss($this->useDefaultCss);
            return $this->returnRenderedView($view);
        }

        $errorView = $this->LwLoginTemplateObject->getViewByName("PwLostErrorView");
        $errorView->setLanguage($this->lang);
        $errorView->setUseOnlyPwLost($this->useOnlyPwLost);
        $errorView->setUseDefaultCss($this->useDefaultCss);
        return $this->returnRenderedView($errorView);
    }

    public function setNewPwAction()
    {
        $userIdHashArray = explode("_", $this->request->getRaw("hash"));

        $response = $this->LwLoginConnectionObject->SetNewPassword($userIdHashArray[0], $userIdHashArray[1], $this->request->getPostArray());

        if ($response->getParameterByKey("newPwSet")) {
            if($this->emailNotificationAfterPasswordChange){
                 $this->sendEmail($response->getDataByKey("email"), $response->getDataByKey("loginname"), true);
            }
            $view = $this->LwLoginTemplateObject->getViewByName("NewPwWasSetView");
            $view->setLanguage($this->lang);
            $view->setUseOnlyPwLost($this->useOnlyPwLost);
            $view->setUseDefaultCss($this->useDefaultCss);
            return $this->returnRenderedView($view);
        }
        else {
            return $this->pwLostAction($response->getDataByKey("error"));
        }
    }

    private function sendEmail($email, $params, $bool = false)
    {
        $mailer = new \LwMailer\Controller\LwMailer($this->config["mailConfig"], $this->config);

        if (!$bool) {
            $EmailView = $this->LwLoginTemplateObject->getViewByName("EmailView");
            $EmailView->setParams($params);
            $EmailView->setLanguage($this->lang);
            $content = $EmailView->render();
            if($this->lang == "de"){
                $subject = "Passwort verloren";
            }else{                
                $subject = "Password Lost";
            }
        }
        else {
            $EmailView = $this->LwLoginTemplateObject->getViewByName("EmailNewPwSetView");
            $EmailView->setParams($params);
            $EmailView->setLanguage($this->lang);
            $content = $EmailView->render();
            if($this->lang == "de"){
                $subject = "Neues Passwort";
            }else{                
                $subject = "New password";
            }
        }
        
        $mailInformationArray = array(
            "toMail" => $email,
            "subject" => $subject,
            "message" => $content
        );
        
        $mailer->sendMail($mailInformationArray);
    }

}
