<?php
    require "database.php";
// expected  reg_first, reg_last, reg_email, reg_pass
    if ( !array_key_exists( "json", $_POST ) ) exit("no json in the POST");

    $a = json_decode( $_POST["json"] );

    if ( !is_string($a->reg_first) || strlen($a->reg_first)<1 ) exit("Введіть ім'я!");
    if ( !is_string($a->reg_last)  || strlen($a->reg_last) <1 ) exit("Введіть прізвище!");
    if ( !is_string($a->reg_email) || strlen($a->reg_email)<1 ) exit("Введіть адресу електронної пошти!");
    if ( !is_string($a->reg_pass)  || strlen($a->reg_pass) <1 ) exit("Введіть пароль!");
    
    //exit(0);
    
    try
    {
        $users = new UserTable();
        
        $users->addUser($a->reg_first, $a->reg_last, $a->reg_email, $a->reg_pass);
        list($sess_id, $name) = $users->loginAndGetSessionIDandName($a->reg_email, $a->reg_pass);
        
        setcookie("session", $sess_id, time()+30, "","", true, true);
        
        echo json_encode( array('user' => $name, 'error' => 0 ) );
    }
    catch (Exception $e)
    {
        print "Exception!!! <br>";
        print_r($e); print "<br>";
    }
?>
