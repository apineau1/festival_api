<?php
class Dao{
    private static $pdo = null;

    public static function connecter()  {
        if (is_null(self::$pdo)) {
            try {
				$parameters = parse_ini_file('parameters.ini');
				$host=$parameters["host"];
				$db=$parameters["db"];
				$username=$parameters["username"];
				$password=$parameters["password"];

                self::$pdo = new PDO('mysql:host='.$host.';dbname='.$db,$username, $password);
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
		$requete = "select count(*) from client where login = :login";
        $stmt = self::connecter()->prepare($requete);
        $stmt->bindParam(':login', $login);
		$ok = $stmt->execute();
		$bool=false;
		if($stmt->fetch(PDO::FETCH_ASSOC)>0){
			$bool=true;
			$array["connexion"]="true";
		}
        $requete2 = "select * from client where login = :login";
        $stmt2 = self::connecter()->prepare($requete2);
        $stmt2->bindParam(':login', $login);
		$ok2 = $stmt2->execute();
		$enreg2=$stmt2->fetch(PDO::FETCH_ASSOC);
		if($ok2 && $enreg2["mdp"]==$mdp && $bool){
			$array["connexion"]="true";
			$array["id"]=$enreg2["idClient"];
			$array["nom"]=$enreg2["nom"];
			$array["prenom"]=$enreg2["prenom"];
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
        $requete = "select * from representation order by dateRepresentation,heureDebut";
        $stmt = self::connecter()->prepare($requete);
        $ok = $stmt->execute();
        if ($ok) {
            while ($enreg = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$array = array();
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
                $lesObjets[] = $array;
            }
        }
        return $lesObjets;
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
				$array3["identiteResponsable"]=$enreg3["identiteResponsable"];
				$array3["adressePostale"]=$enreg3["adressePostale"];
				$array3["nombrePersonnes"]=$enreg3["nombrePersonnes"];
				$array3["nomPays"]=$enreg3["nomPays"];
				$array3["hebergement"]=$enreg3["hebergement"];
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