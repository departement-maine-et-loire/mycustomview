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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMycustomviewMyview extends CommonDBTM
{

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      if (!(PluginMycustomviewProfile::checkProfileRight($_SESSION['glpiactiveprofile']['id']))) {
         return false;
      }
      if ($item->getType() == 'Central') {
         return __("Ma vue personnalisée", "mycustomview");
      }
      
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      if ($item->getType() == 'Central') {
         $max_filters = PluginMycustomviewConfig::getMaxFilters();
         PluginMycustomviewBlocs::showBlocs($max_filters);
      }
      return true;
   }

   static public function getUserNameMcv()
   {
      return $_SESSION['glpirealname'] . " " . $_SESSION['glpifirstname'];
   }

   public static function addScriptAndStyleSheet(){
      echo Html::css("/plugins/mycustomview/css/flexslider.css");
      echo Html::Scss("/plugins/mycustomview/css/mycustomview.scss");
      echo Html::script("/plugins/mycustomview/js/jquery.dad.js");
      echo Html::script("/plugins/mycustomview/js/jquery.tablesorter.min.js");
      echo Html::script("/plugins/mycustomview/js/jquery.tablesorter.widgets.min.js");
      echo Html::script("/plugins/mycustomview/js/jquery.flexslider.js");
      echo Html::script("/plugins/mycustomview/js/mycustomview.js");
      echo Html::css("/plugins/mycustomview/css/theme.default.min.css");
   }
}

if (PluginMycustomviewSavedSearch::isDefaultPageOfUser()) {
    $jsPluginMcv = "
        if (document.querySelector('[title=\'My custom view\']') != null) {
            var change = false
            var myCustomView = document.querySelector('[title=\'My custom view\']');
            var dataChange = myCustomView.getAttribute('data-change');
            
            if (dataChange != null) {
                var change = true;
            }
            
            if(change === false) {
                myCustomView.click();
                change = true;
            }
        } else if (document.querySelector('[title=\'Ma vue personnalisée\']') != null) {
            var change = false
            var myCustomView = document.querySelector('[title=\'Ma vue personnalisée\']');
            var dataChange = myCustomView.getAttribute('data-change');
            
            if (dataChange != null) {
                var change = true;
            }
            
            if(change === false) {
                myCustomView.click();
                change = true;
            }
        }
    ";
    echo Html::scriptBlock($jsPluginMcv);
}