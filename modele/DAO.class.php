<?php
// -------------------------------------------------------------------------------------------------------------------------
//                                                 DAO : Data Access Object
//                   Cette classe fournit des méthodes d'accès à la bdd mrbs (projet Réservations M2L)
//                                                 Elle utilise l'objet PDO
//                       Auteur : JM Cartron                       Dernière modification : 1/9/2016
// -------------------------------------------------------------------------------------------------------------------------

// liste des méthodes de cette classe (dans l'ordre alphabétique) :

// __construct                   : le constructeur crée la connexion $cnx à la base de données
// __destruct                    : le destructeur ferme la connexion $cnx à la base de données
// annulerReservation            : enregistre l'annulation de réservation dans la bdd
// aPasseDesReservations         : recherche si l'utilisateur ($name) a passé des réservations à venir
// confirmerReservation          : enregistre la confirmation de réservation dans la bdd
// creerLesDigicodesManquants    : mise à jour de la table mrbs_entry_digicode (si besoin) pour créer les digicodes manquants
// creerUtilisateur              : enregistre l'utilisateur dans la bdd
// envoyerMdp                    : envoie un mail à l'utilisateur avec son nouveau mot de passe
// estLeCreateur                 : teste si un utilisateur ($nomUser) est le créateur d'une réservation ($idReservation)
// existeReservation             : fournit true si la réservation ($idReservation) existe, false sinon
// existeUtilisateur             : fournit true si l'utilisateur ($nomUser) existe, false sinon
// genererUnDigicode             : génération aléatoire d'un digicode de 6 caractères hexadécimaux
// getLesReservations            : fournit la liste des réservations à venir d'un utilisateur ($nomUser)
// getLesSalles                  : fournit la liste des salles disponibles à la réservation
// getNiveauUtilisateur          : fournit le niveau d'un utilisateur identifié par $nomUser et $mdpUser
// getReservation                : fournit un objet Reservation à partir de son identifiant $idReservation
// getUtilisateur                : fournit un objet Utilisateur à partir de son nom $nomUser
// modifierMdpUser               : enregistre le nouveau mot de passe de l'utilisateur dans la bdd après l'avoir hashé en MD5
// supprimerUtilisateur          : supprime l'utilisateur dans la bdd
// testerDigicodeBatiment        : teste si le digicode saisi ($digicodeSaisi) correspond bien à une réservation de salle quelconque
// testerDigicodeSalle           : teste si le digicode saisi ($digicodeSaisi) correspond bien à une réservation

// certaines méthodes nécessitent les fichiers Reservation.class.php, Utilisateur.class.php, Salle.class.php et Outils.class.php
include_once ('Utilisateur.class.php');
include_once ('Reservation.class.php');
include_once ('Salle.class.php');
include_once ('Outils.class.php');

// inclusion des paramètres de l'application
include_once ('parametres.localhost.php');

// début de la classe DAO (Data Access Object)
class DAO
{
	// ------------------------------------------------------------------------------------------------------
	// ---------------------------------- Membres privés de la classe ---------------------------------------
	// ------------------------------------------------------------------------------------------------------
		
	private $cnx;				// la connexion à la base de données
	
	// ------------------------------------------------------------------------------------------------------
	// ---------------------------------- Constructeur et destructeur ---------------------------------------
	// ------------------------------------------------------------------------------------------------------
	public function __construct() {
		global $PARAM_HOTE, $PARAM_PORT, $PARAM_BDD, $PARAM_USER, $PARAM_PWD;
		try
		{	$this->cnx = new PDO ("mysql:host=" . $PARAM_HOTE . ";port=" . $PARAM_PORT . ";dbname=" . $PARAM_BDD,
							$PARAM_USER,
							$PARAM_PWD);
			return true;
		}
		catch (Exception $ex)
		{	echo ("Echec de la connexion a la base de donnees <br>");
			echo ("Erreur numero : " . $ex->getCode() . "<br />" . "Description : " . $ex->getMessage() . "<br>");
			echo ("PARAM_HOTE = " . $PARAM_HOTE);
			return false;
		}
	}
	
	public function __destruct() {
		unset($this->cnx);
	}

	// ------------------------------------------------------------------------------------------------------
	// -------------------------------------- Méthodes d'instances ------------------------------------------
	// ------------------------------------------------------------------------------------------------------
	
