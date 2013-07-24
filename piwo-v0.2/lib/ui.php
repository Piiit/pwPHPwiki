<?php

function pw_ui_printDialogWrap($dialog = -1) {
  if ($dialog == -1) {
    StringFormat::htmlIndente ("</div>", END);
    return;
  }
  if ($dialog == "") {
    return false;
  }

  StringFormat::htmlIndente ("<!-- MODALDIALOG START -->");
  StringFormat::htmlIndente ('<div id="modal"><div class="overlay-decorator"></div><div class="overlay-wrap"><div class="overlay"><div class="dialog-wrap"><div class="dialog" id="dialog"><div class="dialog-decorator"><div id="ajax_dialog">', START);
  StringFormat::htmlIndente ($dialog);
  StringFormat::htmlIndente ('</div></div></div></div></div></div></div>', END);
  StringFormat::htmlIndente ("<div style='position: absolute; top: 0; left: 0; width: 100%; display: block; height: 100%; overflow: hidden'>");
  StringFormat::htmlIndente ("<!-- MODALDIALOG END -->", START);

  return true;
}

function pw_ui_getDialogInfo($title, $desc, $href, $method = "post") {
  $o  = StringFormat::htmlIndent();
  $o .= StringFormat::htmlIndent("<form method='$method' accept-charset='utf-8' id='form'>", START);
  $o .= StringFormat::htmlIndent("<h1>$title</h1>");
  $o .= StringFormat::htmlIndent("<div>$desc</div>");
  $o .= StringFormat::htmlIndent("<div>", START);
  $o .= StringFormat::htmlIndent("<a href='?$href' id='submit'>OK</a>");
  $o .= StringFormat::htmlIndent("</div>", END);
  $o .= StringFormat::htmlIndent("</form>", END);
  return $o;
}

function pw_ui_getDialogQuestion($title, $desc, $byesname, $byestext, $bno, $method = "post") {
  $o  = StringFormat::htmlIndent();
  $o .= StringFormat::htmlIndent("<form method='$method' accept-charset='utf-8' id='form'>", START);
  $o .= StringFormat::htmlIndent("<h1>$title</h1>");
  $o .= StringFormat::htmlIndent("<div>", START);
  $o .= $desc;
  $o .= StringFormat::htmlIndent("</div>", END);
  $o .= StringFormat::htmlIndent("<div>", START);
  $o .= StringFormat::htmlIndent("<input id='submit' type='submit' name='$byesname' value='$byestext' />");
  $o .= StringFormat::htmlIndent("<a href='?$bno'>Abbrechen</a>");
  $o .= StringFormat::htmlIndent("</div>", END);
  $o .= StringFormat::htmlIndent("</form>", END);
  return $o;
}

function pw_ui_getButton($name, $href, $shortcut = null) {
  $o  = StringFormat::htmlIndent("<span class='edit'>");
  $o .= StringFormat::htmlIndent("<a href='?$href'>");
  if ($shortcut !== null) {
    $o .= StringFormat::htmlIndent("<span class='shortcut'>$shortcut</span>");
  }
  $o .= StringFormat::htmlIndent("$name</a></span>");

  return $o;
}

?>