<?php
require_once('Dao.php');
$return = Dao::insertReservation($_POST["idRepresentation"], $_POST["idClient"], $_POST["nbPlaces"]);
echo(json_encode($return));
?>