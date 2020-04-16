<?php


namespace Cerpus\Helper\Helpers;


use Cerpus\AuthCore\SessionInterface;
use Illuminate\Support\Facades\Session;

class AuthLibSession implements SessionInterface {

    public function put($key, $value) {
        Session::put([$key => $value]);
    }

    public function remove($key) {
        Session::remove($key);
    }

    public function exists($key): bool {
        return Session::exists($key) ? true : false;
    }

    public function get($key) {
        return Session::get($key);
    }
}