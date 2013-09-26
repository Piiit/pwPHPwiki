<?php

  const PIWOVERSION = "0.2.1 - Alpha";
  const WIKIADMINUSER = "root";
  const WIKIADMINPASSWORD = "qwertz"; //TODO Security issue... Encryption needed!!!
  const WIKISTORAGE = "dat";
  const WIKISTARTPAGE = "index";
  const WIKIFILEEXT = ".txt";
  const WIKIDESCRIPTION = "coding, brainstorming, testing, hoffen und suden";
  const WIKIKEYWORDS = "php, javascript, java, cms, wiki, tests";
  
  $WIKIDEFAULTCONFIG = array ( 
			"debug"    	=> true,
 		    "useCache"	=> false,
    		"login"	 	=> array('group' => 'users', 'user' => 'guest'),
            "wikititle" => "piwo",
            "titledesc" => PIWOVERSION
            );

?>