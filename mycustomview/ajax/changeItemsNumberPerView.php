<?php

include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (!(PluginMycustomviewProfileRights::canUpdate())) {
    PluginMycustomviewProfileRights::addErrorMessage('Vous n\'avez pas les droits pour effectuer cette action');
    return false;
}

if (isset($_GET['id']) && isset($_GET['number'])) {
    if (!PluginMycustomviewProfileRights::isAskerIdIdentic($_GET['id'])) {
        PluginMycustomviewProfileRights::addErrorMessage('Vous n\'avez pas les droits pour effectuer cette action');
        return false;
    }

    PluginMycustomviewSavedSearch::getListLimitForUser(Session::getLoginUserID(), $number = $_GET['number']);
    PluginMycustomviewProfileRights::addSuccessMessage('La nombre d&apos;éléments de vos vues a bien été modifié.');
}
