<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/22
 * Time: 13:30
 */
namespace Aw;

class MutiPks extends Model
{
    protected $table = 'muti_pk_test';
    protected $pk = [
        'x','y'
    ];

    protected $append =['y','xy'];

    public function getYAttr($row){
        return $row['y'] . '--';
    }

    public function getXyAttr($row){
        return $row['x'] . '--' . $row['y'];
    }
}