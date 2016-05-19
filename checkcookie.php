<?php
    setcookie( "TestCookie", 1, time()+10, "","", true, true );
    print_r($_COOKIE);
?>
