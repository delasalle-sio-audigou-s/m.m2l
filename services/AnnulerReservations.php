<?php
// Service web du projet Réservations M2L
// Ecrit le 22/11/2016 par Bachelier Valentin
// Ce service web permet à un utilisateur d'annuler une réservation (à condition d'être l'auteur de la réservation)
// Le service web doit recevoir 3 paramètres : nom, mdp, numreservation

// inclusion de la classe Outils
include_once ('../modele/Outils.class.php');
// inclusion des paramètres de l'application
include_once ('../modele/parametres.localhost.php');

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
if ( empty ($_GET ["nom"]) == true)  $nom = "";  else   $nom = $_GET ["nom"];
if ( empty ($_GET ["mdp"]) == true)  $mdp = "";  else   $mdp = $_GET ["mdp"];
if ( empty ($_GET ["numreservation"]) == true)  $numReservation = "";  else   $numReservation = $_GET ["numreservation"];
// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nom == "" && $mdp == "" && $numReservation == "" )
{	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
if ( empty ($_POST ["mdp"]) == true)  $mdp = "";  else   $mdp = $_POST ["mdp"];
if ( empty ($_POST ["numreservation"]) == true)  $numReservation = "";  else   $numReservation = $_POST ["numreservation"];
}
// Contrôle de la présence des paramètres
if ( $nom == "" || $mdp == "" || $numReservation == "" )
{	$msg = "Erreur : données incomplètes.";
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
include_once ('../modele/DAO.class.php');
$dao = new DAO();

if ( $dao->getNiveauUtilisateur($nom, $mdp) == "inconnu" )
{	$msg = "Erreur : authentification incorrecte.";
}
else
{	if ( ! $dao->existeReservation($numReservation) )
{	$msg = "Erreur : numéro de réservation inexistant.";
}
else
{	if ( ! $dao->estLeCreateur($nom, $numReservation) )
{	$msg = "Erreur : vous n'êtes pas l'auteur de cette réservation.";
}
else
{	if ( $dao->getReservation($numReservation)->getStart_time() < time() )
{	$msg = "Erreur : cette réservation est déjà passée.";
}
else
{
	// supprime la réservation dans la base de données
	$dao->annulerReservation($numReservation);

	// recherche de l'adresse mail
	$adrMail = $dao->getUtilisateur($nom)->getEmail();
		
	// envoie un mail de confirmation de l'enregistrement
	$sujet = "Suppression de réservation";
	$contenuMail = "Nous avons bien enregistré la suppression de la réservation N° " . $numReservation ;
	$ok = Outils::envoyerMail ($adrMail, $sujet, $contenuMail, $ADR_MAIL_EMETTEUR);

	if ( $ok )
		$msg = "Enregistrement effectué ; vous allez recevoir un mail de confirmation.";
		else
			$msg = "Enregistrement effectué ; l'envoi du mail de confirmation a rencontré un problème.";
}
}
}
}
// ferme la connexion à MySQL :
unset($dao);
}
// création du flux XML en sortie
creerFluxXML ($msg);
// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;
// création du flux XML en sortie
function creerFluxXML($msg)
{	// crée une instance de DOMdocument (DOM : Document Object Model)
$doc = new DOMDocument();

// specifie la version et le type d'encodage
$doc->version = '1.0';
$doc->encoding = 'ISO-8859-1';

// crée un commentaire et l'encode en ISO
$elt_commentaire = $doc->createComment('Service web AnnulerReservation - BTS SIO - Lycée De La Salle - Rennes');
// place ce commentaire à la racine du document XML
$doc->appendChild($elt_commentaire);

// crée l'élément 'data' à la racine du document XML
$elt_data = $doc->createElement('data');
$doc->appendChild($elt_data);

// place l'élément 'reponse' juste après l'élément 'data'
$elt_reponse = $doc->createElement('reponse', $msg);
$elt_data->appendChild($elt_reponse);

// Mise en forme finale
$doc->formatOutput = true;

// renvoie le contenu XML
echo $doc->saveXML();
return;
}
?>