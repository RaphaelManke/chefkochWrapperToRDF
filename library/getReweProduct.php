<?php 
include 'simple_html_dom.php';
include_once 'utils.php';
include_once 'EasyRdf.php';
include_once 'konstanten.php';
function getHtml ($base) {
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_URL, $base);
	curl_setopt($curl, CURLOPT_REFERER, $base);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	$str = curl_exec($curl);
	curl_close($curl);
	
	// Create a DOM object
	$html = new simple_html_dom();
	// Load HTML from a string
	return $html->load($str);
}

function getReweData($idOrLink, $format){
	$id = preg_replace('/[^0-9]+/', '',  after_last("/PD", $idOrLink));
	if ($id == ""){
		$id = preg_replace('/[^0-9]+/', '',  $idOrLink);
	}
	$base = "https://shop.rewe.de/PD".$id;
	$html = getHtml($base);
	$values = [];
	$values["type"] = "product";
	foreach ($html -> find('section[class=product-container product-detail]') as $a){
		foreach($a->find('#productTitle') as $e) {
			$values['productTitle'] = $e->innertext;
		}
		foreach($a->find('comment') as $e){
			$values["gtin"] = preg_replace('/[^0-9]+/', '',  $e->innertext);
		}
		foreach($a->find('link') as $e){
			$values["link"] = preg_replace('/amp-/', '',  $e->href); 
	
		}
		foreach($a->find('ul[class=article-image] img') as $e){
			$values["image"] = $e->src;
	
		}
		foreach($a->find('#euros') as $e){
			$values["price"] = $e->plaintext;
	
		}
		foreach ($a->find('span[class=base-price]') as $e){
			$values["amount-value"] =preg_replace('/\D/', '',  $e->plaintext);
			$values["amount-currency"] =preg_replace('/[^a-zA-Z]+/', '',  $e->plaintext);
		}
		foreach ($a->find('div[class*=nutritional-values]') as $e){
			foreach ($e->find('tbody tr') as $tabel_row){
				$str = $tabel_row->children(0) -> innertext;
				$str=str_replace(array('ä','ö','ü','ß','Ä','Ö','Ü'),array('ae','oe','ue','ss','Ae','Oe','Ue'),$str);
				$temp[preg_replace('/[^a-zA-Z]+/', '_', $str)] = array(
						"value" => preg_replace('/[^0-9,.]+/', '',$tabel_row->children(1) -> innertext),
						"currency" => preg_replace('/[^a-zA-Z]+/', '',$tabel_row->children(1) -> innertext)
				);
			}
			unset($temp["Durchschnittliche Nährwerte"]);
			$values["naehrwerte"] = $temp;
			unset($temp);
		}
	
	
	}
	switch ($format) {
		case "array":
			return $values;
			break;
		case "turtle":
			return toTurtle($values);
			break;
		default:
			return "falsches Format gewaehlt!";
			break;
	}
	return $values;
};

function getReweDataByApi($idOrLink, $format) {
	//$suchbegriff = "milch";
	//$suchbegriff = preg_replace ( '/[^a-zA-Z0-9]+/', '', $suchbegriff );
	$id = preg_replace('/[^0-9]+/', '',  after_last("/PD", $idOrLink));
	if ($id == ""){
		$id = preg_replace('/[^0-9]+/', '',  $idOrLink);
	}
	$base = "https://shop.rewe.de/PD".$id;
	
	$url = "https://api.import.io/store/connector/d1d51bb1-5caf-4369-aa66-026ef8cfd987/_query?input=webpage/url:".$base."&&_apikey=40581aa5770c4331b477ca6b191549da6a6ebe6117061cab22ffe286a3bec687a28dd324679b4b65c0e05e44c47739c45a4e56111fd9762d4e3dc345e60732bfe413ad4f56724681aeb33b4055f1e54a";
	$result = json_decode ( curlData ( $url ), true );
	if (empty($result["results"])) {
		return "";
	}
	//$result = json_decode ( file_get_contents("/Users/raphaelmanke/git/chefkochWrapperToRDF/chefkochWrapperToRDF/library/test.txt"), true );
	// print_r($result);
	foreach ( $result ["results"] as $resultItem => $resultValue ) {
		$key = str_replace(array('ä','ö','ü','ß','Ä','Ö','Ü'),array('ae','oe','ue','ss','Ae','Oe','Ue'),$resultValue["naehrwerte"]);
		$key =preg_replace('/[^a-zA-ZäöüÄÖÜ]+/', '_', $key);
		$value = preg_replace('/[^0-9,.]+/', '', $resultValue["werte"]);
		$value= str_replace(".", ",", $value);
		$currencie = preg_replace('/[^a-zA-Zµ]+/', '', $resultValue["werte"]);
		
		if (isset($formattetArray[$resultValue["naehrwerte"]])) {
			$formattetArray[$key."1"] = array("value" => $value, "currencie" => $currencie);
		}else {
		$formattetArray[$key] = array("value" => $value, "currencie" => $currencie);
		}
		
		
		//$formattetArray [$resultItem] ["identifier"] = "searchResult";
		//$formattetArray [$resultItem] ["suchbegriff"] = $suchbegriff;
		//$formattetArray [$resultItem] ["wrapperLink"] = "http://wrapper:8888/index.php/reweProdukt/".$formattetArray [$resultItem]["headlineitem-link"];
	}
	$array = array(
			"link" => str_replace('https:/', 'https://',  substr($idOrLink,0,-1)), 
			"naehrwerte" => $formattetArray );
	
	//print_r($result);
	 //print_r($array);
	 switch ($format) {
	 	case "array":
	 		return $array;
	 		break;
	 	case "turtle":
	 		return toTurtle($array);
	 		break;
	 	default:
	 		return "falsches Format gewaehlt!";
	 		break;
	 }
	/*
	$graph = new EasyRdf_Graph ();
	
	foreach ( $formattetArray as $key => $value ) {
		if ($key < 11) {
			buildTree ( $graph, $idOrLink, $value );
		}
	}
	echo $graph->serialise ( "turtle" );
	*/
};
function toTurtle ($array) {
	$graph = new EasyRdf_Graph();
	$rezeptNamespace = new EasyRdf_Namespace ();
	$rezeptNamespace->set ( 'rezept', "http://manke-hosting.de/ns-syntax#" );
	$rezeptNamespace->set ( 'wrapper', HOST . "index.php/lookup/" );
	$rezeptNamespace->set ( 'owl', "http://www.w3.org/2002/07/owl#" );
	buildTree($graph, $array["link"], $array, "rezept");
	return $graph->serialise ( "turtle" );
};

//$comment = $html->find('comment');
// print_r(getReweData("7890168"))."\n";
// echo "second \n";
// print_r(getReweData("https://shop.rewe.de/kuehlprodukte/eier/rewe-bio-frische-bio-eier-6-stueck/PD197892"))."\n";
