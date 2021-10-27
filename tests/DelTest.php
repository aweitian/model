<?php

require_once __DIR__ . "/Admin.php";

class DelTest extends PHPUnit_Framework_TestCase
{
    public function testSave()
    {
        $connect = new Aw\Db\Connection\Mysql (array(
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => 'root',
            'database' => 'test',
            'charset' => 'utf8',
        ));
        $model = new \Aw\Admin($connect);

        $model->name = 'newbie'.rand(1,5555);
        $model->pass = 'df3d9bbdad22d9d9ba41d7e60ffff32e';
        $model->real_name = '张三顺';
        $model->pid = 1;
        $model->role = 'operator';
        $model->status = 'normal';
        $this->assertTrue($model->save() > 1);

        $model->where('admin_id','>',8)->drop();
    }

    public function testDelete()
    {
        $connect = new Aw\Db\Connection\Mysql (array(
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => 'root',
            'database' => 'test',
            'charset' => 'utf8',
        ));
        $model = new \Aw\Admin($connect);
        $t = $model->where('name','cc')->delete_debug();
        $this->assertEquals("DELETE FROM `admin` WHERE `name` = :name",$t[0]);
    }
}


