<?php
class BidDAO {
    private $config;
    function __construct($config) {
        $this->config = $config;
    }

    function createBid($userid, $value, $gameid) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("INSERT INTO bids(userid, value, gameid) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userid, $value, $gameid);
        $stmt->execute();
        
        $stmt->close();
        $mysqli->close();
    }

    function getBids($gameid) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT userid, value FROM bids WHERE gameid = ?");
        $stmt->bind_param("i", $gameid);
        $stmt->execute();

        $stmt->bind_result($userid, $value);
        $result = array();
        while($stmt->fetch()) {
            $row['userid'] = $userid;
            $row['value'] = $value;
            array_push($result, $row);
        }
        
        $stmt->close();
        $mysqli->close();

        return $result;
    }
}
?>