################################################################################

LwLogin Package Beschreibung

################################################################################

Stand : 27.August.2013 
Von:    Michael Mandt


Um das neue LwLogin Package nutzen zu können wird ein Loader für das Login-Package
benötigt, der für den Verwendungszweck spezifische Objekte bereitstellt.

Dieses Beispiel wird die Verwendung des Login-Package am Beispiel "Submission 
Tool 3.0 Login" beschreiben.


    Inhaltsübersicht:

    1.  Pluginaufbau -> subm_login_loader
    2.  Quellcode subm_login_loader.class.php mit Kommentierung
    3.  Quellcode subm_login_loader Autoloader
    4.  Detailbeschreibung ConnectionObject
    5.  Optionales Template Object

________________________________________________________________________________
1. Pluginaufbau subm_login_loader
--------------------------------------------------------------------------------

Das SubmLoader Plugin besteht aus 2 Komponenten:

    a) Die subm_login_loader.class.php ist die gewohnte Plugin-Standardklasse. 
       In diesem Beispiel werden die für das LoginPackage benötigten Objekte
       in der "buildPageOutput" Funktion geladen, einige Parameter gesetzt und
       diese Parameter und Objekte an das Login Package weitergereicht. 

       Der Output aus der LoginPackage Response wird im Frontend ausgegeben.

       Liste der Objekte und Parameter ( genaue Erläuterung unter Punkt )
       
           Objekte :
                - ein Authobject
                - ein LwLoginConnectionObject
                - ein optionales TemplateObject
    
           Parameter :
                - Sprachenkürzel
                - nur PwLost Funktionalität nutzen
                - Emailbenachrichtigung versenden, wenn ein neues Pw gesetzt wurde

    b) Einem Autoloader, der die Plugins "LWddd" und "LWmvc" laden kann und den
       Pfad ins Packageverzeichnis kennt.

________________________________________________________________________________
2. subm_login_loader.class.php mit Kommentierung
--------------------------------------------------------------------------------


<?php

class subm_login_loader extends lw_plugin
{

    protected $db;

    public function __construct()
    {
        parent::__construct();
        include_once(dirname(__FILE__) . '/Services/Autoloader.php');
        $autoloader = new \SubmLoginLoader\Services\Autoloader();
        $autoloader->setConfig($this->config);

        $this->authKeeper = new \LwAuthorityKeeper\LwAuthorityKeeper("LwSubmission");
        /*
         * Das authKeeper Objekt ist das Submission-Tool spezifische Authentifizierungsobjekt.
         * Wichtig ist, dass jedes AuthObjekt, was mit dem LoginPackage genutzt werden soll das 
         * Authentifiezierungsinterface  \LwLogin\Model\Interfaces\AuthObjectInterface implementiert.
         * 
         * Die Pflichtfunktionen sind:
         *      - public function login($SessionData);
         *      - public function isLoggedIn();
         *      - public function logout();
         * 
         */
    }

