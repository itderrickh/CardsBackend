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
}
?>