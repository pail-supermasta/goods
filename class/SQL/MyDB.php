<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.08.2019
 * Time: 17:46
 */

namespace SQL;


class MyDB extends \SQLite3
{
    function __construct()
    {
        $this->open('test.db');
    }
}

$db = new MyDB();
if (!$db) {
    echo $db->lastErrorMsg();
} else {
    echo "Opened database successfully\n";
}
