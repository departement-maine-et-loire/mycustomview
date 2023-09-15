<?php
define('GLPI_ROOT', '../..');
include(GLPI_ROOT . "/inc/includes.php");

Session::checkRight("config", UPDATE, READ);


// To be available when plugin in not activated
Plugin::load('mycustomview');

Html::header("TITRE", $_SERVER['PHP_SELF'], "config", "plugins");

echo __("This is the plugin config page", 'mycustomview');

Html::footer();