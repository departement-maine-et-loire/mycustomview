<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMycustomviewMyview extends CommonDBTM
{

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      if (!isset($_SESSION['glpi_plugin_mycustomview_profile']['id'])) {
         PluginMycustomviewProfileRights::changeProfile();
      }
      if (!(PluginMycustomviewProfileRights::canView())) {
         return false;
      }
      if ($item->getType() == 'Central') {
         return "Ma vue personnalisée";
      }
      
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      if ($item->getType() == 'Central') {
         $max_filters = PluginMycustomviewConfig::getMaxFilters();
         if (!isset($_SESSION['glpi_plugin_mycustomview_profile']['id'])) {
            PluginMycustomviewProfileRights::changeProfile();
         }
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
