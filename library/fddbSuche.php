<?php
//include_once 'simple_html_dom.php';
include_once 'utils.php';
include_once 'getReweSearch.php';
include_once 'getReweProduct.php';
include_once 'konstanten.php';
function fddbSuche($gtin) {
	
	//$gtin = "4388844009943";
	$gtin = preg_replace('/[^0-9]+/', '', $gtin);
	$data = curlData("http://fddb.mobi/search/?lang=de&cat=mobile-de&search=". $gtin);
	$resultblock = between("<!-- %sresultblock% //-->", "<!-- %eresultblock% //-->", $data);
	$link= between("window.location.href = '", "';", $data);
	$area = between("<b>100 g:</b>", "<b>1 Packung:</b>", $data);
	if ($link != false && strlen($gtin) >= 13){
		
	$werte = array(
			"Energie" => before("(", $area),
			"Energie1" => between("(", ")", $area),
			"Fett" => between("Fett: ", "KH:", $area),
			"Kohlenhydrate" => between("KH: ", "<br>", $area));
	//$werte = array("Energie", "Energie1", "Fett", "Kohlenhydrate");
	$naerhwerte ="";
	foreach ($werte as $key => $value) {
		$naerhwerte [$key] ["value"] =preg_replace('/[^0-9]+/', '', $werte[$key]) ;
		$naerhwerte [$key] ["currencie"] =preg_replace('/[^a-zA-Z]+/', '', $werte[$key]) ;
	}
	
	$result = array(
			"link" => $link,
			"gtin" => $gtin,
			"naehrwerte" => $naerhwerte,
			"titel" => between("<a href='".$link."'><b>", '</b></a>', $resultblock)
	);
	$graph = new EasyRdf_Graph();
	buildTree($graph, $result["link"], $result);
	echo $graph -> serialise("turtle");
	}
}


//echo curlData($link);
// Create a DOM object
/*
$html = new simple_html_dom();
// Load HTML from a string
$html->load(curlData($link));

foreach ($html -> find('table') as $a){
	echo $a->outertext;
	echo "\n";
}
*/

