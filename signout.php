<?php
    require "database.php";
// expected  reg_first, reg_last, reg_email, reg_pass
    if ( !array_key_exists( "session", $_COOKIE ) ) exit("You are not logged in");

    try
    {
        $session = $_COOKIE["session"];
        
        $users = new UserTable();
        $users->signOut($session);
        setcookie("session", "", 0, "","", true, true);
        echo json_encode( array('error' => 0) );
    }
    catch (Exception $e)
    {
        print "Exception!!! <br>";
        print_r($e); print "<br>";
    }
?>
