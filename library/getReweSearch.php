<?php
include_once 'utils.php';
require 'EasyRdf.php';
include_once 'konstanten.php';
function getReweSearch($suchbegriff) {
	 //$suchbegriff = "milch";
	$suchbegriff = preg_replace ( '/[^a-zA-Z0-9]+/', '', $suchbegriff );
	//$url = "https://api.import.io/store/connector/478996fb-27a4-45ec-a1d8-2cc119d8d4a2/_query?input=webpage/url:https%3A%2F%2Fshop.rewe.de%2FproductList%3Fsearch%3D" . $suchbegriff . "&&_apikey=40581aa5770c4331b477ca6b191549da6a6ebe6117061cab22ffe286a3bec687a28dd324679b4b65c0e05e44c47739c45a4e56111fd9762d4e3dc345e60732bfe413ad4f56724681aeb33b4055f1e54a";
	$url = "https://api.import.io/store/connector/478996fb-27a4-45ec-a1d8-2cc119d8d4a2/_query?input=webpage/url:https%3A%2F%2Fshop.rewe.de%2FproductList%3Fsearch%3D" . $suchbegriff . "%26sorting%3DRELEVANCE%26category%3D%26mobileFiltersOpened%3D%26selectedFacets%3D&&_apikey=40581aa5770c4331b477ca6b191549da6a6ebe6117061cab22ffe286a3bec687a28dd324679b4b65c0e05e44c47739c45a4e56111fd9762d4e3dc345e60732bfe413ad4f56724681aeb33b4055f1e54a";
	$result = json_decode ( curlData ( $url ), true );
	//$result = json_decode ( file_get_contents("/Users/raphaelmanke/git/chefkochWrapperToRDF/chefkochWrapperToRDF/library/test.txt"), true );
	// print_r($result);
	foreach ( $result ["results"] as $resultItem => $resultValue ) {
		foreach ( $result ["outputProperties"] as $key => $value ) {
			if (isset($result ["results"] [$resultItem]
					[$value ["name"]])){
			$formattetArray [$resultItem] [preg_replace ( '/[^a-zA-Z0-9]+/', '-', strtolower ( $value ["name"] ) )] 
			= $result ["results"] [$resultItem] 
			[$value ["name"]];
			}
		}
		$formattetArray [$resultItem] ["identifier"] = "searchResult";
		$formattetArray [$resultItem] ["suchbegriff"] = $suchbegriff;
		//$formattetArray [$resultItem] ["wrapperLink"] = "http://wrapper:8888/index.php/reweProdukt/".$formattetArray [$resultItem]["headlineitem-link"];
		$formattetArray [$resultItem] ["wrapperLink"] = HOST."index.php/reweProdukt/".$formattetArray [$resultItem]["headlineitem-link"];
	}
	//print_r($result);
	// print_r($formattetArray);
	$graph = new EasyRdf_Graph ();
	
	foreach ( $formattetArray as $key => $value ) {
		if ($key < 2) {
			buildTree ( $graph, $value ["headlineitem-link"], $value );
		}
	}
	echo $graph->serialise ( "turtle" );
}
function getReweSearchByApi($suchbegriff) {
	$suchbegriff = preg_replace ( '/[^a-zA-Z0-9]+/', '', $suchbegriff );
	$url = 'https://shop.rewe.de/services/search?callback=jQuery&q='.$suchbegriff.'&format=json&start=0&rows=20&indent=true&_=1452340271007';
	$cookies = array(
			//'Cookie: myReweCookie=%7B%22customerZip%22%3A%2276131%22%2C%22customerLocation%22%3A%2249.0116951%2C8.430382099999974%22%2C%22basketSize%22%3A%22%22%2C%22basketValue%22%3A%22%22%2C%22serviceText%22%3A%22Liefergebiet+PLZ+76131%22%2C%22serviceType%22%3A%22DELIVERY%22%2C%22deliveryMarketId%22%3A%22840093%22%7D; ABTestGroup=B; mbox=PC#1451297721907-318731.26_01#1453549871|session#1452339209225-951265#1452342131; AMCV_65BE20B35350E8DE0A490D45%40AdobeOrg=1999109931%7CMCIDTS%7C16810%7CMCMID%7C79786094720021902350666691359105009961%7CMCAID%7C2B4085DE85313A7B-400001046001CF2E%7CMCAAMLH-1452944009%7C6%7CMCAAMB-1452944009%7Chmk_Lq6TPIBMW925SPhw3Q; acc=8ym1joyr12ucp8evvm8kybrz2cy29pqpv0p00dtdy5y131dry4jhspyhk5hy0y0yxct1oy0y5yhjj3yx9z2cy29pqa90600eh9y5y131dry4jhspyhk5hy0y0yxct1oy0y5yhjj3yxuz2cy29pqa102g04d1y5y131dry4jhmiyhk5hy0y0yxct1oy0y5yhj9nyx6z2cy29aes5vv00fo1y6y1246sy365c2y0y0y0yxct1oy0y7yhdcoyxy3y29aev5ml00149bz2cy29adkechg0eh9y6y1246sy365fdy0y0y0yxct1oy0y7yhdcoyxy6y29adn3t3g0cutgz2cy29adaplmg0dtey6y1246sy365fdy0y0y0yxct1oy0y7yhdcoyxy2y29adas2ug007dnz2cy29abqg0r0081ly5y123jmy365l3yhdvhy0y0yxct1oy0y5yhdcoyxy3y29ad6egq00149mz2cy29a9mljc00fo1y6y1246sy365l3y0y0y0yxct1oy0y7yhdcoyxy4y29a9o3bc0062luz2cy29a9m6jk005jly6y1246sy365l3y0y0y0yxct1oy0y7yhdcoyxy3y29a9mj380162l3;																																																																																																				   __sonar=6818177221510020577; c_dslv=1452340256754; s_fid=6C272E00FC4356A0-1A6250173BA88D47; s_nr=1452340269410-Repeat; s_vi=[CS]v1|2B4085DE85313A7B-400001046001CF2E[CE]; homePageCookieInfo=%7B%22accept%22%3Afalse%7D; itemsperpage=40; JSESSIONID=51543306B816125FB07CB86DF278FB6A.KE3AECH8.KE3AECH8; secureToken=c9683d34-0eb5-4d0e-bbf4-bc46b717175f; c_lpv=1452340255841|seo_google_nn_nn_nn_nn_nn_nn_nn; c_dslv_s=11; s_cc=true; s_vnum=1454281200231%26vn%3D1; s_invisit=true; s_sq=rewrewededev%2Crewglobal%3D%2526pid%253Drewe-de%25253Aservice%2526pidt%253D1%2526oid%253DSuche%2526oidt%253D3%2526ot%253DSUBMIT; mf_user=dc509be05a948a965b24f2001d051ffd; mf_2d859e38-92a3-4080-8117-c7e82466e45a=29532ba30dd9a5a9d525632cc05201b7|01095679f6829468f0ef10743076db00d498cf22|1452340269421||2|-1966771895',
			//'Cookie: myReweCookie=%7B%22customerZip%22%3A%2276131%22%2C%22customerLocation%22%3A%2249.0116951%2C8.430382099999974%22%2C%22basketSize%22%3A%22%22%2C%22basketValue%22%3A%22%22%2C%22serviceText%22%3A%22Liefergebiet+PLZ+76131%22%2C%22serviceType%22%3A%22DELIVERY%22%2C%22deliveryMarketId%22%3A%22840093%22%7D; ABTestGroup=B; mbox=PC#1451297721907-318731.26_01#1454601069|session#1453391234919-683024#1453393329; AMCV_65BE20B35350E8DE0A490D45%40AdobeOrg=1999109931%7CMCIDTS%7C16822%7CMCMID%7C79786094720021902350666691359105009961%7CMCAID%7C2B4085DE85313A7B-400001046001CF2E%7CMCAAMLH-1453996035%7C6%7CMCAAMB-1453996035%7Chmk_Lq6TPIBMW925SPhw3Q; acc=8yr9hvyr12ucp8evvm8kybhz2cy2a9g0pafg0ftjy6y14bncy6226ly0y0y0yxct1oy0y7yhuakyxdz2cy2a9fuog70081uy6y14op0y6238tyhukoy4cf3ay1q30eayxct1oy0y7yhuakyxy3y2a9fv50c004dbtz2cy29tnqdco00eh5y6y13lp6y5cv5ry0y0y0yxct1oy0y7yhoimyxuz2cy29tnlonng062py6y13lp6y5cv5ry0y0y0yxct1oy0y7yhoimyxez2cy29tnlileg081py6y13lo7y5cveuyhoipy4akjgy1of9e9yxct1oy0y7yhoimyxy2y29tnlkopg0dt1rz2cy29psu3hag0eh9y6y131jhy4jj9ry0y0y0yxct1oy0y7yhjj3yxsz2cy29pqpv0p00dtdy5y131dry4jhspyhk5hy0y0yxct1oy0y5yhjj3yxy2y29pqq770004d18z2cy29pqa90600eh9y5y131dry4jhspyhk5hy0y0yxct1oy0y5yhjj3yxuz2cy29pqa102g04d1y5y131dry4jhmiyhk5hy0y0yxct1oy0y5yhj9nyx6z2cy29aes5vv00fo1y6y1246sy365c2y0y0y0yxct1oy0y7yhdcoyxy3y29aev5ml00149bz2cy29adkechg0eh9y6y1246sy365fdy0y0y0yxct1oy0y7yhdcoyxy6y29adn3t3g0cutgz2cy29adaplmg0dtey6y1246sy365fdy0y0y0yxct1oy0y7yhdcoyxy2y29adas2ug007dnz2cy29abqg0r0081ly5y123jmy365l3yhdvhy0y0yxct1oy0y5yhdcoyxy3y29ad6egq00149mz2cy29a9mljc00fo1y6y1246sy365l3y0y0y0yxct1oy0y7yhdcoyxy4y29a9o3bc0062luz2cy29a9m6jk005jly6y1246sy365l3y0y0y0yxct1oy0y7yhdcoyxy3y29a9mj380162l3; __sonar=6818177221510020577; c_dslv=1453391376416; s_fid=6C272E00FC4356A0-1A6250173BA88D47; s_nr=1453391376422-Repeat; s_vi=[CS]v1|2B4085DE85313A7B-400001046001CF2E[CE]; homePageCookieInfo=%7B%22accept%22%3Afalse%7D; itemsperpage=40; s_vnum=1454281200231%26vn%3D4; mf_user=dc509be05a948a965b24f2001d051ffd; ecid=aff_zanox_2070722_nn_nn_nn_nn_nn_nn; c_lpv=1453391470268|dir_direct_nn_nn_nn_nn_nn_nn_nn; mf_2d859e38-92a3-4080-8117-c7e82466e45a=-1; c_dslv_s=9; s_cc=true; s_invisit=true; s_sq=%5B%5BB%5D%5D; JSESSIONID=0D9A46DD8AE6B294FE0B12D5B1F385DF.5gSnopR4.5gSnopR4; secureToken=957f8d2b-be5f-4267-a551-f86a966c40bf',
			//'Cookie:"ABTestGroup=B; AMCV_65BE20B35350E8DE0A490D45%40AdobeOrg=1999109931%7CMCIDTS%7C16824%7CMCMID%7C91425657749439211185478362468588658449%7CMCAID%7CNONE; mbox=session#1453551932070-407406#1453553832|em-disabled#true#1453553738; JSESSIONID=4929D2D42E06D854CD9788BE9250D4B2.UMO1MUUT.UMO1MUUT; myReweCookie=%7B%22customerZip%22%3A%2276131%22%2C%22customerLocation%22%3A%2249.0116951%2C8.430382099999974%22%2C%22serviceType%22%3A%22STATIONARY%22%2C%22stationaryMarketId%22%3A%22831007%22%7D"',
			'Cookie:"ABTestGroup=B; AMCV_65BE20B35350E8DE0A490D45%40AdobeOrg=1999109931%7CMCIDTS%7C16824%7CMCMID%7C00603201718960094233246323349821284534%7CMCAID%7CNONE; JSESSIONID=17EA1AFC6F79D3276331BDC0018D006F.dnaQpt0A.dnaQpt0A; myReweCookie=%7B%22customerZip%22%3A%2276131%22%2C%22customerLocation%22%3A%2249.0116951%2C8.430382099999974%22%2C%22serviceType%22%3A%22STATIONARY%22%2C%22stationaryMarketId%22%3A%22831007%22%7D; mbox=session#1453569289207-57264#1453571156|em-disabled#true#1453571095"'
			//'Cookie: homePageCookieInfo=%7B%22accept%22%3Afalse%7D; itemsperpage=10; viewMode=is-list; ABTestGroup=B; AMCV_65BE20B35350E8DE0A490D45%40AdobeOrg=1999109931%7CMCIDTS%7C16824%7CMCMID%7C15891270997053393754515849567990099083%7CMCAID%7CNONE%7CMCAAMLH-1454150212%7C6%7CMCAAMB-1454150212%7Chmk_Lq6TPIBMW925SPhw3Q; JSESSIONID=897BF1FBB9171DF56C950DA1E9839204.5gSnopR4.5gSnopR4; secureToken=9924d05d-a5f1-4b73-8f08-9e0d700e328b; myReweCookie=%7B%22customerZip%22%3A%2276131%22%2C%22customerLocation%22%3A%2249.0116951%2C8.430382099999974%22%2C%22basketSize%22%3A%22%22%2C%22basketValue%22%3A%22%22%2C%22serviceText%22%3A%22Liefergebiet+PLZ+76131%22%2C%22serviceType%22%3A%22DELIVERY%22%2C%22deliveryMarketId%22%3A%22840093%22%7D; mbox=session#1453545411817-358812#1453547301|PC#1453545411817-358812.26_1#1454755041|em-disabled#true#1453547222',
// 			'Cookie:'. 
// 			'ABTestGroup:"B";'.
// 			'AMCV_65BE20B35350E8DE0A490D45@AdobeOrg:"1999109931|MCIDTS|16824|MCMID|89123373166528362123016814731372780501|MCAID|NONE";'.
// 			'JSESSIONID:"50FEE2195FACDB11CE7545B7ED27AAB8.KE3AECH8.KE3AECH8";'.
// 			'itemsperpage:"40";'.
// 			'mbox:"session#1453550292335-628728#1453552263|em-disabled#true#1453552139";'.
// 			'myReweCookie:"{"customerZip":"76131","customerLocation":"49.0116951,8.430382099999974","basketSize":"","basketValue":"","serviceText":"Liefergebiet+PLZ+76131","serviceType":"DELIVERY","deliveryMarketId":"840093","stationaryMarketId":"840183"}";'.
// 			'secureToken:"c68862d6-2a36-4630-851e-c7911d48a7af";'		
	);
	/*
	 * 			ABTestGroup=B; 
			AMCV_65BE20B35350E8DE0A490D45%40AdobeOrg=1999109931%7CMCAAMB-1454150091%7CNRX38WO0n5BH8Th-nqAG_A%7CMCAAMLH-1454150091%7C6%7CMCIDTS%7C16824%7CMCMID%7C53856120561605997411982999606108973642%7CMCAID%7C2B51ABA605311A79-4000011440006843; 
			mbox=session#1453545291419-220400#1453548277|PC#1453545291419-220400.26_5#1454756017; 
			homePageCookieInfo=%7B%22accept%22%3Atrue%7D; 
			c_lpv=1453546389961|dir_direct_nn_nn_nn_nn_nn_nn_nn; 
			acc=8ybveoyr20140hcvvcf4yvcz2cy2abpolgt0062ty6y14bncy6226ly0y0y0yxct1oy0y7yhuakyxycy2abpu3ieg0dt70z2cy2abpeiugg0dt7y6y14op0y6238tyhukoy4cf3ay1q30eayxct1oy0y7yhuakyxy4y2abpojr800ftjdz2cy2abpdbu60062ty6y14op0y6238tyhukoy4cf3ay1q30eayxct1oy0y7yhuakyxy7y2abpe5jhg0fob6; 
			c_dslv_s=First%20Visit; 
			c_dslv=1453546390246; 
			s_cc=true; 
			s_fid=3FA4972D858B6C39-367567BC281EE25E; 
			s_nr=1453546415471-New; 
			s_vnum=1454281200873%26vn%3D1; 
			s_invisit=true; 
			s_sq=rewrewededev%2Crewglobal%3D%2526pid%253Drewe-de%25253Aservice%25253Asuche%2526pidt%253D1%2526oid%253DSuche%2526oidt%253D3%2526ot%253DSUBMIT%26rewrewedeprod%3D%2526pid%253Dmarket%252520layer%25253A%252520services%2526pidt%253D1%2526oid%253D%25250A%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520Markt%252520ausw%2525C3%2525A4hlen%25250A%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%25253E%25250A%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%252520%2526oidt%253D3%2526ot%253DSUBMIT; 
			s_vi=[CS]v1|2B51ABA605311A79-4000011440006843[CE]; 
			mf_2d859e38-92a3-4080-8117-c7e82466e45a=-1; 
			JSESSIONID=90739C996442D92873918F25412E84AE.KE3AECH8.KE3AECH8; 
			secureToken=e9f666a8-1bf2-4d5b-90cd-4a5383c9c55d; 
			btpdb.DFi2mPT.dGZjLjMwMzgxMjY=U0VTU0lPTg; 
			myReweCookie=%7B%22customerZip%22%3A%2276131%22%2C%22customerLocation%22%3A%2249.0116951%2C8.430382099999974%22%2C%22basketSize%22%3A%22%22%2C%22basketValue%22%3A%22%22%2C%22serviceText%22%3A%22Liefergebiet+PLZ+76131%22%2C%22serviceType%22%3A%22DELIVERY%22%2C%22deliveryMarketId%22%3A%22840093%22%2C%22stationaryMarketId%22%3A%22840183%22%7D',
 
	 */
	
	
	$headers = array(
			'Host: shop.rewe.de',
			'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:43.0) Gecko/20100101 Firefox/43.0',
			'Accept: */*',
			'Accept-Language: de,en-US;q=0.7,en;q=0.3',
			'Referer: https://www.rewe.de/service/suche/?search='.$suchbegriff,
			$cookies[array_rand($cookies)],
			'Connection: keep-alive'
			
	);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.22 Safari/537.36");
	
	
	
	//curl_setopt($ch, CURLOPT_COOKIE, 'language=0; sid=tjvgtpl0crv62d1tbfaoekg2n0; sid_key=oxid; SRV=varnish-local; emos_jcsid=AAABUiYqvVN0Z_u_wjUmLGQNvVezLUqz:4:AAABUiYraoSTMZkLw9NsWGps7KRP7BPZ:1452339325572; emos_jcvid=AAABUiYqvVN0Z_u_wjUmLGQNvVezLUqz:1:AAABUiYqvVN0Z_u_wjUmLGQNvVezLUqz:1452339281235:0:true:1');
	// führe die Aktion aus und speichere die Daten
	$data = curl_exec ( $ch );
	// schließe den cURL-Handle und gebe die Systemresourcen frei
	
	
	if (curl_errno($ch)) {
		print "Error: " . curl_error($ch);
	} else {
		// Show me the result
		curl_close($ch);
		$data = json_decode(substr($data, 7,-1),JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$products = $data["products"]["docs"];
		//print_r($products);
		$graph = new EasyRdf_Graph();
		$graphNamespace = new EasyRdf_Namespace ();
		$graphNamespace->set ( 'rezept', "http://manke-hosting.de/ns-syntax#" );
		
		foreach ($products as $key => $value){
			if ($key < 20) {
				
			
			$value["identifier"] = "searchResult";
			$value["suchbegriff"] = $suchbegriff;
			//$formattetArray [$resultItem] ["wrapperLink"] = "http://wrapper:8888/index.php/reweProdukt/".$formattetArray [$resultItem]["headlineitem-link"];
			//$value["wrapperLink"] = "http://manke-hosting.de/wrapper/index.php/reweProdukt/".$value["url"];
			//$value["wrapperLink"] = "http://localhost/wrapper/index.php/reweProduktFddb/".$value["gtin"];
			$value["codecheckWrapperLink"] = HOST."index.php/codecheckEan/".$value["gtin"];
			$value["reweProduktWrapperLink"] = HOST."index.php/reweProdukt/".$value["url"];
				
			buildTree($graph, $value["url"], $value, "rezept");
			}
			
		}
		echo $graph -> serialise("turtle");
		
	}
	
	
}