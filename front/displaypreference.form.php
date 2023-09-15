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

error_reporting(E_ALL);
ini_set("display_errors","On");

if (!defined('GLPI_ROOT')) {
include ('../../../inc/includes.php');
}


Html::popHeader(__('Setup'), $_SERVER['PHP_SELF']);

 $setupdisplay = new PluginMycustomviewDisplayPreference();


if (isset($_POST["activate"])) {
   $setupdisplay->activatePersoMcv($_POST);

} else if (isset($_POST["disable"])) {
   if ($_POST['users_id'] == Session::getLoginUserID()) {
       $setupdisplay->deleteByCriteria(['users_id' => $_POST['users_id'],
                                                       'itemtype' => $_POST['itemtype']]);
   }
} else if (isset($_POST["add"])) {
   $setupdisplay->add($_POST);

} else if (isset($_POST["purge"]) || isset($_POST["purge_x"])) {
   $setupdisplay->delete($_POST, 1);

} else if (isset($_POST["up"]) || isset($_POST["up_x"])) {
   $setupdisplay->orderItem($_POST, 'up');

} else if (isset($_POST["down"]) || isset($_POST["down_x"])) {
   $setupdisplay->orderItem($_POST, 'down');
}

// Datas may come from GET or POST : use REQUEST
if (isset($_REQUEST["itemtype"])) {
   $setupdisplay->display(['displaytype' => $_REQUEST['itemtype']]);
}

Html::popFooter();