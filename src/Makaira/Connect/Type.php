<?php

namespace Makaira\Connect;

class Type
{
    /* primary es id field */
    public $es_id;

    /* primary id field */
    public $id;

    /* required fields + mak-fields */
    public $timestamp;
    public $url;
    public $active = true;
    public $shop = [];

    /* additional data array */
    public $additionalData = [];

    public function __construct(array $values = [])
    {
        foreach ($values as $name => $value) {
            $this->{$name} = $value;
        }
    }

    public function __set($name, $value)
    {
        $this->additionalData[$name] = $value;
    }

    public function __get($name)
    {
        return $this->additionalData[$name] ?? null;
    }

    public function __isset($name)
    {
        return isset($this->additionalData[$name]);
    }

    public function __unset($name)
    {
        unset($this->additionalData[$name]);
    }

    public static function __set_state(array $args)
    {
        return new static($args);
    }
}
