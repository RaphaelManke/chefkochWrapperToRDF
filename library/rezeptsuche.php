<?php
include_once 'konstanten.php';
//require 'utils.php';
/**
 * sucht auf Chefkoch mit hilfe des Suchbegriffs
 * 
 * @param unknown $suchbegriff        	
 * @param number $anzahlErgebnisse        	
 * @return string
 */
function sucheRezepte($suchbegriff, $anzahlErgebnisse = 10) {
	$suchParameter = "Suchbegriff=" . $suchbegriff . "&limit=" . $anzahlErgebnisse;
	$api_such_url = "http://api.chefkoch.de/api/1.1/api-recipe-search.php?";
	$result = file_get_contents ( $api_such_url . $suchParameter );
	return $result;
}
/**
 * sucht nach einem einzelnen Rezept anhand der ID.
 * 
 * @param Integer $id        	
 * @return string
 */
function einzelnesRezept($id) {
	$api_rezept_url = "http://api.chefkoch.de/api/1.0/api-recipe.php";
	$params = "ID=" . $id;
	$url_rezept = $api_rezept_url . "?" . $params;
	$details = file_get_contents ( $url_rezept );
	return $details;
}

/**
 * Sucht auf Rewe nach einem Produkt
 * @param Integer $produkt
 * 
 * return Array $value 
 */
function naehrwertSuche($id) {
	//$query = "https://shop.rewe.de/PD1211326";
	$query = "https://shop.rewe.de/PD" . $id;
	$api_key = "40581aa5770c4331b477ca6b191549da6a6ebe6117061cab22ffe286a3bec687a28dd324679b4b65c0e05e44c47739c45a4e56111fd9762d4e3dc345e60732bfe413ad4f56724681aeb33b4055f1e54a";
	$api = "https://api.import.io/store/connector/d1d51bb1-5caf-4369-aa66-026ef8cfd987/_query?input=webpage/url:" . $query . "&&_apikey=" . $api_key;
	$data = curlData($api);
	(Array) $value = json_decode ( $data, True );
	// schließe den cURL-Handle und gebe die Systemresourcen frei
	return $value;
}

/**
 * Erzeugt einen Graph aus dem JSON Dokument der allgemeinen Rezeptsuche.
 * 
 * @param Array $jsonObject        	
 * @return EasyRdf_Graph
 */
function buildGraphFromJsonSearchResult($jsonObject) {
	$baseURL = 'http://chefkoch.de/rezept/';
	$wrapperURL = HOST."lookup/";
	$graph = new EasyRdf_Graph ();
	$rezeptNamespace = new EasyRdf_Namespace ();
	$rezeptNamespace->set ( 'arecipe', "http://purl.org/amicroformat/arecipe/" );
	$rezeptNamespace->set ( 'rdf', "http://www.w3.org/1999/02/22-rdf-syntax-ns#" );
	//$rezeptNamespace->set ( 'wrapper', "http://manke-hosting.de/wrapper/index.php/lookup/" );
	$rezeptNamespace->set ( 'wrapper', HOST."index.php/lookup/" );
	//$rezeptNamespace->set ( 'wrapper', "http://localhost/wrapper/index.php/lookup/" );
	$rezeptNamespace->set ( 'owl', "http://www.w3.org/2002/07/owl#" );
	foreach ( $jsonObject as $key => $value ) {
		$me = $graph->resource ( $baseURL . $value ['RezeptShowID'], 'arecipe:Recipe' );
		foreach ( $value as $key1 => $value1 ) {
			if ($key1 != "hasVideo") {
				$me->add ( 'rdf:' . $key1, $value1 );
			}
		}
		$me->addResource ( 'owl:sameAs', "wrapper:" . $value ['RezeptShowID'] );
	}
	
	return $graph;
}
/**
 * Erzeugt einen Graph aus dem JSON Dokument der spezifischen Rezeptsuche.
 * 
 * @param Array $jsonObject        	
 * @return EasyRdf_Graph
 */
function buildGraphFromJsonRecipeResult($jsonObject) {
	$baseURL = 'http://chefkoch.de/rezept/';
	//$wrapperURL = "http://chefkoch:8888/index.php/lookup/";
	$graph = new EasyRdf_Graph ();
	$rezeptNamespace = new EasyRdf_Namespace ();
	$rezeptNamespace->set ( 'arecipe', "http://purl.org/amicroformat/arecipe/" );
	$rezeptNamespace->set ( 'rdf', "http://www.w3.org/1999/02/22-rdf-syntax-ns#" );
	$rezeptNamespace->set ( 'wrapper', HOST."index.php/lookup/" );
	$rezeptNamespace->set ( 'owl', "http://www.w3.org/2002/07/owl#" );
	$rezeptNamespace->set('reweSuche', HOST."index.php/reweSuche/");
	//$rezeptNamespace->set('reweSuche', "http://manke-hosting.de/wrapper/index.php/reweSuche/");
	//$rezeptNamespace->set('reweSuche', "http://localhost/wrapper/index.php/reweSuche/");
	$url = $baseURL . $jsonObject ['rezept_show_id'];
	$me = $graph->resource ( $url, 'arecipe:Recipe' );
	foreach ( $jsonObject as $key => $value ) {
		$type = gettype ( $value );
		$namespace = "rdf";
		
		if ($type == "string") {
			$me->set ( $namespace . ':' . $key, $value );
		} elseif ($type == "array" && ! empty ( $value )) {
			switch ($key) {
				case 'rezept_videos' :
					// TODO
					break;
				case 'rezept_in_rezeptsammlung' :
					break;
				case 'rezept_zutaten' :
					foreach ( $value as $rezept_zutaten => $zutat ) {
						$bn = $graph->newBNode ();
						$me->add ( $namespace . ":" . $key, $bn );
						foreach ( $zutat as $attribut => $attributWert ) {
							if ($attribut == "name" && $zutat["id"] != "") {
								$attributWert = $str=str_replace(array('ä','ö','ü','ß','Ä','Ö','Ü'),array('ae','oe','ue','ss','Ae','Oe','Ue'),$attributWert);
								$attributWert = preg_replace('/[^a-zA-Z0-9]+/', '', $attributWert);
								$bn->addResource ('owl:sameAs','reweSuche:'.$attributWert);
								$bn->addLiteral ( $namespace . ":" . $attribut, $attributWert );
							} elseif ($attribut == "menge"){
								$attributWert= str_replace(".", ",", $attributWert);
								$bn->addLiteral ( $namespace . ":" . $attribut, $attributWert );
							}
							else {
								$bn->addLiteral ( $namespace . ":" . $attribut, $attributWert );
							}
						}
						
					}
					break;
				default :
					
					break;
			}
		}
	}
	return $graph;
}
