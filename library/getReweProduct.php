<?php 
include 'simple_html_dom.php';
include_once 'utils.php';
include_once 'EasyRdf.php';
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
			$values["link"] = $e->href;
	
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
function toTurtle ($array) {
	$graph = new EasyRdf_Graph();
	$rezeptNamespace = new EasyRdf_Namespace ();
	$rezeptNamespace->set ( 'rdf', "http://www.w3.org/1999/02/22-rdf-syntax-ns#" );
	$rezeptNamespace->set ( 'wrapper', "http://manke-hosting.de/wrapper/index.php/lookup/" );
	$rezeptNamespace->set ( 'owl', "http://www.w3.org/2002/07/owl#" );
	buildTree($graph, $array["link"], $array);
	return $graph->serialise ( "turtle" );
};

//$comment = $html->find('comment');
// print_r(getReweData("7890168"))."\n";
// echo "second \n";
// print_r(getReweData("https://shop.rewe.de/kuehlprodukte/eier/rewe-bio-frische-bio-eier-6-stueck/PD197892"))."\n";
