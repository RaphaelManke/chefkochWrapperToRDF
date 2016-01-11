<?php


    function after ($this, $inthat)
    {
        if (!is_bool(strpos($inthat, $this)))
        return substr($inthat, strpos($inthat,$this)+strlen($this));
    };

    function after_last ($this, $inthat)
    {
        if (!is_bool(strrevpos($inthat, $this)))
        return substr($inthat, strrevpos($inthat, $this)+strlen($this));
    };

    function before ($this, $inthat)
    {
        return substr($inthat, 0, strpos($inthat, $this));
    };

    function before_last ($this, $inthat)
    {
        return substr($inthat, 0, strrevpos($inthat, $this));
    };

    function between ($this, $that, $inthat)
    {
        return before ($that, after($this, $inthat));
    };

    function between_last ($this, $that, $inthat)
    {
     return after_last($this, before_last($that, $inthat));
    };

// use strrevpos function in case your php version does not include it
function strrevpos($instr, $needle)
{
    $rev_pos = strpos (strrev($instr), strrev($needle));
    if ($rev_pos===false) return false;
    else return strlen($instr) - $rev_pos - strlen($needle);
};
/**
 * macht einen GET auf eine URL
 * @param String $url
 * @return String $result
 */
function curlData($url) {
	$ch = curl_init ();

	// setze die URL und andere Optionen
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );

	// führe die Aktion aus und speichere die Daten
	$result = curl_exec ( $ch );
	// schließe den cURL-Handle und gebe die Systemresourcen frei
	curl_close ( $ch );
	return (String) $result;
};
function buildTree ($graph, $nodeId, $array){
	if (!isset($nodeId)) {
		$nodeId = $graph->newBNode();
	}
	$me = $graph-> resource ($nodeId);
	foreach ($array as $key => $value){
		$type = gettype ( $value );
		if ($type != "array") {
			if (substr( $value, 0, 7 ) === "http://") {
				$me -> addResource("rdf:".$key, $value);
			}elseif (substr( $value, 0, 8 ) === "https://"){
				$me -> addResource("rdf:".$key, $value);
			}
			elseif($value != ""){
			$me -> add("rdf:".$key, $value);}
		}
		else {
			$bn = $graph->newBNode ();
			$me -> add("rdf:".$key, $bn);
			buildTree($graph, $bn, $value);
		}

	}


};
