<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/22
 * Time: 13:30
 */

namespace Aw;


use Aw\Build\Mysql\Crud;
use Aw\Db\Connection\Mysql;

class Model
{
    protected $table;
    protected $pk = 'id';
    protected $incrementing = true;
    /**
     * @var Mysql
     */
    public $connection;

    /**
     * @var Crud
     */
    public $builder;

    public $binder = [];

    protected $data = [];

    protected $append = [];

    /**
     * last sql
     * @var String
     */
    public $sql;

    public function __construct($db = null)
    {
        if (is_array($db)) {
            $this->connection = new Mysql($db);
        } else if ($db instanceof Mysql) {
            $this->connection = $db;
        } else {
            return;
        }
        if (!is_string($this->table)) {
            $this->table = strtolower(static::class);
            $this->table = explode('\\', $this->table);
            $this->table = end($this->table);
        }
        $this->builder = new Crud($this->table);
    }

    protected function fill(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param $connection
     * @return $this
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
        return $this;
    }


    /**
     * @param $field
     * @param $op
     * @param null $bind
     * @return $this
     */
    public function where($field, $op, $bind = null)
    {
        $where = '';
        if (is_string($field) && preg_match("/^\w+$/", $field)) {
            if ($bind == null) {
                $bind = $op;
                $op = '=';
            }
            $where = "`$field` $op :$field";
            $this->binder[$field] = $bind;
        } else if (is_array($field)) {
            if (count($field) == 2) {
                $where = "`{$field[0]}` = :{$field[0]}";
                $bind = $field[1];
                $this->binder[$field[0]] = $bind;
            } else if (count($field) == 3) {
                $where = "`{$field[0]}` {$field[1]} :{$field[2]}";
                $this->binder[$field[0]] = $field[2];
            }
        } else if (is_callable($field)) {
            $where = "(" . call_user_func($field, $this->builder, $this->binder) . ")";
        }

        if ($where) {
            $this->builder->bindWhere($where);
        }
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function field($field)
    {
        $this->builder->bindField($field);
        return $this;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->data = [];
        $this->builder->reset();
        return $this;
    }

    /**
     * @return ModelCollection
     */
    public function select()
    {
        $sql = $this->builder->select();
        $this->sql = $sql;
        //var_dump($sql, $this->binder);
        $data = $this->connection->fetchAll($sql, $this->binder);
        $collection = new ModelCollection();
        foreach ($data as $row) {
            $m = new static($this->connection);
            $m->fill($row);
            $collection->add($m);
        }
        return $collection;
    }

    public function update(array $data) {

    }

    /**
     * @param $pk
     * @return Model|null
     */
    public function find($pk)
    {
        if (is_string($this->pk) && (is_string($pk) || is_numeric($pk))) {
            $this->where($this->pk, $pk);
        } else if (is_array($pk) && is_array($this->pk) && count($pk) > 0 && count($pk) == count($this->pk)) {
            for ($i = 0; $i < count($pk); $i++) {
                $this->where($this->pk[$i], $pk[$i]);
            }
        } else {
            return null;
        }
        $sql = $this->builder
            ->select();
        $binder = $this->binder;
//        var_dump($sql,$binder);
        $this->sql = $sql;
        $data = $this->connection->fetch($sql, $binder);
        if ($data) {
            $m = new static($this->connection);
            $m->fill($data);
            return $m;
        }
        return null;
    }



    public function save()
    {
        foreach ($this->data as $field => $value) {
            $this->builder->bindField($field,$value);
        }
        if (is_string($this->pk)) {
            if (array_key_exists($this->pk,$this->data)) {
                //UPDATE
                $this->builder->bindWhere($this->pk,$this->data[$this->pk]);
                foreach ($this->data as $field => $bind) {
                    $this->builder->bindField($field);
                    $this->builder->bindValue($field,$bind);
                }
                $sql = $this->builder->update();
                $this->sql = $sql;
                $ar = $this->connection->exec($sql,$this->binder);
                if ($ar > 0) {
                    return $this;
                } else{
                    throw new \Exception('SAVE FAILED');
                }
            } else {
                //insert
                if ($this->incrementing) {
                    $this->builder->bindWhere($this->pk,$this->data[$this->pk]);
                    $sql = $this->builder->insert();
                    $this->sql = $sql;
                    $id = $this->connection->insert($sql,$this->binder);
                    if ($id > 0) {
                        $m = new static($this->connection);
                        $data = $this->data;
                        $data[$this->pk] = $id;
                        $m->fill($data);
                        return $m;
                    } else{
                        throw new \Exception('SAVE FAILED');
                    }
                } else {
                    throw new \Exception("模型为主键为非自增,保存时主键值必须存在");
                }
            }
        } else if (is_array($this->pk)) {
            for ($i=0;$i<$this->pk;$i++) {
//                $pk =
            }
            $this->builder->bindWhere($this->pk,$this->data[$this->pk]);
            $sql = $this->builder->update();
            $this->sql = $sql;
            $ar = $this->connection->exec($sql,$this->binder);
            if ($ar > 0) {
                return $this;
            } else{
                throw new \Exception('SAVE FAILED');
            }
        } else {
            throw new \Exception("模型为主键类型不能识别");
        }
    }

    public function __get($name)
    {
        if (in_array($name, $this->append)) {
            if (method_exists($this, "get" . ucfirst($name) . "Attr")) {
                return $this->{"get" . ucfirst($name) . "Attr"}($this->data);
            } else {
                throw new \Exception("get" . ucfirst($name) . "Attr method is not exist");
            }

        } else if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            throw new \Exception("field $name not exist");
        }
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->append)) {
            if (method_exists($this, "set" . ucfirst($name) . "Attr")) {
                $this->data[$name] = $this->{"set" . ucfirst($name) . "Attr"}($this->data, $value);
                return $this;
            } else {
                throw new \Exception("set" . ucfirst($name) . "Attr method is not exist");
            }
        } else if (array_key_exists($name, $this->data)) {
            $this->data[$name] = $value;
            return $this;
        } else {
            throw new \Exception("field $name not exist");
        }
    }
}