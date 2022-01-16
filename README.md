# model
model，第一版只包含:ORM

## 静态调用
```php
namespace App\Model
class Model {
    public static function __callStatic($method, $arguments)
    {
        $m = new static(Application::getInstance()->make('mysql'));
        return $m->__call($method, $arguments);
    }
}
```
其它Model继承这个类就可以


## select one
```php
$model = new Admin();
$row = $model->find(1);
echo $row->admin_id;

```
## select
```php
$model = new Admin();
$rows = $model->where('admin_id','>',1)->select();
foreach($rows as $row) {
    echo $row->admin_id;
}
```

## insert
```php
$model = new Admin();
$model->name = "gondar";
$model->sex = "male";
echo $model->save(); //insert into
```

## update one
```php
$model = new Admin();
$model2 = $model->find(1);
$model2->name = "gondar";
$model2->sex = "male";
echo $model2->save(); //affected rows
```

## update
```php
$model = new Admin();
echo $model->where('admin_id','>',1)->update([
    'name' => 'aa',
    'sex' => 'male'
]);
//affected rows
```

## drop
```php
$model = new Admin();
$model->find(1)->drop();
//affected rows
```


## delete
```php
$model = new Admin();
$model->where('name','cc')->delete();
//affected rows
```
