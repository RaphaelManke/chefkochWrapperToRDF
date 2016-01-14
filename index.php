<?php
/*
 * definiert den Header.
 */
header ( 'Content-Type: text/turtle; charset=UTF-8' );
/*
 * lädt die benötigten Libarys.
 */
require 'library/EasyRdf.php';
require 'library/konstanten.php';
include 'library/rezeptsuche.php';
include 'library/getReweProduct.php';
include 'library/getReweSearch.php';
include 'library/fddbSuche.php';
include 'library/codecheck.php';

include_once 'library/utils.php';
/*
 * liest die Anfrage URL und damit die Parameter aus.
 */
$url = $_SERVER ["REQUEST_URI"];
//$url = "index.php/reweProdukt/https://shop.rewe.de/kuehlprodukte/margarine-butter-fett/rama-classic-250g/PD2165358";
$urlRel = after ( "index.php", $url );
$url = explode ( "/", $urlRel );
$suchbegriff="";
foreach ($url as $key => $value){
	if ($key == 1){
		$method = $value;
	}
	elseif ($key > 1){
		$suchbegriff .= $value."/";
	}
}
//$suchbegriff = "https://shop.rewe.de/kuehlprodukte/margarine-butter-fett/rama-classic-250g/PD2165358";
//$suchbegriff = $url [2];
/*
 * zum lokalen Debuggen
 */
//$method = "reweProdukt";
//$method = "explore";
//$method = "lookup";
//$method = "reweSuche";
//$method="reweProduktFddb";
//$method = "codecheckEan";
//$suchbegriff = "4388844009974";
//$suchbegriff = "4388844009943";
// $suchbegriff = "390421126430613";
 //$suchbegriff = "kuchen";
//$suchbegriff = "1211326";
//$suchbegriff = "7890168";
//$suchbegriff = "197892";
//$suchbegriff ="Mandeln";
/*
 * Anfangs Weiche um zwischen allgemeiner Suche und spezieller Suche zu unterscheiden.
 */
switch ($method) {
	case 'explore' :
		generallSearch ( $suchbegriff );
		break;
	
	case 'lookup' :
		$suchbegriff = preg_replace('/[^0-9]+/', '',  $suchbegriff);
		specificSearch ( $suchbegriff );
		break;
	case 'naehrwerte' :
		$result = naehrwertSuche($suchbegriff);
		print_r($result["results"]);
		break;
	case 'reweSuche' :
		//$result = getReweSearch($suchbegriff);
		$result = getReweSearchByApi($suchbegriff);
		print_r($result["results"]);
		break;
	case 'reweProdukt' :
		//TODO Cleanup
		$result = getReweDataByApi($suchbegriff,"turtle");
		//$result2 = getReweData($suchbegriff,"turtle");
		
		
		print_r($result);
		//print_r($result2);
		break;
	case 'reweProduktFddb' :
		//TODO Cleanup
		$result = fddbSuche($suchbegriff);
		//$result2 = getReweData($suchbegriff,"turtle");
	
	
		print_r($result);
		//print_r($result2);
		break;
	case 'codecheckEan' :
		//TODO Cleanup
		//echo $suchbegriff;
		$result = codecheckProductByEanGeneral($suchbegriff);
		$graph = new EasyRdf_Graph();
		//$result2 = getReweData($suchbegriff,"turtle");
		buildTree($graph, null ,$result["result"]);
		echo $graph -> serialise("turtle");
		//print_r($result);
		//print_r($result2);
		break;
		
	default :
		header ( 'HTTP/1.0 404 Not Found' );
		echo "Error 404 Not Found \n";
		echo "The page that you have requested could not be found. Please Check your Parameters.";
		// exit ;
		break;
}
/**
 * startet eine Suche mit einem Suchbegriff.
 * 
 * @param String $suchbegriff        	
 */
function generallSearch($suchbegriff) {
	if ($suchbegriff == "") {
		echo "Error! Der Suchbegriff ist leer.";
		// var_dump($url);
	} else {
		echo "# Suchergebnis für die Suche nach dem Suchbegriff " . $suchbegriff . "\n";
		$search = json_decode ( sucheRezepte ( $suchbegriff, 2 ), JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);
		$recipes = $search ["result"];
		$recipeInN3 = buildGraphFromJsonSearchResult ( $recipes );
		echo $recipeInN3->serialise ( "turtle" );
	}
}
/**
 * Startet die Suche nach genau einem Rezept, anhand einer RezeptShowID.
 * 
 * @param Integer $ID        	
 */
function specificSearch($ID) {
	if ($ID == "") {
		echo "Error! Der Suchbegriff ist leer.";
		// var_dump($url);
	} else {
		echo "# Suchergebnis für die Suche nach dem Rezept mit der ID " . $ID . "\n";
		$search = json_decode ( einzelnesRezept ( $ID ), JSON_UNESCAPED_UNICODE );
		$recipes = $search ["result"] [0];
		$recipeInN3 = buildGraphFromJsonRecipeResult ( $recipes );
		echo $recipeInN3->serialise ( "turtle" );
		// echo $recipeInN3;
	}
}

