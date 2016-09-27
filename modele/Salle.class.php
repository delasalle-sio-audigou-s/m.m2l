<?php
// Projet Réservations M2L - version web mobile
// fichier : modele/Salle.class.php
// Rôle : la classe salle représente les salles
// Création : 27/09/2016 par F GREMY
// Mise à jour : 27/09/2016 par F GREMY

class Salle
{
	// ------------------------------------------------------------------------------------------------------
	// ---------------------------------- Membres privés de la classe ---------------------------------------
	// ------------------------------------------------------------------------------------------------------
	
	// Rappel : le temps UNIX mesure le nombre de secondes écoulées depuis le 1/1/1970
	// les types des champs timestamp, start_time et end_time découlent des types choisis pour la BDD
	private $id;
	private $room_name;
	private $capacity;
	private $area_name;
	
	public function Salle($unId, $unRoomName, $unCapacity, $unAreaName){
		$this->id = $unId;
		$this->room_name = $unRoomName;
		$this->capacity = $unCapacity;
		$this->area_name = $unAreaName;
	}
	// ------------------------------------------------------------------------------------------------------
	// ---------------------------------------- Getters et Setters ------------------------------------------
	// ------------------------------------------------------------------------------------------------------
	
	public function getId()	{return $this->id;}
	public function setId($unId) {$this->id = $unId;}
	
	public function getRoom_Name() {return $this->room_name;}
	public function setRoom_Name($unRoomName) {$this->room_name = $unRoomName;}
	
	public function getCapacity() {return $this->capacity;}
	public function setCapacity($unCapacity) {$this->capacity = $unCapacity;}
	
	public function getAreaName() {return $this->area_name;}
	public function setAreaName($unAreaName) {$this->area_name = $unAreaName;}
	
	// ------------------------------------------------------------------------------------------------------
	// ---------------------------------------- Méthodes d'instances ----------------------------------------
	// ------------------------------------------------------------------------------------------------------
	
	public function toString() {
		$msg = "Salle : <br>";
		$msg .= "id : " . $this->id . "<br>";
		$msg .= "room_name : " . $this->room_name . "<br>";
		$msg .= "capacity : " . $this->capacity . "<br>";
		$msg .= "area_name : " . $this->area_name . "<br>";
		return $msg;
	}
	
} // fin de la classe Reservation
	
// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!
