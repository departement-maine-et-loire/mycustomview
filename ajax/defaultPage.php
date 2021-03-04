<?php

include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (!(PluginMycustomviewProfileRights::canUpdate())) {
    PluginMycustomviewProfileRights::addErrorMessage('Vous n\'avez pas les droits pour effectuer cette action');
    return false;
}

if (isset($_GET['default'])) {
    PluginMycustomviewSavedSearch::changeDefaultPage($_GET['default']);
    if ($_GET['default'] == 1) {
        PluginMycustomviewProfileRights::addSuccessMessage('Cette page est devenue votre page par défaut à la connexion.');
    } else if ($_GET['default'] == 0) {
        PluginMycustomviewProfileRights::addSuccessMessage('Cette page n&apos;est plus votre page par défaut à la connexion.');
    }
}
