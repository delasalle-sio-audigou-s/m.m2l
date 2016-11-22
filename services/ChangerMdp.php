<?php
// Service web du projet Réservations M2L
// Ecrit le 22/11/2016 par Sophie

// Ce service web permet à un utilisateur de modifier le mdp d'un utilisateur
// et fournit un flux XML contenant un compte-rendu d'exécution

// Le service web doit recevoir 4 paramètres : nom, ancienMdp, nouveauMdp, confirmationMdp 
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/ChangerDeMdp.php?nom=zenelsy&ancienMdp=passe&nouveauMdp=123&confirmationMdp=123


// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/ChangerDeMdp.php

// inclusion de la classe Outils
include_once ('../modele/Outils.class.php');
// inclusion des paramètres de l'application
include_once ('../modele/parametres.localhost.php');
	
// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
if ( empty ($_GET ["nom"]) == true)  $nom = "";  else   $nom = $_GET ["nom"];
if ( empty ($_GET ["ancienMdp"]) == true)  $ancienMdp = "";  else   $ancienMdp = $_GET ["ancienMdp"];
if ( empty ($_GET ["nouveauMdp"]) == true)  $nouveauMdp = "";  else   $nouveauMdp = $_GET ["nouveauMdp"];
if ( empty ($_GET ["confirmationMdp"]) == true)  $confirmationMdp = "";  else   $confirmationMdp = $_GET ["confirmationMdp"];

// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nom == "" && $ancienMdp == ""&& $nouveauMdp == ""&& $confirmationMdp == "" )
{	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
	if ( empty ($_POST ["ancienMdp"]) == true)  $ancienMdp = "";  else   $ancienMdp = $_POST ["ancienMdp"];
	if ( empty ($_POST ["nouveauMdp"]) == true)  $nouveauMdp = "";  else   $nouveauMdp = $_POST ["nouveauMdp"];
	if ( empty ($_POST ["confirmationMdp"]) == true)  $confirmationMdp = "";  else   $confirmationMdp = $_POST ["confirmationMdp"];
}

// Contrôle de la présence des paramètres
if ( $nom == "" || $ancienMdp == "" || $nouveauMdp == "" || $confirmationMdp == "" )
{	$msg = "Erreur : données incomplètes.";
} 
else 
{
	if ( $nouveauMdp != $confirmationMdp )
	{	$msg = "Erreur : le nouveau mot de passe et sa confirmation sont différents.";
	}
	else	
	{
		// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
		include_once ('../modele/DAO.class.php');
		$dao = new DAO();
	
		if ( $dao->getNiveauUtilisateur($nom, $ancienMdp) == "inconnu" )
			$msg = "Erreur : authentification incorrecte.";
		else {
			// enregistre le nouveau mot de passe de l'utilisateur dans la bdd après l'avoir codé en MD5
			$dao->modifierMdpUser ($nom, $nouveauMdp);
			
			// envoie un mail à l'utilisateur avec son nouveau mot de passe 
			$ok = $dao->envoyerMdp ($nom, $nouveauMdp);
			if ( $ok )
				$msg = "Enregistrement effectué ; vous allez recevoir un mail de confirmation.";
			else
				$msg = "Enregistrement effectué ; l'envoi du mail de confirmation a rencontré un problème.";
		}
		
		// ferme la connexion à MySQL :
		unset($dao);
	}
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
	$elt_commentaire = $doc->createComment('Service web ChangerDeMdp - BTS SIO - Lycée De La Salle - Rennes');
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