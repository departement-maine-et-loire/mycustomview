<?php

include ('../../../inc/includes.php');

Session::checkLoginUser();

if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
   Html::header("TITRE", $_SERVER['PHP_SELF'], "plugins", "pluginmycustomview", "");
} else {
   Html::helpHeader("TITRE", $_SERVER['PHP_SELF']);
}

   $config = new PluginMycustomviewConfig();

   if($max_filters = $config->getMaxFilters()) {
      $config->showForm($max_filters);
   }
   else 
   {
      $config->showForm();
   }
   Html::closeForm();
   

Html::footer();
