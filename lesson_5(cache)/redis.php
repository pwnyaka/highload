<?php

class redisCacheProvider
{

    private $connection = null;

    private function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = new Redis();
            $this->connection->connect('127.0.0.1', '6379');
        }
        return $this->connection;
    }

    public function get($key)
    {
        $result = false;
        if ($c = $this->getConnection()) {
            $result = unserialize($c->get($key));
        }
        return $result;
    }
    // при нулевом времени в методе обертки нулевое значение передается в метод set redis'a, что вызывает ошибку
    // если не указываем время жизни, то и передавать нулевой параметр не должны
    public function set($key, $value, $time = 0)
    {
        if ($c = $this->getConnection()) {
            $time ? $c->set($key, serialize($value), $time) : $c->set($key, serialize($value));
        }
    }

    public function del($key)
    {
        if ($c = $this->getConnection()) {
            $c->delete($key);
        }
    }

    public function clear()
    {
        if ($c = $this->getConnection()) {
            $c->flushDB();
        }
    }
}


$r = new redisCacheProvider();

$r->set('str', 'string', 8);
$r->set('int', 123);

var_dump($r->get('str'));
var_dump($r->get('int'));

$start = microtime(true);

try {
    $db = new PDO('mysql:host=localhost;dbname=skytech', 'test', 'test');
    foreach ($db->query('select customerNumber, customerName, contactLastName from customers where customerNumber = 112') as $row) {
        var_dump($row);
        $r->set('result', $row);
    }
    $dbh = null;
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

$time = microtime(true) - $start;
var_dump('Time with connection to DB: ' . $time);

$start = microtime(true);
var_dump($r->get('result'));
$time = microtime(true) - $start;
var_dump('Time with redis: ' . $time);