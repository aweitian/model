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
use JsonSerializable;


/**
 * @method static where($field, $op, $value = null):Model
 * @method static find($pk):Model
 * @method static field($field):Model
 * @method static select():Model
 * @method static update(array $data):Model
 * @method static drop():Model
 * Class Model
 * @package Aw
 */
class Model implements JsonSerializable
{
    protected static $fields = array();
    protected $table;
    protected $pk = 'id';
    protected $incrementing = true;
    /**
     * 分页大小获取顺序
     * 1. size函数的参数
     * 2. 从GET参数中用size_key来获取size
     * 3. 使用这默认size
     * @var int 分页大小
     */
    public $size = 20;

    /**
     * 规则和size一样
     * @var int
     */
    public $current_page = 1;
    /**
     * 从GET中用size来获取
     * @var string
     */
    public $size_key = 'size';

    public $page_key = 'page';

    /**
     * 如果是数组，从这里获取，否则从GET中获取
     * @var null
     */
    public $page_size_src = null;
    /**
     * @var Mysql
     */
    public $connection;

    /**
     * @var Crud
     */
    public $builder;

    protected $data = [];

    protected $append = [];

    protected $table_fields = [];
    /**
     * last sql
     * @var String
     */
    public $sql;

    /**
     * select(true) 会赋值
     * @var int
     */
    protected $select_count_return = 0;

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
        if (!array_key_exists($this->table, self::$fields)) {
            $result = $this->connection->fetchAll("SHOW FULL COLUMNS FROM `$this->table`");
            self::$fields[$this->table] = $result;
        } else {
            $result = self::$fields[$this->table];
        }

        foreach ($result as $item) {
            $this->table_fields[$item['Field']] = $item;
        }
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this, "public_$name")) {
            return call_user_func_array([$this, "public_$name"], $arguments);
        } else if (method_exists($this->builder, $name)) {
            call_user_func_array([$this->builder, $name], $arguments);
            return $this;
        } else if (method_exists($this->builder, "bind" . ucfirst($name))) {
            call_user_func_array([$this->builder, "bind" . ucfirst($name)], $arguments);
            return $this;
        }
        throw new \Exception("$name 不存在");
    }

