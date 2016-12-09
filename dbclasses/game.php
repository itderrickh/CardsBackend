<?php
class GameDAO {
    private $config;
    function __construct($config) {
        $this->config = $config;
    }

    function getGame() {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT id, iscomplete, status, currentplayer, trump, tricknumber FROM games WHERE iscomplete = 0 LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($id, $iscomplete, $status, $currentplayer, $trump, $tricknumber);
        $stmt->fetch();

        $result['id'] = $id;
        $result['iscomplete'] = $iscomplete;
        $result['status'] = $status;
        $result['currentplayer'] = $currentplayer;
        $result['trump'] = $trump;
        $result['tricknumber'] = $tricknumber;

        $stmt->close();
        $mysqli->close();

        return $result;
    }

    function setGameStatus($num, $gameid) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("UPDATE games SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $num, $gameid);
        $stmt->execute();
        
        $stmt->close();
        $mysqli->close();
    }

    function getOrCreateGame() {
        $game = $this->getGame();
        if(is_null($game)) {
            $this->createGame();
            $game = $this->getGame();
        }

        return $game;
    }

    function setCurrentUser($num, $gameid) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("UPDATE games SET currentplayer = ? WHERE gameid = ?");
        $stmt->bind_param("ii", $num, $gameid);
        $stmt->execute();

        $stmt->bind_result($count);
        $stmt->fetch();
        
        $stmt->close();
        $mysqli->close();

        return $count > 0;
    }

    function createGame() {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("INSERT INTO games(iscomplete, status) VALUES (0, 1)");
        $stmt->execute();
        
        $stmt->close();
        $mysqli->close();
    }

    function isGameSetup($gameid) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT COUNT(*) AS Count FROM gameuser WHERE gameid = ?");
        $stmt->bind_param("i", $gameid);
        $stmt->execute();
        
        $stmt->bind_result($count);
        $stmt->fetch();

        $stmt->close();
        $mysqli->close();
        
        return $count == 5;
    }

    function addUserToGame($userid, $gameid) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);

        $prestmt = $mysqli->prepare("SELECT COUNT(*) AS Count FROM gameuser WHERE userid = ? AND gameid = ?");
        $prestmt->bind_param("ii", $userid, $gameid);
        $prestmt->execute();
        $prestmt->bind_result($count);
        $prestmt->fetch();

        $prestmt->close();

        if($count == 0) {
            $stmt = $mysqli->prepare("INSERT INTO gameuser(userid, gameid, played) VALUES (?, ?, 0)");
            $stmt->bind_param("ii", $userid, $gameid);
            $stmt->execute();
            
            $stmt2 = $mysqli->prepare("UPDATE games SET currentplayer = ? WHERE id = ?");
            $stmt2->bind_param("ii", $userid, $gameid);
            $stmt2->execute();
            
            $stmt->close();
            $stmt2->close();
            $mysqli->close();
        }
    }

    function startGame($gameid, $deck, $users) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);

        shuffle($deck);
        $i = 0;
        $c = 0;

        //Distribute the cards
        while($i < 5) {
            $user = $users[$i];

            $stmt1 = $mysqli->prepare("INSERT INTO hands(userid, gameid) VALUES (?, ?)");
            $stmt1->bind_param("ii", $user['userid'], $gameid);
            $stmt1->execute();
            $stmt1->close();

            $handId = $mysqli->insert_id;
            $c = 0;

            while($c < 10) {
                $card = $deck[($i * 10) + $c];
                
                $stmt2 = $mysqli->prepare("INSERT INTO handcards(cardid, handid, isplayed) VALUES (?, ?, 0)");
                $stmt2->bind_param("ii", $card['id'], $handId);
                $stmt2->execute();
                $stmt2->close();

                $c += 1;
            }

            $c = 0;
            $i += 1;
        }

        $stmt3 = $mysqli->prepare("UPDATE games SET trump = ? WHERE id = ?");
        $stmt3->bind_param("ii", $deck[51]['id'], $gameid);
        $stmt3->execute();
        $stmt3->close();

        $mysqli->close();
    }

    function isGameReady($gameid) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT COUNT(*) AS Count FROM gameuser WHERE gameid = ?");
        $stmt->bind_param("i", $gameid);
        $stmt->execute();
        
        $stmt->bind_result($count);
        $stmt->fetch();

        $stmt->close();
        $mysqli->close();

        return $count >= 5;
    }

    function completeGame($gameid) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("UPDATE games SET iscomplete = 1 WHERE gameid = ?");
        $stmt->bind_param("i", $gameid);
        $stmt->execute();
        
        $stmt->close();
        $mysqli->close();
    }

    function resetUsers($gameid) {
        //Set current turn to played
        $stmt2 = $mysqli->prepare("UPDATE gameuser SET played = 0 WHERE gameid = ?");
        $stmt2->bind_param("i", $gameid);
        $stmt2->execute();

        $stmt2->close();
        $mysqli->close();
    }

    function getTrumpCard($trumpId) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT id, suit, value FROM cards WHERE id = ?");
        $stmt->bind_param("i", $trumpId);
        $stmt->execute();
        
        $result = array();

        $stmt->bind_result($id, $suit, $value);
        $stmt->fetch();

        $result['id'] = $id;
        $result['suit'] = $suit;
        $result['value'] = $value;

        $stmt->close();
        $mysqli->close();

        return $result;
    }

    function getField($gameId, $tricknumber) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT cards.id, tablecards.userid, suit, value FROM cards
                                    INNER JOIN tablecards ON tablecards.cardid = cards.id
                                    WHERE gameid = ?
                                    AND tricknumber = ?");
        $stmt->bind_param("ii", $gameId, $tricknumber);
        $stmt->execute();

        $result = array();
        $stmt->bind_result($id, $userid, $suit, $value);
        while($stmt->fetch()) {
            $row['id'] = $id;
            $row['userid'] = $userid;
            $row['suit'] = $suit;
            $row['value'] = $value;

            array_push($result, $row);
        }

        return $result;
    }
}
?>