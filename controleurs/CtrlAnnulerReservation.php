<?php
// Projet Réservations M2L - version web mobile
// fichier : controleurs/CtrlCreerUtilisateur.php
// Rôle : traiter la demande de création d'un nouvel utilisateur
// Mis à jour le 15/11/2016 par florentin gremy

// connexion du serveur web à la base MySQL
include_once ('modele/DAO.class.php');
$dao = new DAO();
	
if ( ! isset ($_POST ["btnAnnulerReservation"]) == true) {
	// si les données n'ont pas été postées, c'est le premier appel du formulaire : affichage de la vue sans message d'erreur
	$idReservation = '';
	include_once ('vues/VueAnnulerReservation.php');
}
else
{
	$idReservation = $_POST ["txtReservation"];
	$nomUtilisateur = $_SESSION['nom'];
	
	
	// On teste si la réservation existe
	if (!$dao->existeReservation($idReservation)){
		$message = "Numéro de réservation inexistant !";
		$typeMessage = 'avertissement';
		$themeFooter = $themeProbleme;
		include_once ('vues/VueAnnulerReservation.php');	
	}
	else {
		
		$laReservation = $dao->getReservation($idReservation);
		$laDateReservation = $laReservation->getEnd_time();		
		
		if ($laDateReservation <= time()){
			$message = "Cette réservation est déjà passée !";
			$typeMessage = 'avertissement';
			$themeFooter = $themeProbleme;
			include_once ('vues/VueAnnulerReservation.php');
		}
		else{
			// On teste si l'utilisateur est le créateur de la réservation
			if ( !$dao->estLeCreateur($nomUtilisateur,$idReservation)){
				$message = "Vous n'êtes pas l'auteur de cette réservation !";
				$typeMessage = 'avertissement';
				$themeFooter = $themeProbleme;
				include_once ('vues/VueAnnulerReservation.php');
			}
		
			else {
				// Si la réservation existe et a été faite par l'utilisateur elle est annulée
				$ok = $dao->annulerReservation($idReservation);
				
				if ($ok) {      /////POUR ENVOIE DU MAIL///////
					//Récupere les informations de l'utilisateur
					$utilisateur = $dao->getUtilisateur($nomUtilisateur);
					
					// inclusion de la classe Outils pour utiliser la méthode envoyer mail
					include_once ('modele/Outils.class.php');
					
					// envoi d'un mail de confirmation de l'enregistrement
					$sujet = "Annulation de votre Réservation dans le système de réservation de M2L";
					$contenuMail = "L'administrateur du système de réservations de la M2L vient d'annuler la réservation: " . $idReservation . "\n\n";
					$envoi = Outils::envoyerMail($utilisateur->getEmail(), $sujet, $contenuMail, $ADR_MAIL_EMETTEUR);
					
					if ( ! $envoi ) {
						// si l'envoi de mail a échoué, réaffichage de la vue avec un message explicatif
						$message = "Enregistrement effectué.<br>L'envoi du mail à l'utilisateur a rencontré un problème !";
						$typeMessage = 'avertissement';
						$themeFooter = $themeProbleme;
						include_once ('vues/VueAnnulerReservation.php');
					}
					else {
						// tout a fonctionné
						$message = "Enregistrement effectué.<br>Un mail va être envoyé à l'utilisateur !";
						$typeMessage = 'information';
						$themeFooter = $themeNormal;
						include_once ('vues/VueAnnulerReservation.php');
					}
					
				}
				
			}
		}		
	}
}
