<?php

$connect = mysql_connect("localhost", "root", "") or die(mysql_error());

mysql_select_db("usersdb2", $connect) or die(mysql_error());

?>