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

include('../inc/includes.php');

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$savedsearch = new PluginMycustomviewSavedSearch();
if (isset($_POST["add"])) {
   //Add a new saved search
   $savedsearch->check(-1, CREATE, $_POST);
   if ($savedsearch->addMcv($_POST)) {
      var_dump($savedsearch->getId());
      die;

      if ($_SESSION['glpibackcreated']) {
         Html::redirect($savedsearch->getLinkURL());
      }
   }
   Html::back();
} else if (isset($_POST["purge"])) {
   // delete a saved search
   $savedsearch->check($_POST['id'], DELETE);
   $savedsearch->delete($_POST, 1);
   $savedsearch->redirectToList();
} else if (isset($_POST["update"])) {
   //update a saved search
   $savedsearch->check($_POST['id'], UPDATE);
   $savedsearch->update($_POST);
   Html::back();
} else if (isset($_GET['create_notif'])) {
   $savedsearch->check($_GET['id'], UPDATE);
   $savedsearch->createNotif();
   Html::back();
} else { //print computer information
   if (Session::getCurrentInterface() == "helpdesk") {
      Html::helpHeader(SavedSearch::getTypeName(Session::getPluralNumber()));
   } else {
      Html::header(SavedSearch::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], 'tools', 'savedsearch');
   }
   //show computer form to add
   $savedsearch->display(['id' => $_GET["id"]]);
   Html::footer();
}