	/// aPasseDesReservations : recherche si l'utilisateur ($name) a passé des réservations à venir
	// crée par Sophie le 27/09/2016
	public function aPasseDesReservations($nomUser)
	{	// préparation de la requête pour rechercher si l'utilisateur passé des réservations
		$txt_req = "Select count(*) from mrbs_entry where create_by = :nomUser";
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("nomUser", $nomUser, PDO::PARAM_STR);
		// exécution de la requete
		$req->execute();
		$nbReponses = $req->fetchColumn(0);
		// libère les ressources du jeu de données
		$req->closeCursor();
		
		// fourniture de la réponse
		if ($nbReponses == 0)
			return false;
		else
			return true;
	}
	
	// enregistre l'annulation de réservation
	// modifié par Valentin Bachelier le 27/09/2016
	public function annulerReservation($idReservation)
	{	// préparation de la requete
		$txt_req = "delete from mrbs_entry where id = :idReservation";
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("idReservation", $idReservation, PDO::PARAM_INT);
		// exécution de la requete
		$ok = $req->execute();
		return $ok;

	}


	// estLeCreateur: teste si un utilisateur ($nomUser) est le créateur d'une réservation ($idReservation)
	// modifié par Florentin Gremy le 04/10/2016
	public function estLeCreateur($nomUser, $idReservation)
	{
		$txt_req = "Select count(mrbs_entry.id) from mrbs_entry, mrbs_users where mrbs_entry.create_by = mrbs_users.name and mrbs_entry.create_by = :nomUser and mrbs_entry.id = :idReservation";
		$req = $this->cnx->prepare($txt_req);
		$req->bindValue("nomUser", $nomUser, PDO::PARAM_STR);
		$req->bindValue("idReservation", $idReservation, PDO::PARAM_INT);
		// exécution de la requete
		$req->execute();
		$nbReponses = $req->fetchColumn(0);
		// libère les ressources du jeu de données
		$req->closeCursor();
		
		// fourniture de la réponse
		if ($nbReponses == 0)
			return false;
			else
				return true;
	}

	
	// confirmerReservation: enregistre la confirmation de réservation dans la bdd
	// modifié par Florentin Gremy le 04/10/2016
	public function confirmerReservation($idReservation)
	{
		//préparation de la requete de recherche du statut de la réservation
		$txt_req = "UPDATE mrbs_entry SET Status ='0' WHERE id = :idReservation";
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("idReservation",$idReservation, PDO::PARAM_INT);
		// exécution de la requete
		$ok = $req->execute();
		return $ok;
	}

	// existeReservation: fournit true si la réservation ($idReservation) existe, false sinon
	//modifié par Florentin Gremy le 11/10/2016
	public function existeReservation($idReservation)
	{
		$txt_req = "Select id from mrbs_entry where id = :idReservation";
		$req = $this->cnx->prepare($txt_req);
		$req->bindValue("idReservation", $idReservation, PDO::PARAM_INT);
		$req->execute();
		$nbReponses = $req->fetchColumn(0);
		// libère les ressources du jeu de données
		$req->closeCursor();
		
		// fourniture de la réponse
		if ($nbReponses == 0)
			return false;
			else
				return true;
	}
	

	// mise à jour de la table mrbs_entry_digicode (si besoin) pour créer les digicodes manquants
	// cette fonction peut dépanner en cas d'absence des triggers chargés de créer les digicodes
	// modifié par Jim le 5/5/2015
	public function creerLesDigicodesManquants()
	{	// préparation de la requete de recherche des réservations sans digicode
		$txt_req1 = "Select id from mrbs_entry where id not in (select id from mrbs_entry_digicode)";
		$req1 = $this->cnx->prepare($txt_req1);
		// extraction des données
		$req1->execute();
		// extrait une ligne du résultat :
		$uneLigne = $req1->fetch(PDO::FETCH_OBJ);
		// tant qu'une ligne est trouvée :
		while ($uneLigne)
		{	// génération aléatoire d'un digicode de 6 caractères hexadécimaux
			$digicode = $this->genererUnDigicode();
			// préparation de la requete d'insertion
			$txt_req2 = "insert into mrbs_entry_digicode (id, digicode) values (:id, :digicode)";
			$req2 = $this->cnx->prepare($txt_req2);
			// liaison de la requête et de ses paramètres
			$req2->bindValue("id", $uneLigne->id, PDO::PARAM_INT);
			$req2->bindValue("digicode", $digicode, PDO::PARAM_STR);
			// exécution de la requête
			$req2->execute();
			// extrait la ligne suivante
			$uneLigne = $req1->fetch(PDO::FETCH_OBJ);
		}
		// libère les ressources du jeu de données
		$req1->closeCursor();
		return;
	}
	
