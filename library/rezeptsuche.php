<?php
/**
 * Hilfsfunktion die zum Auslesen der Parameter verwendet wird.
 */
function after ($this, $inthat)
    {
        if (!is_bool(strpos($inthat, $this)))
        return substr($inthat, strpos($inthat,$this)+strlen($this));
    };
/**
 * sucht auf Chefkoch mit hilfe des Suchbegriffs
 * @param unknown $suchbegriff
 * @param number $anzahlErgebnisse
 * @return string
 */
function sucheRezepte($suchbegriff, $anzahlErgebnisse = 10) {
	$suchParameter = "Suchbegriff=" . $suchbegriff . "&limit=" . $anzahlErgebnisse;
	$api_such_url = "http://api.chefkoch.de/api/1.1/api-recipe-search.php?";
	$result = file_get_contents($api_such_url.$suchParameter);
	return $result;
}
/**
 * sucht nach einem einzelnen Rezept anhand der ID.
 * @param Integer $id
 * @return string
 */
function einzelnesRezept($id) {
	$api_rezept_url = "http://api.chefkoch.de/api/1.0/api-recipe.php";
	$params = "ID=" . $id;
	$url_rezept = $api_rezept_url . "?" . $params;
	$details = file_get_contents($url_rezept);
	return $details	;
}

/**
 * NOCH NICHT IMPLEMENTIERT.
 * @param unknown $produkt
 * @param unknown $plz
 * @return string
 */ 
//TODO
function produktsuche($produkt, $plz) {
	$api = "https://www.simplora.de/articles?";
	//search_term=milch&filters%5Bshops%5D%5B%5D=rewe-840093&from=60&size=60&zipcode=76131";
	this . $produkt = milch;
	$filters = null;
	this . $plz = 76131;
	$search_url = $api . "search_term=" . $produkt . "&filters%5Bshops%5D%5B%5D=rewe-840093&from=60&size=60&zipcode=" . $plz;

	$opts = array('http' => array('method' => "GET", 'header' => "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:42.0) Gecko/20100101 Firefox/42.0" . "Accept: application/json, text/javascript, */*; q=0.01" . "Accept-Language: de,en-US;q=0.7,en;q=0.3" . "Accept-Encoding: gzip, deflate" . "X-Requested-With: XMLHttpRequest"));

	$context = stream_context_create($opts);
	$search_url = "https://www.simplora.de/articles?search_term=milch&filters%5Bshops%5D%5B%5D=rewe-840093&from=60&size=60&zipcode=76131";
	// Öffnen der Datei mit den oben definierten HTTP-Headern
	$file = file_get_contents($search_url, false, $context);
	return $file;

}
/**
 * Erzeugt einen Graph aus dem JSON Dokument der allgemeinen Rezeptsuche.
 * @param Array $jsonObject
 * @return EasyRdf_Graph
 */
function buildGraphFromJsonSearchResult($jsonObject) {
	$baseURL = 'http://chefkoch.de/rezept/';
	$wrapperURL = "http://chefkoch:8888/index.php/lookup/";
	$graph = new EasyRdf_Graph();
	$rezeptNamespace = new EasyRdf_Namespace();
	$rezeptNamespace -> set('arecipe', "http://purl.org/amicroformat/arecipe/");
	$rezeptNamespace -> set('rdf', "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
	$rezeptNamespace -> set('wrapper', "http://manke-hosting.de/wrapper/index.php/lookup/");
		foreach ($jsonObject as $key => $value) {
		$me = $graph -> resource($baseURL . $value['RezeptShowID'], 'arecipe:Recipe');
		foreach ($value as $key1 => $value1) {
			if ($key1 != "hasVideo"){
			$me -> addLiteral('rdf:' . $key1, $value1);		
			}
		}
		$me -> addResource('rdf:sameAs', "wrapper:".$value['RezeptShowID']);
	}

	return $graph;
}
/**
 * Erzeugt einen Graph aus dem JSON Dokument der spezifischen Rezeptsuche.
 * @param Array $jsonObject
 * @return EasyRdf_Graph
 */
function buildGraphFromJsonRecipeResult ($jsonObject){
	$baseURL = 'http://chefkoch.de/rezept/';
	$wrapperURL = "http://chefkoch:8888/index.php/lookup/";
	$graph = new EasyRdf_Graph();
	$rezeptNamespace = new EasyRdf_Namespace();
	$rezeptNamespace -> set('arecipe', "http://purl.org/amicroformat/arecipe/");
	$rezeptNamespace -> set('rdf', "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
	$rezeptNamespace -> set('wrapper', "http://chefkoch:8888/lookup/");
	$url = $baseURL . $jsonObject['rezept_show_id'];
	$me = $graph -> resource($url, 'arecipe:Recipe');
	foreach ($jsonObject as $key => $value) {
		$type = gettype($value);
		$namespace = "rdf";
		if ($type == "string") {
			$me -> set($namespace.':' . $key, $value);
		}
		elseif ($type == "array" && !empty($value)) {
			switch ($key) {
				case 'rezept_videos':
					//TODO
					break;
				case 'rezept_in_rezeptsammlung':
					break;
				case 'rezept_zutaten':
					foreach ($value as $rezept_zutaten => $zutat) {
						$bn = $graph -> newBNode();
						$me -> add($namespace . ":" . $key, $bn);
						foreach ($zutat as $attribut => $attributWert) {
							$bn -> addLiteral($namespace . ":" . $attribut, $attributWert);
						}
					}
					break;
				default:

					break;
			}
		}
	}
	return $graph;
}
