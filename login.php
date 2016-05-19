<?php
    require "database.php";

    if ( !array_key_exists( "json", $_POST ) ) exit("no json in the POST");

    $a = json_decode( $_POST["json"] );

    if ( !is_string($a->log_email) || strlen($a->log_email)<1 ) exit("Введіть адресу електронної пошти!");
    if ( !is_string($a->log_pass)  || strlen($a->log_pass) <1 ) exit("Введіть пароль!");
    
    //exit(0);
    
    try
    {
        $users = new UserTable();
        list($sess_id, $name) = $users->loginAndGetSessionIDandName($a->log_email, $a->log_pass);
        setcookie("session", $sess_id, time()+30, "","", true, true);
        echo json_encode( array('user' => $name, 'error' => 0 ) );
    }
    catch (LoginFailedException $e)
    {
        echo json_encode( array('error' => 2, 'errorText' => 'Помилка входу' ) );
    }
    catch (Exception $e)
    {
        echo json_encode( array('error' => 1, 'errorText' => 'Невідома помилка' ) );
    }
?>