	/*
	 // mise à jour de la table mrbs_entry_digicode (si besoin) pour créer les digicodes manquants
	 // cette fonction peut dépanner en cas d'absence des triggers chargés de créer les digicodes
	 // modifié par Jim le 23/9/2015
	 public function creerLesDigicodesManquants()
	 {	// récupération de la date du jour
		 $dateCreation = date('Y-m-d H:i:s', time());
		 // préparation de la requete de recherche des réservations sans digicode
		 $txt_req1 = "Select id from mrbs_entry where id not in (select id from mrbs_entry_digicode)";
		 $req1 = $this->cnx->prepare($txt_req1);
		 // extraction des données
		 $req1->execute();
		 // extrait une ligne du résultat :
		 $uneLigne = $req1->fetch(PDO::FETCH_OBJ);
		 // tant qu'une ligne est trouvée :
		
		 while ($uneLigne)
		 {	// génération aléatoire d'un digicode de 6 caractères hexadécimaux
			 $digicode = $this->genererUnDigicode();
			 // préparation de la requete d'insertion
			 $txt_req2 = "insert into mrbs_entry_digicode (id, digicode, dateCreation) values (:id, :digicode, :dateCreation)";
			 $req2 = $this->cnx->prepare($txt_req2);
			 // liaison de la requête et de ses paramètres
			 $req2->bindValue("id", $uneLigne->id, PDO::PARAM_INT);
			 $req2->bindValue("digicode", $digicode, PDO::PARAM_STR);
			 $req2->bindValue("dateCreation", $dateCreation, PDO::PARAM_INT);
			 // exécution de la requête
			 $req2->execute();
			 // extrait la ligne suivante
			 $uneLigne = $req1->fetch(PDO::FETCH_OBJ);
		 }
		 // libère les ressources du jeu de données
		 $req1->closeCursor();
		 return;
	 }
	*/ 

	// enregistre l'utilisateur dans la bdd
	// modifié par Jim le 26/5/2016
	public function creerUtilisateur($unUtilisateur)
	{	// préparation de la requete
		$txt_req = "insert into mrbs_users (level, name, password, email) values (:level, :name, :password, :email)";
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("level", utf8_decode($unUtilisateur->getLevel()), PDO::PARAM_STR);
		$req->bindValue("name", utf8_decode($unUtilisateur->getName()), PDO::PARAM_STR);
		$req->bindValue("password", utf8_decode(md5($unUtilisateur->getPassword())), PDO::PARAM_STR);
		$req->bindValue("email", utf8_decode($unUtilisateur->getEmail()), PDO::PARAM_STR);
		// exécution de la requete
		$ok = $req->execute();
		return $ok;
	}
	
	// getUtilisateur: fournit un objet Utilisateur à partir de son nom $nomUser
	//modifié par sophie le 11/10/2016
	public function getUtilisateur($nomUser)
	{		
		$txt_req = "SELECT * FROM mrbs_users WHERE name = :nomUser";
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("nomUser", $nomUser, PDO::PARAM_STR);
		$req->execute();
		$uneLigne = $req->fetch(PDO::FETCH_OBJ);
		// libère les ressources du jeu de données
		$req->closeCursor();
		
		if ( ! $uneLigne)
			return null;
			else
			{	// création d'un objet Reservation
				$unId = utf8_encode($uneLigne->id);
				$unLevel = utf8_encode($uneLigne->level);
				$unName = utf8_encode($uneLigne->name);
				$unPassword = utf8_encode($uneLigne->password);
				$unEmail = utf8_encode($uneLigne->email);
				
				$unUtilisateur = new Utilisateur($unId,$unLevel,$unName,$unPassword,$unEmail);
				return $unUtilisateur;
			}
	}
	
