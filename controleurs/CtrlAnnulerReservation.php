<?php
// Projet Réservations M2L - version web mobile
// fichier : controleurs/CtrlCreerUtilisateur.php
// Rôle : traiter la demande de création d'un nouvel utilisateur
// Création : 21/10/2015 par JM CARTRON
// Mise à jour : 2/6/2016 par JM CARTRON

// on vérifie si le demandeur de cette action a le niveau utilisateur
if ($_SESSION['niveauUtilisateur'] != 'utilisateur' || 'administrateur') {
	// si l'utilisateur n'a pas le niveau utilisateur, il s'agit d'une tentative d'accès frauduleux
	// dans ce cas, on provoque une redirection vers la page de connexion
	header ("Location: index.php?action=Deconnecter");
}
	else {
		// récupération des données postées
		if ( empty ($_POST ["txtNumReserv"]) == true)  $numReserv = "";  else   $numReserv = $_POST ["txtNumReserv"];
		
		
		if ($numReserv == '') {
			// si les données sont incorrectes ou incomplètes, réaffichage de la vue de suppression avec un message explicatif
			$message = 'Données incomplètes ou incorrectes !';
			$typeMessage = 'avertissement';
			$themeFooter = $themeProbleme;
			include_once ('vues/VueAnnulerReservation.php');
		}
		else {
			// connexion du serveur web à la base MySQL
			include_once ('modele/DAO.class.php');
			$dao = new DAO();
				
			if ( $dao->existeReservation($idReservation) && $dao->getLesReservations($nomUser) ) {
				// si la réservation existe, et que l'utilisateur l'a bien créer
				// envoi d'un mail de confirmation de l'enregistrement
				$sujet = "annulation de la réservation";
				$contenuMail = "L'administrateur du système de réservations de la M2L vient d'annuler votre réservation.\n\n";
				$contenuMail .= "Votre réservation annulé était : " . $numReserv . "\n";
				$ok = Outils::envoyerMail($adrMail, $sujet, $contenuMail, $ADR_MAIL_EMETTEUR);
				if ( ! $ok ) {
					// si l'envoi de mail a échoué, réaffichage de la vue avec un message explicatif
					$message = "Enregistrement effectué.<br>L'envoi du mail à l'utilisateur a rencontré un problème !";
					$typeMessage = 'avertissement';
					$themeFooter = $themeProbleme;
					include_once ('vues/VueCreerUtilisateur.php');
				}
				else {
					// tout a fonctionné
					$message = "Enregistrement effectué.<br>Un mail va être envoyé à l'utilisateur !";
					$typeMessage = 'information';
					$themeFooter = $themeNormal;
					include_once ('vues/VueCreerUtilisateur.php');
				}
				
			}
			else {
				
					// si la réservation n'éxiste pas				
					$message = "Cette réservation n'éxiste pas !";
					$typeMessage = 'avertissement';
					$themeFooter = $themeProbleme;
					include_once ('vues/VueAnnulerReservation.php');
				}
								
			}
			unset($dao);		// fermeture de la connexion à MySQL
		}

