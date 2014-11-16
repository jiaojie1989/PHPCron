<?php

/*
 * The MIT License
 *
 * Copyright 2014 jiaojie.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Description of autoload
 *
 * @author jiaojie
 */
class Lib_Autoload {

    private static $registered;

    private static $instance;

    /**
     * 注册 spl 自动加载函数
     * @static
     */
    static public function register()
    {
        if ( self::$registered )
            return;
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        if (false === spl_autoload_register(array(self::getInstance(), 'autoload')))
        {
            throw new Exception(sprintf('Unable to register %s::autoload as an autoloading method.',
                                        get_class(self::getInstance())));
        }
        self::$registered = true;
    }


    static public function getInstance()
    {
        if (!isset(self::$instance))
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 取消自动加载函数
     */
    static public function unregister()
    {
        spl_autoload_unregister(array(self::getInstance(), 'autoload'));
        self::$registered = false;
    }


    public function autoload($class)
    {
        // class already exists
        if (class_exists($class, false) || interface_exists($class, false))
        {
            return true;
        }
        $class = strtolower($class);
        $file = WEB_ROOT . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.class.php';
        if ( file_exists( $file ) ){
            require($file);
            return true;
        }
        return false;
    }
}
