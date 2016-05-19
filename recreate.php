<?php
    require "database.php";
    try
    {
        $users = new UserTable();
        $users->drop();
        $users->createTables();
    }
    catch (Exception $e)
    {
        print "Exception!!! <br>";
        print_r($e); print "<br>";
    }
?>
