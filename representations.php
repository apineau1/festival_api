<?php
require_once('Dao.php');
$lesRepresentations = Dao::getAllRepresentations();
$array = array();
$array["representations"]=$lesRepresentations;
echo(json_encode($array));
?>