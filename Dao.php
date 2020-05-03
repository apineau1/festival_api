<?php
class Dao{
    private static $pdo = null;

    public static function connecter()  {
        if (is_null(self::$pdo)) {
            try {
                self::$pdo = new PDO('mysql:host='.$_ENV["host"].';dbname='.$_ENV["db"],$_ENV["username"], $_ENV["password"]);
				self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				self::$pdo->query("SET NAMES utf8");

            } catch (PDOException $e) {
                echo "ERREUR : " . $e->getMessage();
                die();
            }
        }
        return self::$pdo;
    }

	public static function connexion($login, $mdp){
		$array = array();
		$requete = "select * from client where login = :login";
        $stmt = self::connecter()->prepare($requete);
        $stmt->bindParam(':login', $login);
		$ok = $stmt->execute();
		$enreg=$stmt->fetch(PDO::FETCH_ASSOC);
		if($ok && $enreg["mdp"]==$mdp){
			$array["connexion"]="true";
			$array["id"]=$enreg["idClient"];
			$array["nom"]=$enreg["nom"];
			$array["prenom"]=$enreg["prenom"];
		}else{
			$array["connexion"]="false";
		}
		return $array;
	}

	public static function insertReservation($idRepresentation, $idClient, $nbPlaces)
	{
		$array = array();
		$requete = "insert into reservation (idRepresentation, idClient, nbPlaces) values (:idRepresentation, :idClient, :nbPlaces)";
		$stmt = self::connecter()->prepare($requete);
		$stmt->bindParam(':idRepresentation', $idRepresentation);
		$stmt->bindParam(':idClient', $idClient);
		$stmt->bindParam(':nbPlaces', $nbPlaces);
		$ok = $stmt->execute();
		
		$requete2 = "update representation set nbPlacesDisponibles = (nbPlacesDisponibles - :nbPlacesPrises) where idRepresentation = :id";
		$stmt2 = self::connecter()->prepare($requete2);
		$stmt2->bindParam(':nbPlacesPrises', $nbPlaces);
        $stmt2->bindParam(':id', $idRepresentation);
		$ok2 = $stmt2->execute();
		
		if($ok && $ok2){
			$array["return"]="true";
		}else{
			$array["return"]="false";
		}
		return $array;
	}

    public static function getAllRepresentations()
    {
        $lesObjets = array();
        $requete = "select idRepresentation from representation order by dateRepresentation,heureDebut";
        $stmt = self::connecter()->prepare($requete);
        $ok = $stmt->execute();
        if ($ok) {
            while ($enreg = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$array = self::getOneRepresentationById($enreg["idRepresentation"]);
                $lesObjets[] = $array;
            }
		}
		$array = array();
    	$array["representations"]=$lesObjets;
        return $array;
    }

    public static function getOneRepresentationById($id)
    {
        $array = array();
        $requete = "select * from representation where idRepresentation = :id";
        $stmt = self::connecter()->prepare($requete);
        $stmt->bindParam(':id', $id);
        $ok = $stmt->execute();
        if ($ok && $stmt->rowCount() > 0) {
			$enreg=$stmt->fetch(PDO::FETCH_ASSOC);
            $array["id"]=$enreg["idRepresentation"];
			
			$array2 = array();
			$requete2 = "select * from lieu where idLieu = :id";
			$stmt2 = self::connecter()->prepare($requete2);
			$stmt2->bindParam(':id', $enreg["idLieu"]);
			$ok2 = $stmt2->execute();
			if ($ok2 && $stmt2->rowCount() > 0) {
				$enreg2=$stmt2->fetch(PDO::FETCH_ASSOC);
				$array2["id"]=$enreg2["idLieu"];
				$array2["nom"]=$enreg2["nomLieu"];
				$array2["adresseLieu"]=$enreg2["adrLieu"];
				$array2["capAccueil"]=$enreg2["capAccueil"];
			}
			
			$array["lieu"]=$array2;
						
			$array3 = array();
			$requete3 = "select * from groupe where id = :id";
			$stmt3 = self::connecter()->prepare($requete3);
			$stmt3->bindParam(':id', $enreg["idGroupe"]);
			$ok3 = $stmt3->execute();
			if ($ok3 && $stmt3->rowCount() > 0) {
				$enreg3=$stmt3->fetch(PDO::FETCH_ASSOC);
				$array3["id"]=$enreg3["id"];
				$array3["nom"]=$enreg3["nom"];
			}
			$array["groupe"]=$array3;
						
			$array["date"]=$enreg["dateRepresentation"];
			$array["heureDebut"]=$enreg["heureDebut"];
			$array["heureFin"]=$enreg["heureFin"];
			$array["nbPlacesDisponibles"]=$enreg["nbPlacesDisponibles"];
        }
        return $array;
    }
}
?>