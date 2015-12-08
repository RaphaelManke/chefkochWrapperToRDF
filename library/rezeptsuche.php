<?php
function after ($this, $inthat)
    {
        if (!is_bool(strpos($inthat, $this)))
        return substr($inthat, strpos($inthat,$this)+strlen($this));
    };
function sucheRezepte($suchbegriff, $anzahlErgebnisse = 10) {
	$suchParameter = "Suchbegriff=" . $suchbegriff . "&limit=" . $anzahlErgebnisse;
	$api_such_url = "http://api.chefkoch.de/api/1.1/api-recipe-search.php?";
	$result = file_get_contents($api_such_url.$suchParameter);
	return $result;
}

function einzelnesRezept($id) {
	$api_rezept_url = "http://api.chefkoch.de/api/1.0/api-recipe.php";
	$params = "ID=" . $id;
	$url_rezept = $api_rezept_url . "?" . $params;
	$details = file_get_contents($url_rezept);
	//$rezept_beschribung = $details["result"][0]["rezept_zubereitung"];
	//$rezept_bild = $details["result"][0]["rezept_bilder"][0]["big"]["file"];
	//$result = array('beschreibung' => $rezept_beschribung, 'bild' => $rezept_bild);

	return $details	;
}


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
	//$result = file_get_contents($search_url);
	return $file;

}

function buildGraphFromJsonSearchResult($jsonObject) {
	$baseURL = 'http://chefkoch.de/rezept/';
	$wrapperURL = "http://chefkoch:8888/index.php/lookup/";
	$graph = new EasyRdf_Graph();
	$rezeptNamespace = new EasyRdf_Namespace();
	$rezeptNamespace -> set('arecipe', "http://purl.org/amicroformat/arecipe/");
	$rezeptNamespace -> set('rdf', "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
	$rezeptNamespace -> set('wrapper', "http://chefkoch:8888/lookup/");
		foreach ($jsonObject as $key => $value) {
		$me = $graph -> resource($baseURL . $value['RezeptShowID'], 'arecipe:Recipe');
		foreach ($value as $key1 => $value1) {
			if ($key1 != "hasVideo"){
			$me -> addLiteral('rdf:' . $key1, $value1);		
			}
		}
		$me -> set('rdf:sameAs', $wrapperURL.$value['RezeptShowID']);
	}

	//$parser = new EasyRdf_Parser_Rdfa ();
	//$parser -> parse($graph, $data, 'turtle', $baseURL);
	
	//return $data;
	return $graph;
}
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
				case 'rezept_zutaten_is_basic':

					break;
				case 'rezept_tags':

					break;
				case 'rezept_bilder':

					break;
				case 'rezept_votes':

					break;
				case 'rezept_statistik':

					break;
						
				default:

					break;
			}
		}
	}
		return $graph;
}
