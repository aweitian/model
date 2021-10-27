<?php

require_once  __DIR__ . "/Admin.php";
require_once __DIR__ . "/MutiPks.php";

class SaveTest extends PHPUnit_Framework_TestCase
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

        $a = $model->find(7);

        $this->assertEquals($a->admin_id, 7);

        $a->name = "taw";
        $this->assertEquals($a->save(),1);

        $b = new \Aw\Admin($connect);
        $bb = $b->find(7);
//        var_dump($bb->name);
        $this->assertEquals($bb->name, 'taw');
        $bb->name = 'zskss';
        $this->assertEquals($bb->save(),1);

        $mut = new \Aw\MutiPks($connect);
        $c = $mut->find([2,'aa']);
        $this->assertEquals($c->data, 'ccc');
        $c->data = 'aa';
        $this->assertEquals($c->save(),1);
        $mut2 = new \Aw\MutiPks($connect);
        $c2 = $mut2->find([2,'aa']);
        $this->assertEquals($c2->data, 'aa');
        $c2->data='ccc';
        $this->assertEquals($c2->save(),1);

        $t = $c2->update_debug([
            'x' => 2,
            'y' => 'aa',
            'data' => 'data'
        ]);
        $this->assertEquals("UPDATE `muti_pk_test` SET `x`=:x,`y`=:y,`data`=:data WHERE `x` = :x AND `y` = :y",$t[0]);
    }

}


