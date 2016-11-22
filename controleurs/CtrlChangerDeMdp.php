<?php
// Projet Réservations M2L - version web mobile
// fichier : controleurs/CtrlChangerDeMdp.php
// Rôle : cahnger de mot de passe
// écrit par florentin le 15/11/2016

// on vérifie si le demandeur de cette action est bien authentifié
if ( $_SESSION['niveauUtilisateur'] != 'utilisateur' && $_SESSION['niveauUtilisateur'] != 'administrateur') {
	// si le demandeur n'est pas authentifié, il s'agit d'une tentative d'accès frauduleux
	// dans ce cas, on provoque une redirection vers la page de connexion
	header ("Location: index.php?action=Deconnecter");
}
else {
	if ( ! isset ($_POST ["txtName"])) {
		// si les données n'ont pas été postées, c'est le premier appel du formulaire : affichage de la vue sans message d'erreur
		$name = '';
		$typeMessage = '';			// 2 valeurs possibles : 'information' ou 'avertissement'
		$themeFooter = $themeNormal;
		include_once ('vues/VueChangerDeMdp.php');
	}
	else {
		// récupération des données postées
		if ( empty ($_POST ["txtName"]) == true)  $name = "";  else   $name = $_POST ["txtName"];
		
			// inclusion de la classe Outils pour utiliser les méthodes statiques estUneAdrMailValide et creerMdp
			include_once ('modele/Outils.class.php');
			include_once ('modele/Utilisateur.class.php');
			
			if ($name == '') {
				// si les données sont incorrectes ou incomplètes, réaffichage de la vue de suppression avec un message explicatif
				$message = 'Données incomplètes ou incorrectes !';
				$typeMessage = 'avertissement';
				$themeFooter = $themeProbleme;
				include_once ('vues/VueChangerDeMdp.php');
			}						
			else {
				// connexion du serveur web à la base MySQL
				include_once ('modele/DAO.class.php');
				$dao = new DAO();
				//récupération du nom de l'utilisateur
				$nomUser = ($_SESSION['nom']);
				//modification du mot de passe dans la base de données
				$ok = $dao->modifierMdpUser($nomUser, $name);
					if ( ! $ok ) {
						// si l'enregistrement a échoué, réaffichage de la vue avec un message explicatif
						$message = "Problème lors de l'enregistrement !";
						$typeMessage = 'avertissement';
						$themeFooter = $themeProbleme;
						include_once ('vues/ChangerDeMdp.php');
					}
					else {
						// envoi d'un mail de confirmation de l'enregistrement
						$ok = $dao->envoyerMdp($nomUser, $name);
					if ( ! $ok ) {
						// si l'envoi de mail a échoué, réaffichage de la vue avec un message explicatif
						$message = "Enregistrement effectué.<br>L'envoi du mail à l'utilisateur a rencontré un problème !";
						$typeMessage = 'avertissement';
						$themeFooter = $themeProbleme;
						include_once ('vues/VueChangerDeMdp.php');
					}
					else {
						// tout a fonctionné
						$message = "Modification effectué.<br>Un mail va être envoyé à l'utilisateur !";
						$typeMessage = 'information';
						$themeFooter = $themeNormal;
						include_once ('vues/VueChangerDeMdp.php');
					}
				}
			}
			unset($dao);		// fermeture de la connexion à MySQL
	}
}