	 // modifier d'un mot de passe
	 // crée par Sophie le 11/10/2016
	public function modifierMdpUser($nomUser, $nouveauMdp)
	{	// préparation de la requete
		$txt_req = "update mrbs_users set password = :nouveauMdp where name = :nomUser";
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("nouveauMdp", md5($nouveauMdp), PDO::PARAM_STR);
		$req->bindValue("nomUser", $nomUser, PDO::PARAM_STR);		
		// exécution de la requete
		$ok = $req->execute();
		return $ok;
	}
	


	// envoie un mail à l'utilisateur avec son nouveau mot de passe
	// retourne true si envoi correct, false en cas de problème d'envoi
	 // crée par Sophie le 04/10/2016
	public function envoyerMdp($nomUser, $nouveauMdp)
	{	global $ADR_MAIL_EMETTEUR;
		// si l'adresse n'est pas dans la table mrbs_users :
		if ( ! $this->existeUtilisateur($nomUser) ) return false;

		// recherche de l'adresse mail
		$adrMail = $this->getUtilisateur($nomUser)->getEmail();
		
		// envoie un mail à l'utilisateur avec son nouveau mot de passe 
		$sujet = "Modification de votre mot de passe d'accès au service Réservations M2L";
		$message = "Votre mot de passe d'accès au service Réservations M2L a été modifié.\n\n";
		$message .= "Votre nouveau mot de passe est : " . $nouveauMdp;
		$ok = Outils::envoyerMail ($adrMail, $sujet, $message, $ADR_MAIL_EMETTEUR);
		return $ok;
	}
	

	// fournit true si l'utilisateur ($nomUser) existe, false sinon
	// modifié par Jim le 5/5/2015
	public function existeUtilisateur($nomUser)
	{	// préparation de la requete de recherche
		$txt_req = "Select count(*) from mrbs_users where name = :nomUser";
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("nomUser", $nomUser, PDO::PARAM_STR);
		// exécution de la requete
		$req->execute();
		$nbReponses = $req->fetchColumn(0);
		// libère les ressources du jeu de données
		$req->closeCursor();
		
		// fourniture de la réponse
		if ($nbReponses == 0)
			return false;
		else
			return true;
	}
	
	// supprime l'utilisateur dans la bdd
	// modifié par Valentin Bachelier
	public function supprimerUtilisateur($name)
	{	// préparation de la requete
		$txt_req = "delete from mrbs_users where name = :name" ;
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("name", utf8_decode($name), PDO::PARAM_STR);
		// exécution de la requete
		$ok = $req->execute();
		return $ok;
	}	
	

	// génération aléatoire d'un digicode de 6 caractères hexadécimaux
	// modifié par Jim le 5/5/2015
	public function genererUnDigicode()
	{   $caracteresUtilisables = "0123456789ABCDEF";
		$digicode = "";
		// on ajoute 6 caractères
		for ($i = 1 ; $i <= 6 ; $i++)
		{   // on tire au hasard un caractère (position aléatoire entre 0 et le nombre de caractères - 1)
			$position = rand (0, strlen($caracteresUtilisables)-1);
			// on récupère le caracère correspondant à la position dans $caracteresUtilisables
			$unCaractere = substr ($caracteresUtilisables, $position, 1);
			// on ajoute ce caractère au digicode
			$digicode = $digicode . $unCaractere;
		}
		// fourniture de la réponse
		return $digicode;
	}

