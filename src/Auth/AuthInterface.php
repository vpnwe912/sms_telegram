<?php
namespace Svobo\SmsConsiderPpUa\Auth;

interface AuthInterface {
    public function login($username, $password);
}
