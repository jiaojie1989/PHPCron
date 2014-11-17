<?php

/**
 * Try to exec a url link, if there is an error occurs
 * throw this exception out, just with the error detail
 *
 * @package curl
 * @author JIAO Jie <jiaojie1989@gmail.com>
**/
class Lib_Util_Curl_Exception extends Exception {

    public function getCurlError() {
        $error = $this->getMessage();
        return $error;
    }

}