//    public static function __callStatic($method, $arguments)
//    {
//        $m = new static(new \Aw\Db\Connection\Mysql (array(
//            'host' => '127.0.0.1',
//            'port' => '3306',
//            'user' => 'root',
//            'password' => 'root',
//            'database' => 'test',
//            'charset' => 'utf8',
//        )));
//        return $m->__call($method, $arguments);
//    }

    public function paginate($page = null, $size = null)
    {
        $src = is_array($this->page_size_src) ? $this->page_size_src : $_GET;
        if (is_null($page)) {
            $page = isset($src[$this->page_key]) && $src[$this->page_key] ? $src[$this->page_key] : $this->current_page;
        }
        if (is_null($size)) {
            $size = isset($src[$this->size_key]) && $src[$this->size_key] ? $src[$this->size_key] : $this->size;
        }
        if ($size < 1) {
            $size = 1;
        }
        if ($page < 1) $page = 1;

        $this->builder->useCalcFoundRows();
        $this->builder->bindLimit(($page - 1) * $size . ',' . $size);
        $data = $this->public_select(true);
//        var_dump($data);
        $total = $this->select_count_return;
//        var_dump($this->connection->getQueryLog());
        if ($total < 0) $total = 0;
        $page_count = ceil($total / $size);
        if ($page > $page_count) $page = $page_count;
        return compact('page_count', 'total', 'page', 'size', 'data');
    }

    public function fill(array $data)
    {
//        var_dump($this->table_fields, $data);
        foreach ($this->table_fields as $key => $datum) {
            if (array_key_exists($key, $data)) {
                $this->data[$key] = $data[$key];
            }
        }
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
     * @param null $value
     * @return $this
     */
    protected function public_where($field, $op, $value = null)
    {
        $where = '';
        $bind = [];
        if (is_string($field) && preg_match("/^\w+$/", $field)) {
            if ($value == null) {
                $value = $op;
                $op = '=';
            }
            $where = "`$field` $op :$field";
            $bind[$field] = $value;
//            $this->builder->bindValue($field, $bind);
        } else if (is_array($field)) {
            if (count($field) == 2) {
                $where = "`{$field[0]}` = :{$field[0]}";
                $bind[$field[0]] = $field[1];
//                $this->builder->bindValue($field[0], $bind);
            } else if (count($field) == 3) {
                $where = "`{$field[0]}` {$field[1]} :{$field[2]}";
                $bind[$field[0]] = $field[2];
            }
        } else if (is_callable($field)) {
            $where = "(" . call_user_func($field, $this->builder) . ")";
        }

        if ($where) {
            $this->builder->bindWhere($where);
            $this->builder->bindValue($bind);
        }
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    protected function public_field($field)
    {
        $this->builder->bindField($field);
        return $this;
    }

    /**
     * @param $key
     * @param null $value
     * @return $this
     */
    public function bindValue($key, $value = null)
    {
        $this->builder->bindValue($key, $value);
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
     * @param bool $select_count
     * @return ModelCollection
     */
    protected function public_select($select_count = false)
    {
        $sql = $this->builder->select();
        $this->sql = $sql;
        $bind = $this->builder->getBindValue();
//        var_dump($sql, $bind);
        $data = $this->connection->fetchAll($sql, $bind);
        if ($select_count) {
            $this->select_count_return = $this->connection->scalar($this->builder->count());
        }
        $collection = new ModelCollection();
        foreach ($data as $row) {
            $m = new static($this->connection);
            $m->fill($row);
            $collection->add($m);
        }
        return $collection;
    }

    /**
     * @param array $data
     * @return int
     */
    protected function public_update(array $data)
    {
        foreach ($data as $field => $value) {
            $this->builder->bindField($field);
            $this->builder->bindValue($field, $value);
        }
        $sql = $this->builder->update();
        $bind = $this->builder->getBindValue();
//        var_dump($sql,$bind);
//        exit;
        return $this->connection->exec($sql, $bind);
    }

    /**
     * @return int
     */
    protected function public_delete()
    {
        $sql = $this->builder->delete();
        $bind = $this->builder->getBindValue();
        return $this->connection->exec($sql, $bind);
    }

    /**
     * @return array
     */
    public function delete_debug()
    {
        $sql = $this->builder->delete();
        $bind = $this->builder->getBindValue();
        return [$sql, $bind];
    }

    /**
     * @return int
     */
    protected function public_drop()
    {
        if (is_string($this->pk)) {
            $pk = $this->__get($this->pk);
            $this->where($this->pk, $pk);
        } else if (is_array($this->pk)) {
            foreach ($this->pk as $pkk) {
                $this->where($pkk, $this->__get($pkk));
            }
        }
        $sql = $this->builder->delete();
        $bind = $this->builder->getBindValue();
//        var_dump($sql, $bind);
//        exit;
        return $this->connection->exec($sql, $bind);
    }


    /**
     * @param array $data
     * @return array
     */
    public function update_debug(array $data)
    {
        foreach ($data as $field => $value) {
            $this->builder->bindField($field);
            $this->builder->bindValue($field, $value);
        }
        $sql = $this->builder->update();
        $bind = $this->builder->getBindValue();
        return [$sql, $bind];
    }

    /**
     * @param $pk
     * @return Model|null
     */
    protected function public_find($pk = null)
    {
        if (is_string($this->pk) && (is_string($pk) || is_numeric($pk))) {
            $this->where($this->pk, $pk);
        } else if (is_array($pk) && is_array($this->pk) && count($pk) > 0 && count($pk) == count($this->pk)) {
            for ($i = 0; $i < count($pk); $i++) {
                $this->where($this->pk[$i], $pk[$i]);
            }
        }
        $sql = $this->builder
            ->select();
        $binder = $this->builder->getBindValue();
        $this->sql = $sql;
        $data = $this->connection->fetch($sql, $binder);
//        var_dump($data);
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
            $this->builder->bindField($field, $value);
        }
        if (is_string($this->pk)) {
            if (array_key_exists($this->pk, $this->data)) {
                //UPDATE
                $this->builder->bindWhere($this->pk, $this->data[$this->pk]);
                foreach ($this->data as $field => $bind) {
                    $this->builder->bindField($field);
                    $this->builder->bindValue($field, $bind);
                }
                $sql = $this->builder->update();
                $binder = $this->builder->getBindValue();
                $this->sql = $sql;
//                var_dump($sql,$this->binder);
                $ar = $this->connection->exec($sql, $binder);
                if ($ar > 0) {
                    return $ar;
                } else {
                    throw new \Exception('SAVE FAILED');
                }
            } else {
//                var_dump('insert');exit;
                //insert
                if ($this->incrementing) {
//                    $this->builder->bindWhere($this->pk, $this->data[$this->pk]);
                    $sql = $this->builder->insert();
                    $bind = $this->data;
                    $this->sql = $sql;
                    $id = $this->connection->insert($sql, $bind);
                    if ($id > 0) {
                        $this->data[$this->pk] = $id;
                        return $id;
                    } else {
                        throw new \Exception('SAVE FAILED');
                    }
                } else {
                    throw new \Exception("模型为主键为非自增,保存时主键值必须存在");
                }
            }
        } else if (is_array($this->pk)) {
            for ($i = 0; $i < count($this->pk); $i++) {
                $pk = $this->pk[$i];
                if (!array_key_exists($pk, $this->data)) {
                    throw new \Exception("模型为主键为数组,保存时所有主键值必须存在");
                }
                $this->builder->bindWhere($pk, $this->data[$pk]);
            }
            foreach ($this->data as $field => $bind) {
                $this->builder->bindField($field);
                $this->builder->bindValue($field, $bind);
            }
            $sql = $this->builder->update();
            $binder = $this->builder->getBindValue();
            $this->sql = $sql;
            $sql = $this->builder->update();
            $ar = $this->connection->exec($sql, $binder);
            if ($ar > 0) {
                return $ar;
            } else {
                throw new \Exception('SAVE FAILED');
            }
        } else {
            throw new \Exception("模型为主键类型不能识别");
        }
    }

    public function __get($name)
    {
//        var_dump($this->data);
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
//        var_dump('__set',$name,$value);
        if (in_array($name, $this->append)) {
            if (method_exists($this, "set" . ucfirst($name) . "Attr")) {
                $this->data[$name] = $this->{"set" . ucfirst($name) . "Attr"}($this->data, $value);
                return $this;
            } else {
                throw new \Exception("set" . ucfirst($name) . "Attr method is not exist");
            }
        } else if (array_key_exists($name, $this->data) || array_key_exists($name, $this->table_fields)) {
            $this->data[$name] = $value;
            return $this;
        } else {
            throw new \Exception("field $name not exist");
        }
    }

    public function toArray()
    {
        $ret = array();
        foreach (array_merge(array_keys($this->data), $this->append) as $field) {
            $ret[$field] = $this->__get($field);
        }
        return $ret;
    }

    public function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}