	// fournit la liste des réservations à venir d'un utilisateur ($nomUser)
	// le résultat est fourni sous forme d'une collection d'objets Reservation
	// modifié par Jim le 30/9/2015
	public function getLesReservations($nomUser)
	{	// préparation de la requete de recherche
		$txt_req = "Select mrbs_entry.id as id_entry, timestamp, start_time, end_time, room_name, status, digicode";
		$txt_req = $txt_req . " from mrbs_entry, mrbs_room, mrbs_entry_digicode";
		$txt_req = $txt_req . " where mrbs_entry.room_id = mrbs_room.id";
		$txt_req = $txt_req . " and mrbs_entry.id = mrbs_entry_digicode.id";
		$txt_req = $txt_req . " and create_by = :nomUser";
		$txt_req = $txt_req . " and start_time > :time";
		$txt_req = $txt_req . " order by start_time, room_name";
		
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("nomUser", $nomUser, PDO::PARAM_STR);
		$req->bindValue("time", time(), PDO::PARAM_INT);
		// extraction des données
		$req->execute();
		$uneLigne = $req->fetch(PDO::FETCH_OBJ);
		
		// construction d'une collection d'objets Reservation
		$lesReservations = array();
		// tant qu'une ligne est trouvée :
		while ($uneLigne)
		{	// création d'un objet Reservation
			$unId = utf8_encode($uneLigne->id_entry);
			$unTimeStamp = utf8_encode($uneLigne->timestamp);
			$unStartTime = utf8_encode($uneLigne->start_time);
			$unEndTime = utf8_encode($uneLigne->end_time);
			$unRoomName = utf8_encode($uneLigne->room_name);
			$unStatus = utf8_encode($uneLigne->status);
			$unDigicode = utf8_encode($uneLigne->digicode);
				
			$uneReservation = new Reservation($unId, $unTimeStamp, $unStartTime, $unEndTime, $unRoomName, $unStatus, $unDigicode);
			// ajout de la réservation à la collection
			$lesReservations[] = $uneReservation;
			// extrait la ligne suivante
			$uneLigne = $req->fetch(PDO::FETCH_OBJ);
		}
		// libère les ressources du jeu de données
		$req->closeCursor();
		// fourniture de la collection
		return $lesReservations;
	}
	
	// fournit un objet Reservation à partir de son identifiant
	// fournit la valeur null si l'identifiant n'existe pas
	// modifié par Valentin Bachelier 04/10/2016
	public function getReservation($idReservation)
	{	// préparation de la requete de recherche
		$txt_req = "Select mrbs_entry.id, timestamp, start_time, end_time, room_name, status, digicode ";
		$txt_req = $txt_req . " from mrbs_entry, mrbs_entry_digicode, mrbs_room ";
		$txt_req = $txt_req . " where mrbs_entry.room_id = mrbs_room.id ";
		$txt_req = $txt_req . " and mrbs_entry.id = mrbs_entry_digicode.id";
		$txt_req = $txt_req . " and mrbs_entry.id = :idReservation";
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("idReservation", $idReservation, PDO::PARAM_INT);
		// extraction des données
		$req->execute();
		$uneLigne = $req->fetch(PDO::FETCH_OBJ);
		// libère les ressources du jeu de données
		$req->closeCursor();
	
		// traitement de la réponse
		if ( ! $uneLigne)
			return null;
			else
			{	// création d'un objet Reservation
				$unId = utf8_encode($uneLigne->id);
				$unTimeStamp = utf8_encode($uneLigne->timestamp);
				$unStartTime = utf8_encode($uneLigne->start_time);
				$unEndTime = utf8_encode($uneLigne->end_time);
				$unRoomName = utf8_encode($uneLigne->room_name);
				$unStatus = utf8_encode($uneLigne->status);
				$unDigicode = utf8_encode($uneLigne->digicode);
				
				$uneReservation = new Reservation($unId, $unTimeStamp, $unStartTime, $unEndTime, $unRoomName, $unStatus, $unDigicode);
				return $uneReservation;
			}
	}

	// fournit le niveau d'un utilisateur identifié par $nomUser et $mdpUser
	// renvoie "utilisateur" ou "administrateur" si authentification correcte, "inconnu" sinon
	// modifié par Jim le 5/5/2015
	public function getNiveauUtilisateur($nomUser, $mdpUser)
	{	// préparation de la requête de recherche
		$txt_req = "Select level from mrbs_users where name = :nomUser and password = :mdpUserCrypte and level > 0";
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("nomUser", $nomUser, PDO::PARAM_STR);
		$req->bindValue("mdpUserCrypte", md5($mdpUser), PDO::PARAM_STR);		
		// extraction des données
		$req->execute();
		$uneLigne = $req->fetch(PDO::FETCH_OBJ);
		// traitement de la réponse
		$reponse = "inconnu";
		if ($uneLigne)
		{	$level = $uneLigne->level;
			if ($level == "1") $reponse = "utilisateur";
			if ($level == "2") $reponse = "administrateur";
		}
		// libère les ressources du jeu de données
		$req->closeCursor();
		// fourniture de la réponse
		return $reponse;
	}	

