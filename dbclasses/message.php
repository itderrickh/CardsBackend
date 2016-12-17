<?php
class MessageDAO {
    private $config;
    function __construct($config) {
        $this->config = $config;
    }

    function addMessage($userId, $gameId, $message) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("INSERT INTO messages(userid, gameid, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $userId, $gameId, $message);
        $stmt->execute();
        
        $stmt->close();
        $mysqli->close();
    }

    function getMessages($gameId) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT userid, users.email, message FROM messages
                                    LEFT JOIN users ON users.id = messages.userid
                                    WHERE gameid = ?");
        $stmt->bind_param("i", $gameId);
        $stmt->execute();

        $result = array();
        $stmt->bind_result($userid, $name, $message);
        while($stmt->fetch()) {
            $row['userid'] = $userid;
            $row['username'] = $name;
            $row['message'] = $message;

            array_push($result, $row);
        }

        $stmt->close();
        $mysqli->close();

        return $result;
    }
}
?>