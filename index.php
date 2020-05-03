<?php
require_once 'vendor/autoload.php';

require_once('Dao.php');

$app = new \Slim\App();

$app->get('/', function ($request, $response, $args) {
    return $response->withStatus(200)->write('Hello World!');
});

$app->get('/representations', function ($request, $response, $args) {    
    return $response->withStatus(200)->write(json_encode(Dao::getAllRepresentations()));
});

$app->get('/representation/{id}', function ($request, $response, $args) {
    return $response->withStatus(200)->write(json_encode(Dao::getOneRepresentationById($args['id'])));
});

$app->post('/connexion', function ($request, $response, $args) {
    return $response->withStatus(200)->write(json_encode(Dao::connexion($_POST["login"], $_POST["mdp"])));
});

$app->post('/insertReservation', function ($request, $response, $args) {
    return $response->withStatus(200)->write(json_encode(Dao::insertReservation($_POST["idRepresentation"], $_POST["idClient"], $_POST["nbPlaces"])));
});

$app->run();

?>