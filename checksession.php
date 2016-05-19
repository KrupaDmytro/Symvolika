<?php
    require "database.php";

    if ( !array_key_exists( "session", $_COOKIE ) )
    {
        exit(json_encode( array('You are not logged in') ));
    }
    
    try
    {
        $users = new UserTable();
        $sess_id = $_COOKIE["session"];
        
        $email = $users->getEmailBySessionId($sess_id, 30);
        $name = $users->getUserNameByEmail($email);
        
        $users->updateSessionExpirationTime($sess_id);
        
        // update cookie expiration time by 30 sec
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
