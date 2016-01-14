# chefkochWrapperToRDF
A Wrapper which transalte a Chefkoch Search to RDF.<br>

Usage:<br>

http://yourServer.de/index.php/{param1}/{param2}<br>

param 1 = {explore | lookup}<br>
	=> explore := es wird eine Suche mit Suchbegriff durchgeführt<br>
	=> lookup := es werden die weiteren Rezeptinfos anhand der Rezept ID abgerufen<br>
	=> reweProdukt := es werden Produktinfos vom Rewe lieferservice abgerufen<br>

param 2 = Der Suchbegriff dieser kann sein:<br>
			- ein Suchbegriff für ein Rezept<br>
			- eine ID eines Rezeptes sein<br>
			- eine Produkt Id oder Url von Rewe.<br>

WICHTIG: <br>
es muss zusätzlich /library/konstanten.php angelegt mit dem Inhalt:<br>

define("HOST", "http://mein-Server/unterverzeichnis/"); <br>
define("CODECHEK_USR", "login-name"); <br>
define("CODECHEK_KEY", "login-key");

