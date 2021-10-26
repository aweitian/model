<?php


class CollectionTest extends PHPUnit_Framework_TestCase
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
        //$a = $model->where('admin_id', '>', 1)->select();

        $coll = new \Aw\ModelCollection();
        $coll[] = $model;
        $coll[] = $model;
        $coll[] = 3;
        $i = 0;
        $this->assertEquals(count($coll), 2);
        foreach ($coll as $c) {
            $this->assertTrue($c instanceof \Aw\Model);
            $i++;
        }
        $this->assertEquals($i, 2);
    }
}


