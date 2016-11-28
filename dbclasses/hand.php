<?php
class HandDAO {
    private $config;
    function __construct($config) {
        $this->config = $config;
    }

    function getHand($userId, $gameId) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT cards.id, cards.suit, cards.value FROM hands
                                  LEFT JOIN handcards ON handcards.handid = hands.id
                                  LEFT JOIN cards ON cards.id = handcards.cardid
                                  WHERE hands.gameid = ?
                                  AND hands.userid = ?
                                  AND handcards.isplayed = 0");
        $stmt->bind_param("ii", $gameId, $userId);
        $stmt->execute();
        
        $cards = array();
        $stmt->bind_result($id, $suit, $value);
        while ($stmt->fetch()) {
            $row['id'] = $userId;
            $row['suit'] = $suit;
            $row['value'] = $value;
            array_push($cards, $row);
        }

        $stmt->close();
        $mysqli->close();

        return $cards;
    }
}
?>