<?php
require_once('Dao.php');
$uneRepresentation = Dao::getOneRepresentationById($_POST["id"]);
echo(json_encode($uneRepresentation));
?>