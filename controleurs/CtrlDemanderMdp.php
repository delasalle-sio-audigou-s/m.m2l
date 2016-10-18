<?php
// Projet Réservations M2L - version web mobile
// Fonction du contrôleur CtrlDemanderMdp.php : traiter la demande d'envoi d'un nouveau mot de passe
// Ecrit le 18/10/2016 par Bachelier

if ( ! isset ($_POST ["nom"]) == true) {
	// si les données n'ont pas été postées, c'est le premier appel du formulaire : affichage de la vue sans message d'erreur
	$nom = '';
	$msgFooter = 'Mot de passe oublié';
	$themeFooter = $themeNormal;
	include_once ('vues/VueDemanderMdp.php');
}
else {
	// récupération des données postées
	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
			
	if ($nom == '') {
		// si les données sont incomplètes, réaffichage de la vue avec un message explicatif
		$msgFooter = 'Données incomplètes !';
		$themeFooter = $themeProbleme;
		include_once ('vues/VueDemanderMdp.php');
	}
	else {
		// connexion du serveur web à la base MySQL
		include_once ('modele/DAO.class.php');
		$dao = new DAO();
		
		// test de l'existence de l'utilisateur
		// la méthode existeUtilisateur de la classe DAO retourne true si $nom existe, false s'il n'existe pas
		if ( ! $dao->existeUtilisateur($nom) )  {
			// si le nom n'existe pas, retour à la vue
			$msgFooter = "Nom d'utilisateur inexistant !";
			$themeFooter = $themeProbleme;
			include_once ('vues/VueDemanderMdp.php');
		}
		else {
			// génération d'un nouveau mot de passe
			$nouveauMdp = Outils::creerMdp();
			// enregistre le nouveau mot de passe de l'utilisateur dans la bdd après l'avoir codé en MD5
			$dao->modifierMdpUser($nom, $nouveauMdp);
		
			// envoi d'un mail à l'utilisateur avec son nouveau mot de passe 
			$ok = $dao->envoyerMdp($nom, $nouveauMdp);
			if ($ok) {
				$themeFooter = $themeNormal;
				$msgFooter = 'Vous allez recevoir un mail<br>avec votre nouveau mot de passe.';
				include_once ('vues/VueDemanderMdp.php');
			}
			else {
				$themeFooter = $themeProbleme;
				$msgFooter = "Echec lors de l'envoi du mail !";
				include_once ('vues/VueDemanderMdp.php');
			}
		}
		unset($dao);		// fermeture de la connexion à MySQL
	}
}
