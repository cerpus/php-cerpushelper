<?php

namespace Cerpus\Helper\Helpers;


function profile($key, $default = null, $requiredProfile = null) {

    $profile = $requiredProfile ?? \config('app.deploymentEnvironment');
    if( !is_null($profile) ){
        return \config($profile . "." . $key, \config($key, $default));
    }
    return \config($key, $default);
}
