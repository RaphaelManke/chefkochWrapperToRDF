<?php
/*
 * definiert den Header.
 */
header('Content-Type: text/turtle; charset=UTF-8');
/*
 * lädt die benötigten Libarys.
 */
require 'library/EasyRdf.php';
include 'library/rezeptsuche.php';
/*
 * liest die Anfrage URL und damit die Parameter aus.
 */
$url = $_SERVER["REQUEST_URI"];
$urlRel = after("index.php", $url);
$url = explode("/", $urlRel);
$method = $url[1];
$suchbegriff = $url[2];
/*
 * zum lokalen Debuggen
 */
//$method = "lookup";
//$suchbegriff = "390421126430613"; 
//$suchbegriff = "kuchen"; 
/*
 * Anfangs Weiche um zwischen allgemeiner Suche und spezieller Suche zu unterscheiden.
 */
switch ($method) {
	case 'explore' :
		generallSearch($suchbegriff);
		break;

	case 'lookup' :
		specificSearch($suchbegriff);
		break;

	default :
		header('HTTP/1.0 404 Not Found');
		echo "Error 404 Not Found \n";
		echo "The page that you have requested could not be found. Please Check your Parameters.";
		//exit ;
		break;
}
/**
 * startet eine Suche mit einem Suchbegriff.
 * @param String $suchbegriff
 */
function generallSearch($suchbegriff) {
	if ($suchbegriff == "") {
		echo "Error! Der Suchbegriff ist leer.";
		//var_dump($url);
	} else {
		echo "# Suchergebnis für die Suche nach dem Suchbegriff " . $suchbegriff . "\n";
		$search = json_decode(sucheRezepte($suchbegriff, 20), JSON_UNESCAPED_UNICODE);
		$recipes = $search["result"];
		$recipeInN3 = buildGraphFromJsonSearchResult($recipes);
		echo $recipeInN3 -> serialise("turtle");
	}
}
/**
 * Startet die Suche nach genau einem Rezept, anhand einer RezeptShowID.
 * @param Integer $ID
 */
function specificSearch($ID) {
	if ($ID == "") {
		echo "Error! Der Suchbegriff ist leer.";
		//var_dump($url);
	} else {
		echo "# Suchergebnis für die Suche nach dem Rezept mit der ID " . $ID . "\n";
		$search = json_decode(einzelnesRezept($ID), JSON_UNESCAPED_UNICODE);
		$recipes = $search["result"][0];
		$recipeInN3 = buildGraphFromJsonRecipeResult($recipes);
		echo $recipeInN3 -> serialise("turtle");
		//echo $recipeInN3;
	}}