    public function buildPageOutput()
    {
        $useOnlyPwLost = true;
        /*
         * Der $useOnlyPwLost Flagg soll nur auf true gesetzt werden, wenn man ausschließlich die
         * PwLost Funktionalität des LoginPackages benötigt wird. Das Login Formular wird dann
         * nicht angezeigt und bei dem Versuch das Login Formular über die direkte eingabe des
         * des entsprechenden Cmds aufzurufen wird automatisch das PwLost Forumular geladen. 
         */
        
        if ($this->request->getAlnum("cmd")) {
            $cmd = $this->request->getAlnum("cmd");
        }
        else {
            if($useOnlyPwLost){
                $cmd = 'showPwLost';
            }else{
                $cmd = 'showLogin';
            }
        }

        $objectResponse = \LWmvc\Model\CommandDispatch::getInstance()->execute("LwSubmissionAccounting", "LwLoginConnectionObjectSubmission", 'getConnectionObjectSubmission', array(), array());
        $LwLoginConnectionObject = $objectResponse->getDataByKey('connectionObject');
        /*
         * Hier wird das Submission-Tool spezifische Verbindungsobject geladen. Dieses Verbindungsobjekt ist die
         * Schnittstelle zwischen dem LoginPackage und den Submission Userdaten, wo auch immer diese gespeichert
         * sind.
         * 
         * Wichtig ist, dass jedes ConnectionObjekt, was mit dem LoginPackage genutzt werden soll das 
         * ConnectionObjectinterface  \LwLogin\Model\Interfaces\ConnectionObjectInterface implementiert.
         * 
         * Die Pflichtfunktionen sind:
         *      - public function setPasswordValidator($rules);
         *      - public function CheckLogin($loginname, $loginpass);
         *      - public function PwLostRequest($userIdentifier, $hash);
         *      - public function IsCombinationOfIdAndHashValid($id, $hash);
         *      - public function SetNewPassword($id, $hash, $postArray);
         * 
         */

        $Controller = new \LwLogin\Controller\Login($cmd, $this->params['oid']);
        $Controller->setLwLoginConnectionObject($LwLoginConnectionObject);
        /*
         * Setter für das oben beschriebene ConnectionObject.
         * 
         * Ein nicht gesetztes ConnectionObject wird eine Exception werfen, da dieses Objekt vorhanden sein muss.
         */
        
        $Controller->setAuthObject($this->authKeeper);
        /*
         * Setter für das oben beschriebene AuthObject.
         * 
         * Ein nicht gesetztes AuthObject wird eine Exception werfen, da dieses Objekt vorhanden sein muss.
         */
        
        $Controller->setLanguage("de");
        /*
         * Erlaubte Parameter für die Sprachen sind "de" oder "en". Sprachausgabe
         * erfolgt dann entsprechend der angegebenen Sprache, sollte der Setter nicht
         * aufgerufen werden, dann ist LoginPackage "de" als Standardwert angegeben.
         */
        
        $Controller->sendEmailNotificationAfterSuccessfullyPasswordChange(false);
        /*
         * Wird dieser Parameter auf True gesetzt, dann wird nach der erfolgreichen
         * Passwrotänderung eine Bestätigungsmail an den Nutzer geschickt, dass
         * das Passwort für seinen Account geändert wurde.
         * Sollte dieser Setter nicht aufgerufen werden, dann ist im LoginPackage
         * false als Standardwert angegeben.
         */
        
        $Controller->setUseOnlyPwLostFunction($useOnlyPwLost);
        /*
         * Dies ist die Setter Funktion für den oben beschriebenen Flagg $useOnlyPwLost.
         * Sollte dieser Setter nicht aufgerufen werden, dann ist im LoginPackage
         * false als Standardwert angegeben.
         */
        
        $Controller->setUseDefaultCss(true);
        /*
         * Es wird festgelegt, ob das Standard CSS zu den Standard Templates genutzt
         * werden soll.
         * Sollte dieser Setter nicht aufgerufen werden, dann ist im LoginPackage
         * true als Standardwert angegeben.
         */

        try {
            $response = $Controller->execute();
        } catch (\LwLogin\Model\Exceptions\MissingLwLoginConnectionObjectException $exc) {
            die("LwLogin ConnectionObject was not set.");
        } catch (\LwLogin\Model\Exceptions\MissingLwLoginAuthObjectException $exc) {
            die("LwLogin AuthObject was not set.");
        }

        if ($response->getParameterByKey('loginOK')) {
            $this->pageReload(\lw_page::getInstance(intval($this->config["submission"]["pageIndex"]["welcome_" . $this->authKeeper->getValueByKey("role_id")]))->getUrl());
        }

        if ($response->getParameterByKey('cmd')) {
            if (intval($response->getParameterByKey('redirectIndex')) > 0) {
                $url = \lw_page::getInstance(intval($response->getParameterByKey('redirectIndex')))->getUrl($response->getParameterArray());
            }
            else {
                $url = \lw_page::getInstance()->getUrl($response->getParameterArray());
            }
            $this->pageReload($url);
        }
        elseif (intval($response->getParameterByKey('redirectIndex')) > 0) {
            $url = \lw_page::getInstance(intval($response->getParameterByKey('redirectIndex')))->getUrl($response->getParameterArray());
            $this->pageReload($url);
        }
        else {
            if ($response->getParameterByKey('die') == 1) {
                die($response->getOutputByKey('output'));
            }
            return $response->getOutputByKey('output');
        }
    }

