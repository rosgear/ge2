# Пакет компонента Ge\IpManager входит в состав Ge Framework.

Пакет компонента управляет списками IP-адресов, имеющие ограничения.

## Ресурсы
- [Ge Framework](https://rosgear.ru/framework/)


## Список проверяемых IP-адресов

Обращение к списку:

```php
Rg::$app->ip->list('blocked');
```

Исли список уже создан (см. выше), обращение ксписку:

```php
// вариант 1
$blocked = Rg::$app->ip->list('blocked');
// вариант 2
$blocked = Rg::$app->ip->getList();
```

### С прямым указанием IP-адреса
Получение информации об IP-адресе:
```php
// вариант 1
$ip = $blocked->ip('127.0.0.1')->get();
echo $ip->note; // $ip->{$property}

// вариант 2
$blocked->ip('127.0.0.1');
$blocked->get();
echo $blocked->note; // $ip->{$property}

// вариант 3
$blocked->ip('127.0.0.1');
$ip = $blocked->get();
echo $ip->note; // $ip->{$property}
```

Получение всей информации об IP-адресе:
```php
// вариант 1
$ip = $blocked->ip('127.0.0.1')->get();
print_r($ip->getIpInfo());

// вариант 2
$blocked->ip('127.0.0.1');
$blocked->get();
print_r($blocked->getIpInfo());

// вариант 3
$ip = $blocked->ip('127.0.0.1');
$ip->get();
print_r($ip->getIpInfo());

// вариант 4
$blocked->ip('127.0.0.1');
$ip = $blocked->get();
print_r($ip->getIpInfo());
```
Добавление информации об IP-адресе:
```php
// вариант 1
$blocked->ip('127.0.0.1')->add([
    'note' => 'some note',
    // ...
]);

// вариант 2
$blocked->ip('127.0.0.1');
$blocked->add([
    'note' => 'some note',
    // ...
]);

// вариант 3
$ip = $blocked->ip('127.0.0.1');
$ip->add([
    'note' => 'some note',
    // ...
]);
```

### Без указания прямого IP-адреса

Получение всей информации об IP-адресе:
```php
$ip = $blocked->get('127.0.0.1');
// информации об IP-адресе
$info = $ip->getIpInfo();
```
