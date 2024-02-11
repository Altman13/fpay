<?php

namespace FpDbTest;

use mysqli;

class Database implements DatabaseInterface
{
    private mysqli $mysqli;
    private $ret;
    private $values;
    private TemplateFormatter $templateFormatter;

    public function __construct(mysqli $mysqli, TemplateFormatter $templateFormatter)
    {
        $this->mysqli = $mysqli;
        $this->templateFormatter = $templateFormatter;
    }

    public function buildQuery(string $query, array $args = []): string
    {
        $this->ret = $this->templateFormatter->convertPlaceholders($query, $args);
        return $this->ret;
    }

    public function getValue()
    {
        return $this->values;
    }

    public function skip()
    {
        return new SpecialValue(SpecialValue::SKIP);
    }
}


