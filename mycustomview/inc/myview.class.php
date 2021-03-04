<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMycustomviewMyview extends PluginMycustomviewBlocs
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

         $view = new self();
         $max_filters = PluginMycustomviewConfig::getMaxFilters();
         if (!isset($_SESSION['glpi_plugin_mycustomview_profile']['id'])) {
            PluginMycustomviewProfileRights::changeProfile();
         }
         $view->showBlocs($max_filters);
      }
      return true;
   }

   static public function getUserNameMcv()
   {


      return $_SESSION['glpirealname'] . " " . $_SESSION['glpifirstname'];
   }
}
