<?php

if (!defined('INC_PATH')) {
  define ('INC_PATH', realpath(dirname(__FILE__).'/../../../../').'/');
}

require_once INC_PATH."piwo-v0.2/lib/testing.php";
require_once INC_PATH."pwTools/validator/pw_isvalid.php";

// Input...
$urls = array(
  "http://",
  "",
  "http://localhost",
  "www.google.com",
  "https://user:pass@www.somewhere.com:8080/login.php?do=login&style=%23#pagetop",
  "http://user@www.somewhere.com/#pagetop",
  "https://somewhere.com/index.html",
  "ftp://user:****@somewhere.com:21/",
  "http://somewhere.com/index.html/",
  "http://routerlogin",
  "http://url.com/?source=rss_feed",
  "...",
  "http://localhost/projekte/piwo/index.php?mode=cleared&id=index",
  "Hallo, wie gehts?",
  "Adresse",
  "http://www.example.com/file[/].html",
  "http://[2001:db8:85a3::8a2e:370:7334]/foo/bar", //an IPv6 literal instead of a host name
  "mailto://user@unkwndesign.com",
  "mailto:user@unkwndesign.com",
  "mailto:Fritz%20Eierschale%20%3Cfritz.eierschale@example.org%3E",
  "mailto:fritz.eierschale@example.org?cc=heidi.bratze@example.org&amp;subject=Hallo%20Fritz,%20hallo%20Heidi",
  "NIXDA://www.google.com",
  "http://[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:80/index.html",
  "http://[1080:0:0:0:8:800:200C:417A]/index.html",
  "http://[3ffe:2a00:100:7031::1]",
  "http://[1080::8:800:200C:417A]/foo",
  "http://[::192.9.5.5]/ipng",
  "http://[::FFFF:129.144.52.38]:80/index.html",
  "http://[2010:836B:4179::836B:4179]",
  "http:/sch.to"
);

// Expected Results...
$expres = array(
  false,
  false,
  true,
  true,
  true,
  true,
  true,
  true,
  true,
  true,
  true,
  false,
  true,
  false,
  false,
  false,
  true,
  false,
  true,
  true,
  true,
  false,
  true,
  true,
  true,
  true,
  true,
  true,
  true,
  false
);

function test_pw_isvalid_url($urls) {
  $res = array();
  foreach ($urls as $i => $url) {
    $res[$i] = pw_isvalid_url($url);
  }

  return $res;
}


testing_html_header('pw_regexp_checkurl');

$errors = 0;
$res = test_pw_isvalid_url($urls);

if (test_compare($expres, $res, $errors)) {
  test_overview_output("pw_regexp_checkurl", count($urls), $errors);
  test_output($urls, $expres, $res);
} else {
  test_overview_output("pw_regexp_checkurl", null, null);
}

testing_html_footer();
?>