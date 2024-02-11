<?php
namespace FpDbTest;

class SpecialValue
{
    const SKIP = '__SKIP__';

    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}