<?php
class UserDAO {
    private $config;
    function __construct($config) {
        $this->config = $config;
    }
    
    function createUser($email, $password) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("INSERT INTO users(email, password) VALUES (?, ?)");
        $hash = password_hash($password);
        $stmt->bind_param("ss", $email, $hash);
        $stmt->execute();
        
        $stmt->close();
        $mysqli->close();
    }
    
    function authenticate($email, $password) {
        $user = $this->getUser($email);
        return password_verify($password, $user["password"]);
    }

    function getUser($email) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT id, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $stmt->bind_result($userId, $userEmail, $userHash);
        $stmt->fetch();
        
        $stmt->close();
        $mysqli->close();
        
        $user['id'] = $userId;
        $user['email'] = $userEmail;
        $user['password'] = $userHash;
        
        return $user;
    }

    function getGameUsers($gameId) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT gameuser.id, userid, users.email FROM gameuser 
                                    LEFT JOIN users ON users.id = gameuser.userid
                                    WHERE gameid = ?");
        $stmt->bind_param("i", $gameId);
        $stmt->execute();
        
        $users = array();
        $stmt->bind_result($id, $userId, $email);
        while ($stmt->fetch()) {
            $row['userid'] = $userId;
            $row['gameid'] = $gameId;
            $row['id'] = $id;
            $row['email'] = $email;
            array_push($users, $row);
        }

        $stmt->close();
        $mysqli->close();

        return $users;
    }
}
?>