<?php

  const PIWOVERSION = "0.1.9 - Alpha";
  const WIKIADMINUSER = "root";
  const WIKIADMINPASSWORD = "qwertz"; //TODO Security issue... Encryption needed!!!
  const WIKISTORAGE = "dat";
  const WIKICACHE = "home";
  const WIKIFILEEXT = ".txt";
  const WIKICACHEFILEEXT = ".html";
  const WIKINSDEFAULTPAGE = "_index";
  const WIKITEMPLATE = "tpl";	//TODO should be outside STORAGE
  const WIKITEMPLATESNS = ":tpl:";	//TODO no ns for templates, should be outside STORAGE
  const WIKIDESCRIPTION = "coding, brainstorming, testing, hoffen und suden";
  const WIKIKEYWORDS = "php, javascript, java, cms, wiki, tests";
  const NOTIFICATION_DELAY_MINIMUM = 1500; //in ms
  const NOTIFICATION_DELAY_PER_LETTER = 30;
  const DEFAULT_LANGUAGE = "en";
  const DEFAULT_DEBUG = false;
  const DEFAULT_USECACHE = false;
  const DEFAULT_LOGIN_GROUP = 'users';
  const DEFAULT_LOGIN_USER = 'guest';
  const DEFAULT_WIKITITLE = 'piwo';
  const DEFAULT_WIKITITLE_DESCRIPTION = PIWOVERSION;

  //TODO Remove this global variables...
  $WIKIDEFAULTCONFIG = array (
  		"debug"    	=> DEFAULT_DEBUG,
  		"useCache"	=> DEFAULT_USECACHE,
  		"login"	 	=> array(
  			'group' => DEFAULT_LOGIN_GROUP, 
  			'user'  => DEFAULT_LOGIN_USER
  		),
  		"wikititle" => DEFAULT_WIKITITLE,
  		"titledesc" => DEFAULT_WIKITITLE_DESCRIPTION
  );
  
  // Keep PHP warnings quite...
  $WIKIDEFAULTCONFIG;
?>