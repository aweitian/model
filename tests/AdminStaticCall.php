<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/22
 * Time: 13:30
 */

namespace Aw;

class AdminStaticCall extends Model
{
    protected $table = 'admin';
    protected $pk = 'admin_id';

    public static function __callStatic($method, $arguments)
    {
        $m = new static(new \Aw\Db\Connection\Mysql (array(
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => 'root',
            'database' => 'test',
            'charset' => 'utf8',
        )));
        return $m->__call($method, $arguments);
    }
}