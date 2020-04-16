<?php


namespace Cerpus\Helper\Helpers;


use Cerpus\AuthCore\AuthCoreIntegration;
use Cerpus\AuthCore\SessionInterface;

class Session implements AuthCoreIntegration {

    private $session = null;

    public function session(): SessionInterface {
        if ($this->session === null) {
            $this->session = new AuthLibSession();
        }
        return $this->session;
    }
}