    public function getOutput()
    {
        return "";
    }

    function deleteEntry()
    {
        return true;
    }

}

?>


________________________________________________________________________________
3.  Quellcode subm_login_loader Autoloader
--------------------------------------------------------------------------------

<?php

namespace SubmLoginLoader\Services;

class Autoloader
{
    public function __construct()
    {
        spl_autoload_register(array($this, 'loader'));
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    private function loader($className)
    {
        if (strstr($className, 'LWddd')) {
            $config = \lw_registry::getInstance()->getEntry('config');
            $path = $this->config['plugin_path']['lw'].'lw_ddd';
            $filename = str_replace('LWddd', $path, $className);
        }
        elseif (strstr($className, 'LWmvc')) {
            $config = \lw_registry::getInstance()->getEntry('config');
            $path = $this->config['plugin_path']['lw'].'lw_mvc';
            $filename = str_replace('LWmvc', $path, $className);
        }
        else {
            $className = str_replace("Factory", "", $className);
            $filename = $this->config['path']['package'].$className;
        }
        $filename = str_replace('\\', '/', $filename).'.php';
        
        if (is_file($filename)) {
            include_once($filename);
        }
    }

}

?>

________________________________________________________________________________
4.  Detailbeschreibung ConnectionObject
--------------------------------------------------------------------------------

Beispiel Submission-Tool Connection Object:
<?php
namespace LwSubmissionAccounting\Model\LwLoginConnectionObjectSubmission\Object;

class connectionObject extends \LWmvc\Model\Entity implements \LwLogin\Model\Interfaces\ConnectionObjectInterface
{
    protected $passwordValidator = false;
    
    public function setPasswordValidator($rules)
    {
        $this->passwordValidator = $rules;
    }

    public function CheckLogin($loginname, $loginpass)
    {
        /*
         * Die Kombination aus loginname und passwort wird geprüft, ob diese gültig ist.
         */        
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute(
                "LwSubmissionAccounting", "LwLoginConnectionSubmission", 'CheckLogin', array(), array('loginname' => $loginname, "loginpass" => $loginpass)
        );

        return $response;
        /*
         * Die Response muss folgende Rückgabewerde haben :
         *  
         *      Ist der Login Ok :
         *          $response Parameter setzen "loginOK" = true;
         *          $response Data setzen "userData" = Array mit den UserInformationen (submission bsp: id, loginname, email, role_id )
         *      
         *      Ist der Login nicht Ok:
         *          $response Parameter setzen "loginOK" = false;
         *          $response Data setzeb "error" = Array mit Fehlermeldung ( array("de" => "ungültige Logindaten", "en" => "invalid logindata" ) )
         */
    }

    public function PwLostRequest($userIdentifier, $hash)
    {
        /*
         * Anhand des $userIdentifier wird geprüft ob der entsprechende Account vorhanden ist.
         */
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute(
                "LwSubmissionAccounting", "LwLoginConnectionSubmission", 'PwLostRequest', array(), array('email' => $userIdentifier, 'hash' => $hash)
        );

        return $response;
        
        /*
         * Die Response muss folgende Rückgabewerde haben :
         * 
         *      Ist ein oder mehrer Accounts unter diesem Useridentifier vorhanden :
         *          $response Parameter setzen "accounts" = true;
         *          $response Data setzen "accounts" = array der user 
         * 
         *      Ist kein Account unter diesem Useridentifier vorhanden :
         *          $response Parameter setzen "accounts" = false; 
         * 
         * Der/Die vorhandenen User als Array zurückgeben, da über das array iteriert
         * und die email mit dem Link zum Passwort ändern verschickt wird.
         * 
         * mit folgender schleife wird durch das userarray gegangen:
         * 
         * foreach ($accounts as $acc) {
         *      $params[] = array("loginname" => $acc["loginname"], "id" => $acc["id"], "hash" => $acc["hash"]);  loginname = Identifier im Submission Tool Beispiel
         *  }
         */
    }

