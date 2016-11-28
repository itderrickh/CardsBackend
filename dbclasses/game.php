<?php
class GameDAO {
    private $config;
    function __construct($config) {
        $this->config = $config;
    }

    function getCurrentGame() {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT id FROM games WHERE iscomplete = 0 LIMIT 1");
        $stmt->execute();

        $stmt->bind_result($id);
        
        $stmt->close();
        $mysqli->close();

        return $id;
    }

    function createGame() {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("INSERT INTO games(iscomplete) VALUES (0)");
        $stmt->execute();
        
        $stmt->close();
        $mysqli->close();
    }

    function addUserToGame($userid, $gameid) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("INSERT INTO gameuser(userid, gameid) VALUES (?, ?)");
        $stmt->bind_param("ii", $userid, $gameid);
        $stmt->execute();
        
        $stmt->close();
        $mysqli->close();
    }

    function getOrCreateGame() {
        $gameid = $this->getCurrentGame();
        if(is_null($game)) {
            $this->createGame();
            $gameid = $this->getCurrentGame();
        }

        return $gameid;
    }

    function isGameSetup($gameId) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT COUNT(*) AS Count FROM hands WHERE gameid = ?");
        $stmt->bind_param("i", $gameid);
        $stmt->execute();
        
        $stmt->bind_result($count);

        $stmt->close();
        $mysqli->close();

        return $count >= 1;
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

            $i += 1;
        }

        $mysqli->close();
    }

    function isGameReady($gameid) {
        $mysqli = new mysqli($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbdatabase']);
        $stmt = $mysqli->prepare("SELECT COUNT(*) AS Count FROM gameuser WHERE gameid = ?");
        $stmt->bind_param("i", $gameid);
        $stmt->execute();
        
        $stmt->bind_result($count);

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
}
?>