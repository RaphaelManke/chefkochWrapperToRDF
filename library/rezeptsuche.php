<?php
/**
 * Hilfsfunktion die zum Auslesen der Parameter verwendet wird.
 */
function after($this, $inthat) {
	if (! is_bool ( strpos ( $inthat, $this ) ))
		return substr ( $inthat, strpos ( $inthat, $this ) + strlen ( $this ) );
}
;
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
		$result = curl_exec ( $ch );
	(Array) $value = json_decode ( curlData($api), True );
	// schließe den cURL-Handle und gebe die Systemresourcen frei
	return $value;
}
/**
 * macht einen GET auf eine URL
 * @param String $url
 * @return String $result
 */
function curlData(String $url) {
	$ch = curl_init ();
	
	// setze die URL und andere Optionen
	curl_setopt ( $ch, CURLOPT_URL, $api );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
	
	// führe die Aktion aus und speichere die Daten
	$result = curl_exec ( $ch );
	// schließe den cURL-Handle und gebe die Systemresourcen frei
	curl_close ( $ch );
	return (String) $result;
}
/**
 * Erzeugt einen Graph aus dem JSON Dokument der allgemeinen Rezeptsuche.
 * 
 * @param Array $jsonObject        	
 * @return EasyRdf_Graph
 */
function buildGraphFromJsonSearchResult($jsonObject) {
	$baseURL = 'http://chefkoch.de/rezept/';
	$wrapperURL = "http://chefkoch:8888/index.php/lookup/";
	$graph = new EasyRdf_Graph ();
	$rezeptNamespace = new EasyRdf_Namespace ();
	$rezeptNamespace->set ( 'arecipe', "http://purl.org/amicroformat/arecipe/" );
	$rezeptNamespace->set ( 'rdf', "http://www.w3.org/1999/02/22-rdf-syntax-ns#" );
	$rezeptNamespace->set ( 'wrapper', "http://manke-hosting.de/wrapper/index.php/lookup/" );
	foreach ( $jsonObject as $key => $value ) {
		$me = $graph->resource ( $baseURL . $value ['RezeptShowID'], 'arecipe:Recipe' );
		foreach ( $value as $key1 => $value1 ) {
			if ($key1 != "hasVideo") {
				$me->addLiteral ( 'rdf:' . $key1, $value1 );
			}
		}
		$me->addResource ( 'rdf:sameAs', "wrapper:" . $value ['RezeptShowID'] );
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
	$wrapperURL = "http://chefkoch:8888/index.php/lookup/";
	$graph = new EasyRdf_Graph ();
	$rezeptNamespace = new EasyRdf_Namespace ();
	$rezeptNamespace->set ( 'arecipe', "http://purl.org/amicroformat/arecipe/" );
	$rezeptNamespace->set ( 'rdf', "http://www.w3.org/1999/02/22-rdf-syntax-ns#" );
	$rezeptNamespace->set ( 'wrapper', "http://chefkoch:8888/lookup/" );
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
							$bn->addLiteral ( $namespace . ":" . $attribut, $attributWert );
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
