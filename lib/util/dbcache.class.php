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
 * Description of dbcache
 *
 * @author jiaojie
 */
class Lib_Util_Dbcache extends Lib_Util_Db {

    private $redis;
    private $cache_time;
    private $cache;

    /**
     * construct
     * 
     * @param type $options
     * @param type $redis_optins
     */
    public function __construct($options, $redis_optins = NULL, $cache_time = 600) {
        parent::__construct($options);
        $redis_options = !empty($redis_optins) ? $redis_options : Conf_Redis::$cache_redis;
        $this->redis = new Lib_Util_Redis($redis_options);
        $this->cache_time = is_int($cache_time) ? $cache_time : 600;
    }
    
    /**
     * set redis cache
     * 
     * @param type $key
     * @param type $cache
     * @return type
     */
    private function setCache($key, $cache) {
        return $this->redis->setex($key, $this->cache_time, json_encode($cache));
    }

    /**
     * get redis cache
     * 
     * @param type $key
     * @return array
     */
    private function getCache($key) {
        $cache = $this->redis->get($key);
        if (empty($cache)) {
            return NULL;
        }
        return json_decode($cache, 1);
    }

    /**
     * override method select
     * 
     * @param type $table
     * @param type $join
     * @param type $columns
     * @param type $where
     * @return type
     */
    public function select($table, $join, $columns = null, $where = null) {
        $this->queryString = $this->select_context($table, $join, $columns, $where);
        $key = md5($this->queryString);
        $cache = $this->getCache($key);
        if(empty($cache)) {
            $query = $this->query($this->queryString);
            $cache = $query ? $query->fetchAll(
                    (is_string($columns) && $columns != '*') ? PDO::FETCH_COLUMN : PDO::FETCH_ASSOC
                    ) : false;
            $this->setCache($key, $cache);
        }
        return $cache;
    }

    /**
     * override method query
     * 
     * @param type $query
     * @return type
     */
    public function query($query) {
        return $this->pdo->query($query);
    }
//
//    public function exec($query) {
//        $this->queryString = $query;
//
//        return $this->pdo->exec($query);
//    }

//    private function getExecCache($query) {
//        $this->queryString = $query;
//        $key = md5($query);
//        $cache = $this->getCache($key);
//        if (empty($cache)) {
//            $cache = $this->pdo->exec($query);
//            $this->setCache($key, $cache);
//        }
//        return $cache;
//    }
//
//    private function getQueryCache($query) {
//        $this->queryString = $query;
//        $key = md5($query);
//        $cache = $this->getCache($key);
//        if (empty($cache)) {
//            $cache = $this->pdo->query($query);
//            $this->setCache($key, $cache);
//        }
//        return $cache;
//    }

    

}
