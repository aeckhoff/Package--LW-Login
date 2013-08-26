<?php

namespace LwLogin\Model\Interfaces;

interface ConnectionObjectInterface
{

    public function setPasswordValidator($rules);

    public function CheckLogin($loginname, $loginpass);

    public function PwLostRequest($userIdentifier, $hash);

    public function IsCombinationOfIdAndHashValid($id, $hash);

    public function SetNewPassword($id, $hash, $postArray);
}
