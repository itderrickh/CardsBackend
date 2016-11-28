<?php
require_once './config.php';
require_once './dbclasses/user.php';
require_once './dbclasses/game.php';
require_once './verify.php';
$AJAX_FORM = json_decode(file_get_contents('php://input'), true);

$userDao = new UserDAO($config);
$gameDao = new GameDAO($config);
$cardDao = new CardDAO($config);

$result = array();
$result['success'] = false;
$token = $_SERVER['HTTP_AUTHORIZE'];

if(verifyToken($token, $config)) {
    $gameId = $gameDao->getOrCreateGame();

    if($gameDao->isSetup($gameId)) {
        $result['success'] = true;
    } else {
        if($gameDao->isGameReady($gameId)) {
            $deck = $cardDao->getCards();
            $users = $userDao->getGameUsers($gameId);

            $gameDao->startGame($gameId, $deck, $users);
            $result['success'] = true;
        }
    }
    
}

echo json_encode($result);
?>