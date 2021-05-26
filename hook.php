<?php
/*
 -------------------------------------------------------------------------
 MyCustomView plugin for GLPI
 Copyright (C) 2020 by the Département de Maine-et-Loire .

 https://github.com/pluginsGLPI/mycustomview
 -------------------------------------------------------------------------

 LICENSE

 This file is part of MyCustomView.

 MyCustomView is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 MyCustomView is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with MyCustomView. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_mycustomview_install()
{

   global $DB;

   // ------- On include les classes importantes
   include_once(GLPI_ROOT . "/plugins/mycustomview/inc/mycustomview.class.php");
   include_once(GLPI_ROOT . "/plugins/mycustomview/inc/profile.class.php");
   include_once(GLPI_ROOT . "/plugins/mycustomview/inc/config.class.php");


   // première installation -> Création de la table dans la base

   // requete de création des tables

   if (!$DB->TableExists("glpi_plugin_mycustomview_user_settings")) {
      $query = "CREATE TABLE `glpi_plugin_mycustomview_user_settings` (
         `id` INT(11) NOT NULL AUTO_INCREMENT,
         `user_id` INT(11) NOT NULL,
         `default_page` TINYINT,
         `list_limit` INT(11),
         `settings_hidden` TINYINT,
          PRIMARY KEY  (`id`)
       ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->query($query) or die("error creating glpi_plugin_mycustomview_user_settings " . $DB->error());
   }

   if (!$DB->TableExists("glpi_plugin_mycustomview_savedsearch_list")) {
      $query = "CREATE TABLE `glpi_plugin_mycustomview_savedsearch_list` (
         `id` INT(11) NOT NULL AUTO_INCREMENT,
         `user_id` INT(11) NOT NULL,
         `savedsearch_id` INT(11) NOT NULL,
         `order` TINYINT,
         `screen_mode` TINYINT,
         `height` INT(11),
          PRIMARY KEY  (`id`)
       ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->query($query) or die("error creating glpi_plugin_mycustomview_savedsearch_list " . $DB->error());
   }

   if (!$DB->TableExists("glpi_plugin_mycustomview_config")) {
      $query = "CREATE TABLE `glpi_plugin_mycustomview_config` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `max_filters` TINYINT,
         PRIMARY KEY  (`id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->query($query) or die("error creating glpi_plugin_mycustomview_config " . $DB->error());

      $DB->insert(
         'glpi_plugin_mycustomview_config',
         [
            //'id' => 1,
            'max_filters' => 6
         ]
      );
   }

   if (!$DB->TableExists("glpi_plugin_mycustomview_profile_rights")) {
      $query = "CREATE TABLE `glpi_plugin_mycustomview_profile_rights` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `profile` INT(11) NOT NULL,
        `right` CHAR(2) NOT NULL,
         PRIMARY KEY  (`id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->query($query) or die("error creating glpi_plugin_mycustomview_profile_rights " . $DB->error());

      //creation du premier accès nécessaire lors de l'installation du plugin
      //($_SESSION['glpiactiveprofile']['id']) will return 4 if super-admin
      PluginMycustomviewProfileRights::createAdminAccess($_SESSION['glpiactiveprofile']['id']);
   }

   if (!$DB->TableExists("glpi_plugin_mycustomview_displaypreferences")) {
      $query = "CREATE TABLE `glpi_plugin_mycustomview_displaypreferences` (
     `id` INT(11) NOT NULL AUTO_INCREMENT,
     `itemtype` VARCHAR(100) NOT NULL,
     `num` INT(11) NOT NULL,
     `rank` INT(11) NOT NULL,
     `users_id` INT(11) NOT NULL,
      PRIMARY KEY  (`id`)
   ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->query($query) or die("error creating glpi_plugin_mycustomview_displaypreferences " . $DB->error());
   }

   return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_mycustomview_uninstall()
{
   global $DB;

   $tables = array("glpi_plugin_mycustomview_user_settings", "glpi_plugin_mycustomview_displaypreferences", "glpi_plugin_mycustomview_savedsearch", "glpi_plugin_mycustomview_savedsearch_list", "glpi_plugin_mycustomview_config", "glpi_plugin_mycustomview_profile_rights");

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }
   return true;
}

function changePageOnHome()
{

   // vérification plugin activé + vérification du profil (si profil demandeur -> return)
   $plugin = new Plugin();
   if ($plugin->isActivated("mycustomview")) {
      if (isset($_SESSION['glpiactiveprofile']['id'])) {
         if ($_SESSION['glpiactiveprofile']['id'] == 1) {
            return;
         }
      }

      // vérification de la page par défaut
      if (PluginMycustomviewSavedSearch::isDefaultPageOfUser()) {
         $jsPluginMcv = "
           // VOIR DANS MUCUSTOMVIEW.JS COMMENT EST GERE LE PROBLEME DE CLICK SUR 'VUE PERSONNELLE OU TABLEAU DE BORD'
           var change = false;
           var myCustomView = document.querySelector('[title=\'Ma vue personnalisée\']');
           var dataChange = myCustomView.getAttribute('data-change');
           
           if (dataChange != null) {
              var change = true;
           }
           
           if(change == false) {
              myCustomView.click();
              change = true;
           }
           ";
         echo Html::scriptBlock($jsPluginMcv);
      }
   }
}
