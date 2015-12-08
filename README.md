# chefkochWrapperToRDF
A Wrapper which transalte a Chefkoch Search to RDF.

Usage:

http://yourServer.de/index.php/{param1}/{param2}

param 1 = {explore | lookup}
	=> explore := es wird eine Suche mit Suchbegriff durchgeführt
	=> lookup := es werden die weiteren Rezeptinfos anhand der Rezept ID abgerufen

param 2 = Der Suchbegriff dieser kann eine Suchbegriff oder eine ID eines Rezeptes sein.
