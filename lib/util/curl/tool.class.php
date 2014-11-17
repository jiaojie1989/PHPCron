<?php

if (!defined('CURL_MAX_CONNECT')) {
    define('CURL_MAX_CONNECT', 3);
}
if (!defined('CURL_MAX_RECEIVE')) {
    define('CURL_MAX_RECEIVE', 8);
}

/**
 * A basic CURL wrapper
 *
 * See the README for documentation/examples or http://php.net/curl for more information about the libcurl extension for PHP
 *
 * @package curl
 * @author Sean Huber <shuber@huberry.com>
 * */
class Lib_Util_Curl_Tool {
    /**
     * The file to read and write cookies to for requests
     *
     * @var string
     * */
    //public $cookie_file;

    /**
     * Determines whether or not requests should follow redirects
     *
     * @var boolean
     * */
    public $follow_redirects = true;

    /**
     * An associative array of headers to send along with requests
     *
     * @var array
     * */
    public $headers = array();

    /**
     * An associative array of CURLOPT options to send along with requests
     *
     * @var array
     * */
    public $options = array();

    /**
     * The referer header to send along with requests
     *
     * @var string
     * */
    public $referer;

    /**
     * The user agent to send along with requests
     *
     * @var string
     * */
    public $user_agent;

    /**
     * Stores an error string for the last request if one occurred
     *
     * @var string
     * @access protected
     * */
    protected $error = '';

    /**
     * Stores resource handle for the current CURL request
     *
     * @var resource
     * @access protected
     * */
    protected $request;
    protected $connection_time;
    protected $receive_time;
    private static $instance;
    /**
     * Initializes a Curl object
     *
     * Sets the $cookie_file to "curl_cookie.txt" in the current directory
     * Also sets the $user_agent to $_SERVER['HTTP_USER_AGENT'] if it exists, 'Curl/PHP '.PHP_VERSION.' (http://github.com/shuber/curl)' otherwise
     * */
    private function __construct($connect_time = NULL, $receive_time = NULL) {
        //$this->cookie_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'curl_cookie.txt';
        $this->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'PHPCRON/CURL ' . PHP_VERSION . 'JIAOJIE';
        $this->connect_time = empty($connect_time) ? CURL_MAX_CONNECT : $connect_time;
        $this->receive_time = empty($receive_time) ? CURL_MAX_RECEIVE : $receive_time;
    }
    
    private function __clone() {}
    /**
     * 单例接口
     * @param type $connect_time
     * @param type $receive_time
     * @return Zwp_Util_Curl_Tool
     */
    public static function getInstance($connect_time = NULL, $receive_time = NULL) {
        if(!(self::$instance instanceof self)) {
            self::$instance = new self($connect_time, $receive_time);
        } else {
            self::$instance->setTime($connect_time, $receive_time);
        }
        return self::$instance;
    }
    /**
     * 设定超时时间
     * @param type $connect_time
     * @param type $receive_time
     */
    public function setTime($connect_time = NULL, $receive_time = NULL) {
        $this->connect_time = empty($connect_time) ? CURL_MAX_CONNECT : $connect_time;
        $this->receive_time = empty($receive_time) ? CURL_MAX_RECEIVE : $receive_time;
        return TRUE;
    }
    /**
     * Makes an HTTP DELETE request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse object
     * */
    function delete($url, $vars = array()) {
        return $this->request('DELETE', $url, $vars);
    }

    /**
     * Returns the error string of the current request if one occurred
     *
     * @return string
     * */
    function error() {
        return $this->error;
    }

    /**
     * Makes an HTTP GET request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse
     * */
    function get($url, $vars = array()) {
        if (!empty($vars)) {
            $url .= (stripos($url, '?') !== false) ? '&' : '?';
            $url .= (is_string($vars)) ? $vars : http_build_query($vars, '', '&');
        }
        return $this->request('GET', $url);
    }

    /**
     * Makes an HTTP HEAD request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars
     * @return CurlResponse
     * */
    function head($url, $vars = array()) {
        return $this->request('HEAD', $url, $vars);
    }

    /**
     * Makes an HTTP POST request to the specified $url with an optional array or string of $vars
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse|boolean
     * */
    function post($url, $vars = array()) {
        return $this->request('POST', $url, $vars);
    }

    /**
     * Makes an HTTP PUT request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse|boolean
     * */
    function put($url, $vars = array()) {
        return $this->request('PUT', $url, $vars);
    }

    /**
     * Makes an HTTP request of the specified $method to a $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $method
     * @param string $url
     * @param array|string $vars
     * @return CurlResponse|boolean
     * */
    function request($method, $url, $vars = array()) {
        $this->error = '';
        $this->request = curl_init();
        if (is_array($vars))
            $vars = http_build_query($vars, '', '&');

        $this->set_request_method($method);
        $this->set_request_options($url, $vars);
        $this->set_request_headers();

        $response = curl_exec($this->request);

        if ($response) {
            $response = new Zwp_Util_Curl_Response($response);
        } else {
            //$this->error = curl_errno($this->request).' - '.curl_error($this->request);
            $this->error = curl_error($this->request);
        }

        curl_close($this->request);

        return $response;
    }

    /**
     * Formats and adds custom headers to the current request
     *
     * @return void
     * @access protected
     * */
    protected function set_request_headers() {
        $headers = array();
        foreach ($this->headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }
        curl_setopt($this->request, CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * Set the associated CURL options for a request method
     *
     * @param string $method
     * @return void
     * @access protected
     * */
    protected function set_request_method($method) {
        switch (strtoupper($method)) {
            case 'HEAD':
                curl_setopt($this->request, CURLOPT_NOBODY, true);
                break;
            case 'GET':
                curl_setopt($this->request, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($this->request, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($this->request, CURLOPT_CUSTOMREQUEST, $method);
        }
    }

    /**
     * Sets the CURLOPT options for the current request
     *
     * @param string $url
     * @param string $vars
     * @return void
     * @access protected
     * */
    protected function set_request_options($url, $vars) {
        curl_setopt($this->request, CURLOPT_URL, $url);
        if (!empty($vars))
            curl_setopt($this->request, CURLOPT_POSTFIELDS, $vars);

        # Set some default CURL options
        curl_setopt($this->request, CURLOPT_HEADER, true);
        curl_setopt($this->request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->request, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($this->request, CURLOPT_TIMEOUT, $this->receive_time);
        curl_setopt($this->request, CURLOPT_CONNECTTIMEOUT, $this->connect_time);
        //if ($this->cookie_file) {
        //    curl_setopt($this->request, CURLOPT_COOKIEFILE, $this->cookie_file);
        //    curl_setopt($this->request, CURLOPT_COOKIEJAR, $this->cookie_file);
        //}
        if ($this->follow_redirects)
            curl_setopt($this->request, CURLOPT_FOLLOWLOCATION, true);
        if ($this->referer)
            curl_setopt($this->request, CURLOPT_REFERER, $this->referer);

        # Set any custom CURL options
        foreach ($this->options as $option => $value) {
            curl_setopt($this->request, constant('CURLOPT_' . str_replace('CURLOPT_', '', strtoupper($option))), $value);
        }
    }

}
