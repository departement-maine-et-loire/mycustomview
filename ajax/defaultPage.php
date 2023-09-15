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

include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (PluginMycustomviewProfile::checkProfileRight($_SESSION['glpiactiveprofile']['id']) != ALLSTANDARDRIGHT) {
    Session::addMessageAfterRedirect(__("Vous n'avez pas les droits pour effectuer cette action", "mycustomview"), true, ERROR);
    return false;
}

if (isset($_GET['default'])) {
    PluginMycustomviewSavedSearch::changeDefaultPage($_GET['default']);
    if ($_GET['default'] == 1) {
        Session::addMessageAfterRedirect(__("Cette page est devenue votre page par défaut à la connexion.", "mycustomview"));
    } else if ($_GET['default'] == 0) {
        Session::addMessageAfterRedirect(__("Cette page n'est plus votre page par défaut à la connexion.", "mycustomview"));
    }
}