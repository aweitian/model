<?php

require_once __DIR__ . "/Admin.php";
require_once __DIR__ . "/MutiPks.php";

class SelectTest extends PHPUnit_Framework_TestCase
{
    public function testFind()
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

        $a = $model->find(7);

//        var_dump($a);exit;
        $this->assertEquals($a->admin_id, 7);

        $model = new \Aw\Admin($connect);

        $a = $model->where('admin_id',7)->find();

//        var_dump($a);exit;
        $this->assertEquals($a->admin_id, 7);


        $a = $model->field('name')->find(7);

        $this->assertEquals($a->name, 'zskss');
    }

    public function testFindPks()
    {
        $connect = new Aw\Db\Connection\Mysql (array(
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => 'root',
            'database' => 'test',
            'charset' => 'utf8',
        ));
        $model = new \Aw\MutiPks($connect);

        $a = $model->find([
            2, 'aa'
        ]);
//        var_dump($a);
        $this->assertEquals($a->x, 2);
        $this->assertEquals($a->y, 'aa--');
        $this->assertEquals($a->xy, '2--aa');
//var_dump($a->x,$a->y);
        // $this->assertEquals($a->admin_id,7);
    }

    public function testField()
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
        $a = $model->where('admin_id', '>', 1)->select();
//        var_dump($a);exit;
        foreach ($a as $item) {
            if ($item->admin_id == 2) {
                $this->assertEquals('dkd', $item->name);
            }
        }
        $this->assertEquals(3, count($a));
    }
}


