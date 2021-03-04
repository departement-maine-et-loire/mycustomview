<?php

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
