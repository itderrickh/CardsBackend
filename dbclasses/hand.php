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

    function playCard($userId, $handCardId, $gameId, $gameDao) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("UPDATE handcards SET isplayed = 1 WHERE handid = ?");
        $stmt->bind_param("i", $handCardId);
        $stmt->execute();

        $stmtPre1 = $mysqli->prepare("SELECT tricknumber FROM games WHERE id = ?");
        $stmtPre1->bind_param("i", $gameId);
        $stmtPre1->execute();

        $stmtPre1->bind_result($trickNum);
        $stmtPre1->fetch();

        $stmt1 = $mysqli->prepare("INSERT INTO tablecards (userid, cardid, tricknumber, gameid) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5])");
        $stmt1->bind_param("iiii", $userId, $handCardId, $trickNum, $gameId);
        $stmt1->execute();

        //Set current turn to played
        $stmt2 = $mysqli->prepare("UPDATE gameuser SET played = 1 WHERE userid = ? AND gameid = ?");
        $stmt2->bind_param("ii", $userId, $gameId);
        $stmt2->execute();

        $stmt3 = $mysqli->prepare("SELECT id FROM gameuser WHERE played = 0 AND gameid = ?");
        $stmt3->bind_param("i", $gameId);
        $stmt3->execute();
        
        $playersLeft = array();
        $stmt3->bind_result($id);
        while ($stmt3->fetch()) {
            $row['id'] = $id;
            array_push($playersLeft, $row);
        }

        if(count($playersLeft) > 0) {
            $gameDao->setCurrentPlayer($playersLeft[0]);
        } else {
            $gameDao->setGameStatus(5, $gameId);
        }

        $stmt->close();
        $stmt2->close();
        $stmt3->close();
        $mysqli->close();
    }
}
?>