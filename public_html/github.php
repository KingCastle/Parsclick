<?php

/*
 |----------------------------------------------------------------
 | This file will call `github.php` in the parent directory
 |----------------------------------------------------------------
 |
 | We use two version of `github.php` file
 | because GitHub can only access this file in our host
 |
 | Use one of the code line below only:
 | The First one will use PHP to access the file
 | The Second one will use Shell PHP to access the file
 */

return include __DIR__ . '/../github.php';
// return `php -f ../github.php`;