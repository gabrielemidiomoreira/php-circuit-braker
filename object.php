<?php

class SomeObject
{
    public $name;

    public function fill(array $data)
    {
        $class = new ReflectionClass(self::class);

        foreach ($data as $key => $value) {
            $p = $class->getProperty($key);
            $p->setValue($this, $value);
            $p->setAccessible(false);
            var_dump($p->isPrivate());
        }
    }
}

$o = new SomeObject();
$o->fill(['name' => 'Gabriel']);
$o->name = 'Carlos';

echo $o->name."\n";
