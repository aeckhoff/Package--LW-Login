<?php

namespace LwLogin\Model\Interfaces;

interface AuthObjectInterface
{

    public function login($SessionData);

    public function isLoggedIn();

    public function logout();
}
