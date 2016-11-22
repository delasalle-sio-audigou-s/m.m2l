<?php
//Service web du projet Réservations M2L
//Ecrit le 22/11/2016 par Bachelier Valentin

//Ce service web permet à un administrateur authentifié de supprimer un utilisateur
// Le service web doit être appelé avec 3 paramètres : nomAdmin, mdpAdmin, name

// inclusion de la classe Outils
include_once ('../modele/Outils.class.php');
// inclusion des paramètres de l'application
include_once ('../modele/parametres.localhost.php');

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
if ( empty ($_GET ["nomAdmin"]) == true)  $nomAdmin = "";  else   $nomAdmin = $_GET ["nomAdmin"];
if ( empty ($_GET ["mdpAdmin"]) == true)  $mdpAdmin = "";  else   $mdpAdmin = $_GET ["mdpAdmin"];
if ( empty ($_GET ["name"]) == true)  $name = "";  else   $name = $_GET ["name"];
// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nomAdmin == "" && $mdpAdmin == "" && $name == "" )
{	if ( empty ($_POST ["nomAdmin"]) == true)  $nomAdmin = "";  else   $nomAdmin = $_POST ["nomAdmin"];
if ( empty ($_POST ["mdpAdmin"]) == true)  $mdpAdmin = "";  else   $mdpAdmin = $_POST ["mdpAdmin"];
if ( empty ($_POST ["name"]) == true)  $name = "";  else   $name = $_POST ["name"];
}

// Contrôle de la présence des paramètres
if ( $nomAdmin == "" || $mdpAdmin == "" || $name == "" )
{	$msg = "Erreur : données incomplètes.";
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
include_once ('../modele/DAO.class.php');
$dao = new DAO();
if ( $dao->getNiveauUtilisateur($nomAdmin, $mdpAdmin) != "administrateur" )
	$msg = "Erreur : authentification incorrecte.";
	else
	{
		if ( ! $dao->existeUtilisateur($name) )
		{	$msg = "Erreur : nom d'utilisateur inexistant.";
		}
		else
		{	// rechercher si cet utilisateur a passé des réservations à venir
			if ( $dao->aPasseDesReservations($name) )
			{	$msg = "Erreur : cet utilisateur a passé des réservations à venir.";
			}
			else
			{	// recherche l'adresse mail de l'utilisateur (avant de le supprimer)
				$email = $dao->getUtilisateur($name)->getEmail();

				// supprime l'utilisateur dans la bdd
				$ok = $dao->supprimerUtilisateur($name);
				if ( ! $ok )
					$msg = "Erreur : problème lors de la suppression de l'utilisateur.";
					else
					{	// envoie un mail de confirmation de la suppression
						$sujet = "Suppression de votre compte dans le système de réservation de M2L";
						$contenuMail = "L'administrateur du système de réservations de la M2L vient de supprimer votre compte utilisateur.\n";
							
						$ok = Outils::envoyerMail($email, $sujet, $contenuMail, $ADR_MAIL_EMETTEUR);
						if ( ! $ok )
							$msg = "Suppression  effectuée ; l'envoi du mail à l'utilisateur a rencontré un problème.";
							else
								$msg = "Suppression  effectuée ; un mail va être envoyé à l'utilisateur.";
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
$elt_commentaire = $doc->createComment('Service web SupprimerUtilisateur - BTS SIO - Lycée De La Salle - Rennes');
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