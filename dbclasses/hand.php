<?php
class HandDAO {
    private $config;
    function __construct($config) {
        $this->config = $config;
    }

    function getHand($userId, $gameId) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT handcards.id AS handcardid, cards.id, cards.suit, cards.value FROM hands
                                  LEFT JOIN handcards ON handcards.handid = hands.id
                                  LEFT JOIN cards ON cards.id = handcards.cardid
                                  WHERE hands.gameid = ?
                                  AND hands.userid = ?
                                  AND handcards.isplayed = 0");
        $stmt->bind_param("ii", $gameId, $userId);
        $stmt->execute();
        
        $cards = array();
        $stmt->bind_result($handCardId, $id, $suit, $value);
        while ($stmt->fetch()) {
            $row['handcardid'] = $handCardId;
            $row['id'] = $userId;
            $row['suit'] = $suit;
            $row['value'] = $value;
            array_push($cards, $row);
        }

        $stmt->close();
        $mysqli->close();

        return $cards;
    }

    function playCard($userId, $handCardId, $gameId, $gameDao, $trickNum) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("UPDATE handcards SET isplayed = 1 WHERE id = ?");
        $stmt->bind_param("i", $handCardId);
        $stmt->execute();
        $stmt->close();

        $stmt1 = $mysqli->prepare("INSERT INTO tablecards (userid, cardid, tricknumber, gameid) VALUES (?, ?, ?, ?)");
        $stmt1->bind_param("iiii", $userId, $handCardId, $trickNum, $gameId);
        $stmt1->execute();

        //Set current turn to played
        $stmt2 = $mysqli->prepare("UPDATE gameuser SET played = 1 WHERE userid = ? AND gameid = ?");
        $stmt2->bind_param("ii", $userId, $gameId);
        $stmt2->execute();
        $stmt2->close();

        $stmt3 = $mysqli->prepare("SELECT userid FROM gameuser WHERE played = 0 AND gameid = ?");
        $stmt3->bind_param("i", $gameId);
        $stmt3->execute();
        
        $playersLeft = array();
        $stmt3->bind_result($id);
        while ($stmt3->fetch()) {
            $row['id'] = $id;
            array_push($playersLeft, $row);
        }

        if(count($playersLeft) > 0) {
            $gameDao->setCurrentUser($playersLeft[0]['id'], $gameId);
        } else {
            $gameDao->setGameStatus(5, $gameId);
        }
        
        $stmt3->close();
        $mysqli->close();
    }
}
?>