<?php
// Projet Réservations M2L - version web mobile
// fichier : controleurs/CtrlSupprimerUtilisateur.php
// Rôle : traiter la demande de suppression d'un utilisateur
// Création : 15/11/2016 par Bachelier Valentin
// on vérifie si le demandeur de cette action a le niveau administrateur
if ($_SESSION['niveauUtilisateur'] != 'administrateur') {
	// si le demandeur n'a pas le niveau administrateur, il s'agit d'une tentative d'accès frauduleux
	// dans ce cas, on provoque une redirection vers la page de connexion
	header ("Location: index.php?action=Deconnecter");
}
else {
	if ( ! isset ($_POST ["txtName"]) ) {
		// si les données n'ont pas été postées, c'est le premier appel du formulaire : affichage de la vue sans message d'erreur
		$name = '';
		$message = '';
		$typeMessage = '';			// 2 valeurs possibles : 'information' ou 'avertissement'
		$themeFooter = $themeNormal;
		include_once ('vues/VueSupprimerUtilisateur.php');
	}
	else {
		// récupération des données postées
		if ( empty ($_POST ["txtName"]) == true)  $name = "";  else   $name = $_POST ["txtName"];
		
		if ($name == '') {
			// si les données sont incorrectes ou incomplètes, réaffichage de la vue avec un message explicatif
			$message = 'Données incomplètes ou incorrectes !';
			$typeMessage = 'avertissement';
			$themeFooter = $themeProbleme;
			include_once ('vues/VueSupprimerUtilisateur.php');
		}
		else {
			// connexion du serveur web à la base MySQL
			include_once ('modele/DAO.class.php');
			$dao = new DAO();
				
			if ( ! $dao->existeUtilisateur($name) ) {
				// si le nom n'existe pas, réaffichage de la vue
				$message = "Nom d'utilisateur inexistant !";
				$typeMessage = 'avertissement';
				$themeFooter = $themeProbleme;
				include_once ('vues/VueSupprimerUtilisateur.php');
			}
			else {
				// si cet utilisateur a passé des réservations à venir, sa suppression est refusée
				if ( $dao->aPasseDesReservations($name) ) {
					$message = "Cet utilisateur a passé des réservations à venir !";
					$typeMessage = 'avertissement';
					$themeFooter = $themeProbleme;
					include_once ('vues/VueSupprimerUtilisateur.php');		
				}
				else {
					// recherche de l'adresse mail de l'utilisateur (avant de le supprimer)
					$adrMail = $dao->getUtilisateur($name)->getEmail();
				
					// suppression de l'utilisateur dans la BDD
					$ok = $dao->supprimerUtilisateur($name);
					if ( ! $ok ) {
						// si la suppression a échoué, réaffichage de la vue avec un message explicatif
						$message = "Problème lors de la suppression de l'utilisateur !";
						$typeMessage = 'avertissement';
						$themeFooter = $themeProbleme;
						include_once ('vues/VueSupprimerUtilisateur.php');
					}
					else {
						// envoi d'un mail de confirmation de la suppression
						$sujet = "Suppression de votre compte dans le système de réservation de M2L";
						$contenuMail = "Bonjour " . $name . "\n\nL'administrateur du système de réservations de la M2L vient de supprimer votre compte utilisateur.\n";
							
						$ok = Outils::envoyerMail($adrMail, $sujet, $contenuMail, $ADR_MAIL_EMETTEUR);
						if ( ! $ok ) {
							// si l'envoi de mail a échoué, réaffichage de la vue avec un message explicatif
							$message = "Suppression effectuée.<br>L'envoi du mail à l'utilisateur a rencontré un problème !";
							$typeMessage = 'avertissement';
							$themeFooter = $themeProbleme;
							include_once ('vues/VueSupprimerUtilisateur.php');
						}
						else {
							// tout a fonctionné
							$message = "Suppression effectuée.<br>Un mail va être envoyé à l'utilisateur !";
							$typeMessage = 'information';
							$themeFooter = $themeNormal;
							include_once ('vues/VueSupprimerUtilisateur.php');
						}
					}
				}
			}
			unset($dao);		// fermeture de la connexion à MySQL
		}
	}
}