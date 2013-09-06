<?php

	//TODO no globals, most of this session variables could be part of the parser user info system...

  const PIWOVERSION = "0.2.1 - Alpha";
  
  //TODO Security issue... Change this!!! (just for testing purposes)
  $user = "root";
  $pwd = "qwertz";

  if (! isset($_SESSION['pw_wiki']['config'])) {
    $config  = array ( "debug"        => true,
    				   "useCache"	  => false,
    				   "login"	 	  => array('group' => 'users', 'user' => 'guest'),
                       "startpage"    => "index",
                       "storage"      => "dat",
                       "fileext"      => ".txt",
                       "wikititle"    => "piwo",
                       "titledesc"    => PIWOVERSION,
                       "showuser"     => true,
                       "showsource"   => true,
                       "showpages"    => true,
                       "config"       => true,
                       "description"  => "coding, brainstorming, testing, hoffen und suden",
                       "keywords"     => "php, javascript, java, cms, wiki, tests",
                       "anchor_text"  => array( "_top"        => "Seitenanfang",
                                                "_toc"        => "Inhaltsverzeichnis",
                                                "_maintitle"  => "Haupttitel",
                                                "_bottom"     => "Seitenende"
                                              ));
    if (isset($_SESSION['pw_wiki']) && is_array($_SESSION['pw_wiki'])) {
      $_SESSION['pw_wiki'] = array_merge($_SESSION['pw_wiki'], $config);
    } else {
      $_SESSION['pw_wiki'] = $config;
    }
  }

?>