# chefkochWrapperToRDF
A Wrapper which transalte a Chefkoch Search to RDF.

Usage:

http://yourServer.de/index.php/{param1}/{param2}

param 1 = {explore | lookup}
	=> explore := es wird eine Suche mit Suchbegriff durchgeführt
	=> lookup := es werden die weiteren Rezeptinfos anhand der Rezept ID abgerufen
	=> reweProdukt := es werden Produktinfos vom Rewe lieferservice abgerufen

param 2 = Der Suchbegriff dieser kann sein:
			- ein Suchbegriff für ein Rezept
			- eine ID eines Rezeptes sein
			- eine Produkt Id oder Url von Rewe.

WICHTIG: 
es muss zusätzlich /library/konstanten.php angelegt mit dem Inhalt:

define("HOST", "http://mein-Server/unterverzeichnis/"); <br>
define("CODECHEK_USR", "login-name"); <br>
define("CODECHEK_KEY", "login-key");

