<?php

include ('../../../inc/includes.php');

Session::checkLoginUser();

if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
   Html::header("TITRE", $_SERVER['PHP_SELF'], "plugins", "pluginmycustomview", "");
} else {
   Html::helpHeader("TITRE", $_SERVER['PHP_SELF']);
}

Search::show('Ticket');

if (Session::getCurrentInterface() == "helpdesk") {
   Html::helpFooter();
} else {
   Html::footer();
}