	// teste si le digicode saisi ($digicodeSaisi) correspond bien à une réservation
	// de la salle indiquée ($idSalle) pour l'heure courante
	// fournit la valeur 0 si le digicode n'est pas bon, 1 si le digicode est bon
	// modifié par Jim le 18/5/2015
	public function testerDigicodeSalle($idSalle, $digicodeSaisi)
	{	global $DELAI_DIGICODE;
		// préparation de la requete de recherche
		$txt_req = "Select count(*)";
		$txt_req = $txt_req . " from mrbs_entry, mrbs_entry_digicode";
		$txt_req = $txt_req . " where mrbs_entry.id = mrbs_entry_digicode.id";
		$txt_req = $txt_req . " and room_id = :idSalle";
		$txt_req = $txt_req . " and digicode = :digicodeSaisi";
		$txt_req = $txt_req . " and (start_time - :delaiDigicode) < " . time();
		$txt_req = $txt_req . " and (end_time + :delaiDigicode) > " . time();
		
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("idSalle", $idSalle, PDO::PARAM_STR);
		$req->bindValue("digicodeSaisi", $digicodeSaisi, PDO::PARAM_STR);	
		$req->bindValue("delaiDigicode", $DELAI_DIGICODE, PDO::PARAM_INT);	
				
		// exécution de la requete
		$req->execute();
		$nbReponses = $req->fetchColumn(0);
		// libère les ressources du jeu de données
		$req->closeCursor();
		
		// fourniture de la réponse
		if ($nbReponses == 0)
			return "0";
		else
			return "1";
	}
	

	// testerDigicodeBatiment: teste si le digicode saisi ($digicodeSaisi) correspond bien à une réservation de salle quelconque
	//Modifié par Florentin Gremy le 11/10/2016
	public function testerDigicodeBatiment($digicodeSaisi)
	{
		$txt_req = "Select count(*) from mrbs_entry_digicode where digicode = :digicodeSaisi";
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("digicodeSaisi", $digicodeSaisi, PDO::PARAM_STR);
		
		// exécution de la requete
		$req->execute();
		$nbReponses = $req->fetchColumn(0);
		// libère les ressources du jeu de données
		$req->closeCursor();
		
		// fourniture de la réponse
		if ($nbReponses == 0)
			return "0";
			else
				return "1";
	}

	// Supprimer un utilisateur
	//modifié par Valentin Bachelier le 27/09/2016
	// public function supprimerUtilisateur($idReservation)

	// fournit la liste des salles disponibles à la réservation
	// le résultat est fourni sous forme d'une collection d'objets Salle
	// modifié par Bachelier Valentin
	function getLesSalles()
	{	// préparation de la requete de recherche
		$txt_req = "Select mrbs_room.id, mrbs_room.room_name, mrbs_room.capacity, mrbs_area.area_name, mrbs_area.area_admin_email";
		$txt_req = $txt_req . " from mrbs_room, mrbs_area";
		$txt_req = $txt_req . " where mrbs_room.area_id = mrbs_area.id";
		$txt_req = $txt_req . " order by mrbs_area.area_name, mrbs_room.room_name";
	
		$req = $this->cnx->prepare($txt_req);
		// extraction des données
		$req->execute();
		$uneLigne = $req->fetch(PDO::FETCH_OBJ);
	
		// construction d'une collection d'objets Salle
		$lesSalles = array();
		// tant qu'une ligne est trouvée :
		while ($uneLigne)
		{	// création d'un objet Salle
			$unId = utf8_encode($uneLigne->id);
			$unRoomName = utf8_encode($uneLigne->room_name);
			$unCapacity = utf8_encode($uneLigne->capacity);
			$unAreaName = utf8_encode($uneLigne->area_name);
			$unAeraAdminEmail = utf8_encode($uneLigne->area_admin_email);
		
			$uneSalle = new Salle($unId, $unRoomName, $unCapacity, $unAreaName, $unAeraAdminEmail);
			// ajout de la réservation à la collection
			$lesSalles[] = $uneSalle;
			// extrait la ligne suivante
			$uneLigne = $req->fetch(PDO::FETCH_OBJ);
		}
		// libère les ressources du jeu de données
		$req->closeCursor();
		// fourniture de la collection
		return $lesSalles;
	}

} // fin de la classe DAO

// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!