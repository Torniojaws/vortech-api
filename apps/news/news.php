<?php

echo "News!<hr/>";
print_r($_GET);
$things = explode('/', $_GET['params']);

foreach ($things as $thing) {
    echo $thing."<br/>";
}
