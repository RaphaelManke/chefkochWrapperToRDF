<?php 
include 'simple_html_dom.php';
function get_string_between($string, $start, $end){
	$string = ' ' . $string;
	$ini = strpos($string, $start);
	if ($ini == 0) return '';
	$ini += strlen($start);
	$len = strpos($string, $end, $ini) - $ini;
	return substr($string, $ini, $len);
}
function strrevpos($instr, $needle)
{
	$rev_pos = strpos (strrev($instr), strrev($needle));
	if ($rev_pos===false) return false;
	else return strlen($instr) - $rev_pos - strlen($needle);
};
function after_last ($this, $inthat)
{
	if (!is_bool(strrevpos($inthat, $this)))
		return substr($inthat, strrevpos($inthat, $this)+strlen($this));
};
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

function getReweData($idOrLink){
	$id = preg_replace('/[^0-9]+/', '',  after_last("/", $idOrLink));
	if ($id == ""){
		$id = preg_replace('/[^0-9]+/', '',  $idOrLink);
	}
	$base = "https://shop.rewe.de/PD".$id;
	$html = getHtml($base);
	$values = [];
	foreach ($html -> find('section[class=product-container product-detail]') as $a){
		foreach($a->find('#productTitle') as $e) {
			$values['productTitle'] = $e->innertext;
		}
		foreach($a->find('comment') as $e){
			$values["gtin"] = get_string_between($e->innertext, "gtin: ", '"/> -->');
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
				$temp[$tabel_row->children(0) -> innertext] = array(
						"value" => preg_replace('/[^0-9,.]+/', '',$tabel_row->children(1) -> innertext),
						"currency" => preg_replace('/[^a-zA-Z]+/', '',$tabel_row->children(1) -> innertext)
				);
			}
			unset($temp["Durchschnittliche Nährwerte"]);
			$values["naehrwerte"] = $temp;
			unset($temp);
		}
	
	
	}
	return $values;
}
//$comment = $html->find('comment');
print_r(getReweData("7890168"))."\n";
echo "second \n";
print_r(getReweData("https://shop.rewe.de/kuehlprodukte/eier/rewe-bio-frische-bio-eier-6-stueck/PD197892"))."\n";
