<?php
/*
 -------------------------------------------------------------------------
 MyCustomView plugin for GLPI
 Copyright (C) 2020 by the MyCustomView Development Team.

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

define('PLUGIN_MYCUSTOMVIEW_VERSION', '1.2.1');
include_once(GLPI_ROOT . "/plugins/mycustomview/inc/profilerights.class.php");

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_mycustomview()
{
   global $PLUGIN_HOOKS;
   Plugin::registerClass('PluginMycustomviewProfile', ['addtabon' => 'Profile']);
   Plugin::registerClass('PluginMycustomviewMyview', ['addtabon' => 'Central']);
   Plugin::registerClass('PluginMycustomviewDisplayPreference', ['addtabon' => 'DisplayPreference']);

   $PLUGIN_HOOKS['change_profile']['mycustomview'] = ['PluginMycustomviewProfileRights', 'changeProfile'];
   $PLUGIN_HOOKS['csrf_compliant']['mycustomview'] = true;
   // -- PAGE DE CONFIGURATION -- 
   $PLUGIN_HOOKS['config_page']['mycustomview'] = 'front/config.form.php';
   // - PAGE D'ACCUEIL
   $PLUGIN_HOOKS['display_central']['mycustomview'] = "changePageOnHome";

   // -- JAVASCRIPT --
   $PLUGIN_HOOKS["javascript"]['mycustomview'] = [
      "/plugins/mycustomview/js/mycustomview.js",
   ];
   $PLUGIN_HOOKS["javascript"]['mycustomview'] = [
      "/plugins/mycustomview/js/jquery.dad.js",
   ];

   // -- CSS --
   $PLUGIN_HOOKS['add_css']['mycustomview'] = [
      "/plugins/mycustomview/css/mycustomview.css",
   ];
   $PLUGIN_HOOKS['add_css']['mycustomview'] = [
      "/plugins/mycustomview/css/theme.default.min.css",
   ];
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_mycustomview()
{
   return [
      'name'           => 'MyCustomView',
      'version'        => PLUGIN_MYCUSTOMVIEW_VERSION,
      'author'         => 'Maxime MERIOT',
      'license'        => '',
      'homepage'       => 'https://github.com/departement-maine-et-loire/mycustomview',
      'requirements'   => [
         'glpi' => [
            'min' => '9.1',
         ]
      ]
   ];
}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_mycustomview_check_prerequisites()
{

   //Version check is not done by core in GLPI < 9.2 but has to be delegated to core in GLPI >= 9.2.
   $version = preg_replace('/^((\d+\.?)+).*$/', '$1', GLPI_VERSION);
   if (version_compare($version, '9.1', '<')) {
      echo "This plugin requires GLPI >= 9.1";
      return false;
   }
   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_mycustomview_check_config($verbose = false)
{
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      echo __('Installed / not configured', 'mycustomview');
   }
   return false;
}
