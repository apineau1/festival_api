<?php
require_once('Dao.php');
$connexion = Dao::connexion($_POST["login"], $_POST["mdp"]);
echo(json_encode($connexion));
?>