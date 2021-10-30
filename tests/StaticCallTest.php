<?php

require_once __DIR__ . "/AdminStaticCall.php";

class StaticCallTest extends PHPUnit_Framework_TestCase
{

    public function testFindPks()
    {

        $a = \Aw\AdminStaticCall::find(7);

//        var_dump($a);exit;
        $this->assertEquals($a->admin_id, 7);


        $a = \Aw\AdminStaticCall::field('name')->find(7);

        $this->assertEquals($a->name, 'zskss');
    }

}


