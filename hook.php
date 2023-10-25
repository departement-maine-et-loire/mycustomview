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

   PluginMycustomviewProfile::createFirstAccess($_SESSION["glpiactiveprofile"]["id"]);

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
            'max_filters' => 6
         ]
      );
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

   $tables = array("glpi_plugin_mycustomview_user_settings", "glpi_plugin_mycustomview_savedsearch", "glpi_plugin_mycustomview_savedsearch_list", "glpi_plugin_mycustomview_config", "glpi_plugin_mycustomview_profile_rights");

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   global $DB;

    $result = $DB->request([
        'SELECT' => ['profiles_id'],
        'FROM' => 'glpi_profilerights',
        'WHERE' => ['name' => ['LIKE', 'plugin_mycustomview%']]
    ]);

    foreach ($result as $id_profil) {
        $DB->delete(
            'glpi_profilerights', [
                'name' => ['LIKE', 'plugin_mycustomview%'],
                'profiles_id' => $id_profil
            ]
        );
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
   }
}