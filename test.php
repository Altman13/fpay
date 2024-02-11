<?php

use FpDbTest\Database;
use FpDbTest\DatabaseTest;
use FpDbTest\TemplateFormatter;

spl_autoload_register(function ($class) {
    $a = array_slice(explode('\\', $class), 1);
    if (!$a) {
        throw new Exception();
    }
    $filename = implode('/', [__DIR__, ...$a]) . '.php';
    require_once $filename;
});

$mysqli = @new mysqli('127.0.0.1', 'root', '', 'test', 3306);
if ($mysqli->connect_errno) {
    throw new Exception($mysqli->connect_error);
}

$templateFormatter = new TemplateFormatter();
$db = new Database($mysqli, $templateFormatter);
$test = new DatabaseTest($db);
$test->testBuildQuery();

exit('OK');
