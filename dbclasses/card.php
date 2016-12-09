<?php
class CardDAO {
    private $config;
    function __construct($config) {
        $this->config = $config;
    }

    function getCards() {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT id, suit, value FROM cards");
        $stmt->execute();
        
        $cards = array();
        $stmt->bind_result($id, $suit, $value);
        while ($stmt->fetch()) {
            $row['suit'] = $suit;
            $row['value'] = $value;
            $row['id'] = $id;
            array_push($cards, $row);
        }

        $stmt->close();
        $mysqli->close();

        return $cards;
    }
}
?>