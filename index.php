<?php
header('Content-Type: text/turtle; charset=UTF-8');
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
require 'library/EasyRdf.php';
include 'library/rezeptsuche.php';
//$header = apache_request_headers ();
$url = $_SERVER["REQUEST_URI"];
$urlRel = after("index.php", $url);
$url = explode("/", $urlRel);
$method = $url[1];
$suchbegriff = $url[2];
switch ($method) {
	case 'explore' :
		generallSearch($suchbegriff);
		break;

	case 'lookup' :
		specificSearch();
		break;

	default :
		header('HTTP/1.0 404 Not Found');
		echo "Error 404 Not Found \n";
		echo "The page that you have requested could not be found. Please Check your Parameters.";
		//exit ;
		break;
}
function generallSearch($suchbegriff) {
	if ($suchbegriff == "123") {
		echo "Error! Der Suchbegriff ist leer.";
		//var_dump($url);
	} else {
		echo "# Suchergebnis für die Suche nach dem Suchbegriff " . $suchbegriff . "\n";
		$search = json_decode(sucheRezepte($suchbegriff, 20), JSON_UNESCAPED_UNICODE);
		$recipes = $search["result"];
		$recipeInN3 = buildGraphFromJson($recipes, "n3");
		echo $recipeInN3 -> serialise("turtle");
		//echo $recipeInN3;
	}
}

function specificSearch() {
	echo '<http://chefkoch.de/rezept/bla> <http://www.w3.org/1999/02/22-rdf-syntax-ns#bild_group_id> "399375" .';
}
?>
