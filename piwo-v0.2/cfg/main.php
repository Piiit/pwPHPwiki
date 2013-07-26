<?php

  global $piwo_version;

  if (! isset($_SESSION['pw_wiki']['config'])) {
    $config  = array ( "debug"        => false,
    				   "useCache"	  => true,
                       "startpage"    => "index",
                       "storage"      => "dat",
                       "fileext"      => ".txt",
                       "wikititle"    => "piwo",
                       "titledesc"    => isset($piwo_version) ? $piwo_version : "version missing",
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
    if (isset($_SESSION['pw_wiki']) and is_array($_SESSION['pw_wiki'])) {
      $_SESSION['pw_wiki'] = array_merge($_SESSION['pw_wiki'], $config);
    } else {
      $_SESSION['pw_wiki'] = $config;
    }
  }

?>