    public function IsCombinationOfIdAndHashValid($id, $hash)
    {
        /*
         * Die Kombination von User-Id und Hash wird geprüft.
         */
        
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute(
                "LwSubmissionAccounting", "LwLoginConnectionSubmission", 'IsCombinationOfIdAndHashValid', array(), array('id' => $id, "hash" => $hash)
        );

        return $response;
        
        /*
         * Die Response muss folgende Rückgabewerde haben :
         * 
         *      Ist die Kombination vorhanden :
         *          $response Parameter setzen "idAndHashCombination" = true;
         *  
               Ist die Kombination nicht vorhanden :
         *          $response Parameter setzen "idAndHashCombination" = false;
         */
    }

    public function SetNewPassword($id, $hash, $postArray)
    {
        /*
         * Das neue Passwort wird gesetzt.
         */
        $response = \LWmvc\Model\CommandDispatch::getInstance()->execute(
                "LwSubmissionAccounting", "LwLoginConnectionSubmission", 'SetNewPassword', array("id" => $id, "hash" => $hash), array('postArray' => $postArray, 'passwordValidator' => $this->passwordValidator)
        );

        return $response;
        
        /*
         * Die Response muss folgende Rückgabewerde haben :
         * 
         *      Ist das neue Passwort valide :
         *          $response Parameter setzen "newPwSet" = true;
         *          $response Data setzen "email" = useremail;
         *          $response Data setzen "loginname" = userloginname;
         * 
         *      Ist das neue Passwort valide :
         *          $response Parameter setzen "newPwSet" = false;
         *          $response Data setzen "error" = array( array("de" => "fehler1", "en" => "error1" ), array("de" => "fehler2", "en" => "error2" ), ... );
         */
    }
    
}
?>

________________________________________________________________________________
5.  Optionales Template Object
--------------------------------------------------------------------------------

Um die Default Templates zu überschreiben muss man ein eigenes TemplateObject
laden und im LoginPackage setzen.

Im Loader ist dies einfach zu machen :

<?php 
    $controller->setLwLoginTemplateObject($templateObject);
?>
Template Object Bsp:

<?php
namespace LwLogin\Model\TemplateObject\Object;

class templateObject extends \LWmvc\Model\Entity
{
    public function __construct($id = false)
    {
        parent::__construct($id);
    }
    
    public function getViewByName($name)
    {
        $viewNamespace = "\\LwLogin\\View\\".$name;
        return new $viewNamespace();
    }
}
?>

Im TempalteObject muss der Pfad zu den Views der neuen Templates angegeben werden.
diese werden dann geladen. Momentan kann nur das gesamte Templatepaket überschrieben!!!

Folgende Viewnamen finden Verwendung im LoginPackage:
-EmailNewPwSetView.php ( Bestätigungsmail nach PW Set )
-EmailView.php ( Email mit Link zum neuem Pw setzen )
-LoginView.php ( Login Formular )
-LogoutConfirmedView.php ( Ausloggen bestätigt )
-LogoutOutView.php ( Logout Button anstatt LoginForm )
-NewPwWasSetView ( Bestätigungsausgabe, dass neues Pw gesetzt wurde )
-PwLostErrorView ( Kombination Id und Hash nicht valid - Fehlermeldungsausgabe )
-PwLostView ( Form zur Eingabe des Identifiers )
-SetNewPwView ( Form zur Eingabe des neuen Pws )


Bsp: Laden eines TemplateObjects ( LoginPackage intere TemplateObject )
<?php 
    $objectResponse = \LWmvc\Model\CommandDispatch::getInstance()->execute("LwLogin", "TemplateObject", 'getTemplateObject', array(), array());
    $this->LwLoginTemplateObject = $objectResponse->getDataByKey('templateObject');
?>