<?php
/*
 -------------------------------------------------------------------------
 MyCustomView plugin for GLPI
 Copyright (C) 2023 by the MyCustomView Development Team.

 https://github.com/pluginsGLPI/mycustomview
 -------------------------------------------------------------------------

 LICENSE

 This file is part of MyCustomView.

 MyCustomView is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 MyCustomView is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with MyCustomView. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

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