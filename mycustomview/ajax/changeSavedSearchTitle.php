<?php

include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (!(PluginMycustomviewProfileRights::canUpdate())) {
    PluginMycustomviewProfileRights::addErrorMessage('Vous n\'avez pas les droits pour effectuer cette action');
    return false;
}

if (isset($_GET['id']) && isset($_GET['newTitle'])) {
    PluginMycustomviewSavedSearch::changeSavedSearchTitle($_GET['id'], $_GET['newTitle']);
    PluginMycustomviewProfileRights::addSuccessMessage('La titre de votre recherche sauvegardée n°'. $_GET['id'].' a bien été modifié');

}
