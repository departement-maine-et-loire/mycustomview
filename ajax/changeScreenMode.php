<?php


include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (!(PluginMycustomviewProfileRights::canUpdate())) {
    PluginMycustomviewProfileRights::addErrorMessage('Vous n\'avez pas les droits pour effectuer cette action');
    return false;
}

if (isset($_GET['id']) && isset($_GET['screenmode'])) {
    PluginMycustomviewSavedSearch::changeScreenMode($_GET['id'], $_GET['screenmode']);
    PluginMycustomviewProfileRights::addSuccessMessage('La taille de votre fenêtre a bien été modifiée.');

}
