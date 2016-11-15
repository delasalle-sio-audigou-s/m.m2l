<?php
// Projet Réservations M2L - version web mobile
// fichier : controleurs/CtrlCreerUtilisateur.php
// Rôle : traiter la demande de création d'un nouvel utilisateur
// Mis à jour le 15/11/2016 par florentin gremy

// connexion du serveur web à la base MySQL
include_once ('modele/DAO.class.php');
$dao = new DAO();
	
if ( ! isset ($_POST ["btnConfirmerReservation"]) == true) {
	// si les données n'ont pas été postées, c'est le premier appel du formulaire : affichage de la vue sans message d'erreur
	$idReservation = '';
	include_once ('vues/VueConfirmerReservation.php');
}
else
{
	$idReservation = $_POST ["txtReservation"];
	$nomUtilisateur = $_SESSION['nom'];
	
	
	// On teste si la réservation existe
	if (!$dao->existeReservation($idReservation)){
		$message = "Numéro de réservation inexistant !";
		$typeMessage = 'avertissement';
		$themeFooter = $themeNormal;
		include_once ('vues/VueConfirmerReservation.php');	
	}
	else {
		
		$laReservation = $dao->getReservation($idReservation);
		$laDateReservation = $laReservation->getEnd_time();		
		
		if ($laDateReservation <= time()){
			$message = "Cette réservation est déjà passée !";
			$typeMessage = 'avertissement';
			$themeFooter = $themeNormal;
			include_once ('vues/VueConfirmerReservation.php');
		}
		else{
			// On teste si l'utilisateur est le créateur de la réservation
			if ( !$dao->estLeCreateur($nomUtilisateur,$idReservation)){
				$message = "Vous n'êtes pas l'auteur de cette réservation !";
				$typeMessage = 'avertissement';
				$themeFooter = $themeNormal;
				include_once ('vues/VueConfirmerReservation.php');
			}
		
			else {
				// Si la réservation existe et a été faite par l'utilisateur elle est confirmer
				$ok = $dao->confirmerReservation($idReservation);
				
				if ($ok) {
					$message = 'Réservation Confirmer.';
					$typeMessage = 'information';
					$themeFooter = $themeNormal;
					include_once ('vues/VueAnnulerReservation.php');
				}
			}
		}		
	}
}
