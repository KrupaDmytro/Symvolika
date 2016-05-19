<?php
    include "private.php";

    error_reporting(E_ALL);
    
    function myErrorHandler($errno, $errstr, $errfile, $errline)
    {
        echo "<b>Error</b> [$errno] $errstr in <b>$errfile</b> line $errline <br />\n";

        /* Execute PHP internal error handler */
        return false;
    }
    
    set_error_handler("myErrorHandler");
    
    class LoginFailedException extends Exception {}
    class SessionExpiredException extends Exception {}

    class UserTable
    {
        var $conn = NULL;
        var $SECRET = "diu7ajksf8sj,vKLDHliewudksfj";
        
        function UserTable()
        {
            global $connstring, $user, $pass;
            
            if (!$connstring)
            {
                $connenv = getenv("SQLAZURECONNSTR_defaultConnection");
                parse_str(str_replace(";", "&", $connenv), $connarray);
            
                $connstring = "sqlsrv:Server=".$connarray["Data_Source"].";Database=".$connarray["Initial_Catalog"];
                $user = $connarray["User_Id"];
                $pass = $connarray["Password"];
            }
            
            try
            {
                $this->conn = new PDO( $connstring, $user, $pass );
                $this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            }
            catch ( PDOException $e )
            {
                print_r($e);
                die("Database connection error");
            }
        }
        
        function createTables()
        {
            $conn = $this->conn;
            if (!$conn) die("Database connection is closed");
        
            try
            {
                $conn->exec( "CREATE TABLE usertable( ".
                             "ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,".
                             "firstname     VARCHAR( 64 ) NOT NULL,".
                             "lastname      VARCHAR( 64 ) NOT NULL,".
                             "email         VARCHAR( 64 ) NOT NULL UNIQUE,".
                             "password      VARCHAR( 128 ) NOT NULL,".
                             "admin         BIT  NOT NULL".
                             ")");
                print("Table 'usertable' was created.<br>");
            }
            catch ( PDOException $e )
            {
                echo "Create table 'usertable' error. May be it already exists.<br>"; print_r($e); echo "<br>";
            }
            
            try
            {
                $conn->exec("CREATE TABLE sessions( ".
                             "sessionid     VARCHAR( 64 ) NOT NULL PRIMARY KEY,".
                             "email         VARCHAR( 64 ) NOT NULL UNIQUE,".
                             "time          DATETIME NOT NULL DEFAULT GETDATE()".
                             ")");
                print("Table 'sessions' was created.<br>");
            }
            catch ( PDOException $e )
            {
                echo "Create table 'sessions' error. May be it already exists.<br>"; print_r($e); echo "<br>";
            }
        }
        
        function passwordHash($email, $passwd)
        {
            $SECRET = $this->SECRET;
            try
            {
                return hash( "whirlpool", $SECRET.$email.$SECRET.$passwd.$SECRET, false );
            }
            catch(Exception $e)
            {
                print_r($e);
                die("Can't encode string");
            }
        }
        
        function newSessionHash($email)
        {
            $SECRET = $this->SECRET;
            try
            {
                return hash( "sha256", $SECRET.$email.$SECRET.time().$SECRET, false );
            }
            catch(Exception $e)
            {
                print_r($e);
                die("Can't encode session id");
            }
        }
        
        function addUser($firstname, $lastname, $email, $passwd)
        {
            $conn = $this->conn;
            if (!$conn) die("Database connection is closed");
            
            $passhash = $this->passwordHash($email, $passwd);
            $isAdmin=0;
            
            try
            {
                $q = $conn->prepare("insert into usertable (firstname, lastname, email, password, admin) ".
                                "values (?, ?, ?, ?, ?)");
                
                $q->execute(array($firstname, $lastname, $email, $passhash, $isAdmin));
                                
                if ($q->errorCode() === "00000" && $q->rowCount()==1) return true;
            }
            catch ( PDOException $e )
            {
                //print_r($e);
                return false;
            }
        }
        
        // returns user's ($firstname, $lastname) or throws LoginFailedException, PDOException
        function checkLoginAndGetName($email, $passwd)
        {
            $conn = $this->conn;
            if (!$conn) die("Database connection is not set");
            
            $sqlselect = "select firstname, lastname from usertable where email=? AND password=?";
            $query = $conn->prepare($sqlselect);
            $passhash = $this->passwordHash($email, $passwd);
            $query->execute(array($email, $passhash));
            
            foreach($query as $row)
            {
                // There is such a user
                return array($row[0], $row[1]);
            }
            // incorrect login/password
            throw new LoginFailedException();
        }
        
        // returns user's ($firstname ." ". $lastname) or throws LoginFailedException, PDOException
        function getUserNameByEmail($email)
        {
            $conn = $this->conn;
            if (!$conn) die("Database connection is not set");
            
            $sqlselect = "select firstname, lastname from usertable where email=?";
            $query = $conn->prepare($sqlselect);
            $query->execute(array($email));
            
            foreach($query as $row)
            {
                // There is such a user
                return $row[0]. " ".$row[1];
            }
            // incorrect email
            throw new LoginFailedException();
        }
        
        // returns ($sessionid, $name) or throws LoginFailedException, PDOException
        function loginAndGetSessionIDandName($email, $passwd)
        {
            $conn = $this->conn;
            if (!$conn) die("Database connection is not set");
                       
            list($firstname, $lastname) = $this->checkLoginAndGetName($email, $passwd);

            $conn->prepare("delete from sessions where email=?")->execute(array($email));
            
            $new_session_id = $this->newSessionHash($email);
            $conn->prepare("insert into sessions(sessionid, email) values(?,?)")->
                        execute(array($new_session_id, $email));

            return array($new_session_id, $firstname." ".$lastname);
        }
        
        function signOut($sess_id)
        {
            $conn = $this->conn;
            if (!$conn) die("Database connection is not set");
            
            try
            {
                $conn->prepare("delete from sessions where sessionid=?")->execute(array($sess_id));
                return true;
            }
            catch(Exception $e)
            {
                return false;
            }
        }
        
        // returns email or throws SessionExpiredException.
        function getEmailBySessionId($session_id, $expire_seconds)
        {
            $conn = $this->conn;
            if (!$conn) die("Database connection is not set");
            
            $query = $conn->prepare("select email from sessions where sessionid=? and DATEDIFF(second, time, GETDATE()) < ?");
            $query->execute(array($session_id, $expire_seconds));
            
            foreach($query as $row)
            {
                // There is such a session. Update expire time
                
                return $row[0];
            }
            // session is expired or was never created
            throw new SessionExpiredException();
        }
        
        function updateSessionExpirationTime($session_id)
        {
            $conn = $this->conn;
            if (!$conn) die("Database connection is not set");
            
            $conn->prepare("UPDATE sessions SET time=GETDATE() where sessionid=?")->execute(array($session_id));
        }
        
        function drop()
        {
            $conn = $this->conn;
            if (!$conn) die("Database connection is closed");
            
            try
            {
                print "Dropping usertable...<br>";
                $conn->exec("DROP TABLE usertable");
                echo "Table usertable was dropped <br>";
            }
            catch ( PDOException $e )
            {
                echo "Table usertable was not dropped <br>";
            }
            
            try
            {
                print "Dropping sessions...<br>";
                $conn->exec("DROP TABLE sessions");
                print "Table sessions was dropped <br>";
            }
            catch ( PDOException $e )
            {
                echo "Table sessions was not dropped <br>";
            }
        }
        
        function dumpUsers()
        {
            $conn = $this->conn;
            if (!$conn) die("Database connection is not set");
            
            print "Dumping usertabe:<br>";
            foreach($conn->query("select * from usertable") as $row)
            {
                print_r($row); print "<br>";
            }
            print "<br>";
        }
        
        function dumpSessions()
        {
            $conn = $this->conn;
            if (!$conn) die("Database connection is not set");
            
            try
            {
                print "Dumping sessions:<br>";
                foreach($conn->query("select * from sessions") as $row)
                {
                    print_r($row); print "<br>";
                }
                print "Dump end";
            }
            catch ( PDOException $e )
            {
                echo "Table sessions query error <br>";
            }
            
            print "<br>";
        }
    }
    
    function usertest()
    {
        try
        {
        $users = new UserTable();
        
        $users->drop();
        
        $users->createTables();
        
        $users->addUser("User1", "Login1", "mail@user.com", "passwd");
        $users->addUser("User2", "Login2", "mail1@user.com", "passwd1");
        $users->addUser("SameUser", "SameUser", "mail@user.com", "passwd1");
        
        $users->dumpUsers();
        $users->dumpSessions();
        
        print "Before list<br>";
        list($sess_id, $name) = $users->loginAndGetSessionIDandName("mail@user.com", "passwd");
        print "User $name is logged in with session $sess_id<br>";
        $users->dumpSessions();
        
        $email = $users->getEmailBySessionId($sess_id,10);
        assert($email == "mail@user.com");
        sleep(2);
        try
        {
            $users->getEmailBySessionId($sess_id, 1);
            assert(false, "The session should be already expired");
        }
        catch(SessionExpiredException $e)
        {
            print "OK. Session is expired <br>";
        }
        
        $users->dumpSessions();
        
        $users->drop();
        
        }
        catch (Exception $e)
        {
            print "Exception!!! <br>";
            print_r($e); print "<br>";
        }
    }
?>
