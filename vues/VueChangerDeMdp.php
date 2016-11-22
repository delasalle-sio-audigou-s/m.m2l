<?php
	// Projet Réservations M2L - version web mobile
	// fichier : vues/VueCreerUtilisateur.php
	// Rôle : visualiser la demande de création d'un nouvel utilisateur
	// cette vue est appelée par le contôleur controleurs/CtrlCreerUtilisateur.php
	// Création : 15/11/2016 par Florentin gremy
?>
<!doctype html>
<html>
	<head>
		<?php include_once ('vues/head.php'); ?>
		
		<script>
			// associe une fonction à l'événement pageinit
			$(document).bind('pageinit', function() {
				<?php if ($typeMessage != '') { ?>
					// affiche la boîte de dialogue 'affichage_message'
					$.mobile.changePage('#affichage_message', {transition: "<?php echo $transition; ?>"});
				<?php } ?>
			} );
		</script>
	</head>
	
	<body>
		<div data-role="page" id="page_principale">
			<div data-role="header" data-theme="<?php echo $themeNormal; ?>">
				<h4>M2L-GRR</h4>
				<a href="index.php?action=Menu" data-transition="<?php echo $transition; ?>">Retour menu</a>
			</div>
			
			<div data-role="content">
				<h4 style="text-align: center; margin-top: 0px; margin-bottom: 0px;">Changer de mot de passe</h4>
				<form action="index.php?action=ChangerDeMdp" method="post" data-ajax="false">
					<div data-role="fieldcontain" class="ui-hide-label">
						<label for="txtName">Nouveau mot de passe :</label>
						<input type="text" name="txtName" id="txtName" required placeholder="Entrez le nouveau mot de passe" value="<?php echo $name; ?>">
					</div>

					<div data-role="fieldcontain">
						<input type="submit" name="btnChangerMdp" id="btnChangerMdp" value="Changer de mot de passe" data-mini="true">
					</div>
				</form>

				<?php if($debug == true) {
					// en mise au point, on peut afficher certaines variables dans la page
					echo "<p>name = " . $name . "</p>";
				} ?>
				
			</div>
			
			<div data-role="footer" data-position="fixed" data-theme="<?php echo $themeNormal; ?>">
				<h4>Suivi des réservations de salles<br>Maison des ligues de Lorraine (M2L)</h4>
			</div>
		</div>
		
		<?php include_once ('vues/dialog_message.php'); ?>
		
	</body>
</html>