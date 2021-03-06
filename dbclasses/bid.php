<?php
class BidDAO {
    private $config;
    function __construct($config) {
        $this->config = $config;
    }

    function createBid($userid, $value, $gameid, $trickNum) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("INSERT INTO bids(userid, value, gameid, tricknumber) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $userid, $value, $gameid, $trickNum);
        $stmt->execute();
        
        $stmt->close();
        $mysqli->close();
    }

    function getBids($gameid, $trickNum) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT userid, value, email FROM bids
                                    LEFT JOIN users ON userid = users.id
                                    WHERE gameid = ? AND tricknumber = ?");
        $stmt->bind_param("ii", $gameid, $trickNum);
        $stmt->execute();

        $stmt->bind_result($userid, $value, $email);
        $result = array();
        while($stmt->fetch()) {
            $row['userid'] = $userid;
            $row['value'] = $value;
            $row['email'] = $email;
            array_push($result, $row);
        }
        
        $stmt->close();
        $mysqli->close();

        return $result;
    }
}
?>