#! /usr/bin/env php
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
//header('content-type:text/html;charset=utf-8');
echo microtime(1) . "\n";
require('./setting.php');

$db = new Lib_Util_Dbcache(Conf_Db::$dev_newziroom);

$table = 't_cms_user';
$columns = array(
        'user_account',
        'cn',
        );
$where = array(
        'AND' => array(
            'is_del' => 0,
            'type' => 1,
            ),
        );

$data = $db->select($table, $columns, $where);

if(FALSE != $data && is_array($data)) {
    foreach($data as $v) {
        //var_dump($v);
    }
} else {
    echo date('Y-m-d H:i:s') . ' | ERROR QUERY | ' . $db->last_query() . "\n";
    print_r($db->error());
}

echo microtime(1) . "\n";
