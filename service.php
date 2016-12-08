<?php
require_once './config.php';
require_once './dbclasses/bid.php';
require_once './dbclasses/card.php';
require_once './dbclasses/game.php';
require_once './dbclasses/hand.php';
require_once './dbclasses/user.php';
require_once './verify.php';

$bidDao = new BidDAO($config);
$cardDao = new CardDAO($config);
$gameDao = new GameDAO($config);
$handDao = new HandDAO($config);
$userDao = new UserDAO($config);

$AJAX_FORM = json_decode(file_get_contents('php://input'), true);

$token = $_SERVER['HTTP_AUTHORIZE'];

if(verifyToken($token, $config)) {
    $tokenInfo = getTokenInfo($token);
    $user = $userDao->getUser($tokenInfo['email']);

    $result = array();

    $game = $gameDao->getOrCreateGame();

    //Join game
    if($game['status'] == 1) {
        if(!$gameDao->isGameSetup($game['id'])) {
            $gameDao->addUserToGame($user['id'], $game['id']);

            //Return hand
            $result['status'] = 1;
        } else {
            $gameDao->setGameStatus(2, $game['id']);
        }
    }
    //Setup game
    else if($game['status'] == 2) {
        $deck = $cardDao->getCards();
        $users = $userDao->getGameUsers($game['id']);
        $gameDao->startGame($game['id'], $deck, $users);
        $gameDao->setGameStatus(3, $game['id']);

        $result['status'] = 2;
    }
    //Wait for bids/return bids
    else if($game['status'] == 3) {
        $bids = $bidDao->getBids($game['id']);

        if(count($bids) >= 5) {
            $gameDao->setGameStatus(4, $game['id']);
        }

        $result['status'] = 3;
        $result['bids'] = $bids;
        $result['hand'] = $handDao->getHand($user['id'], $game['id']);
        $result['users'] = $userDao->getGameUsers($game['id']);
    } else if($game['status'] == 4) {
        $result['status'] = 4;
        $result['yourturn'] = ($game['currentplayer'] == $user['id']);
    } else if($game['status'] == 5) {
        //Determine scores

        $gameDao->resetUsers($game['id']);
        $gameDao->setGameStatus(6, $game['id']);
    } else if($game['status'] == 6) {
        $gameDao->completeGame($game['id']);
    }

    echo json_encode($result);
}
?>