<?php

include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (!(PluginMycustomviewProfileRights::canUpdate())) {
    PluginMycustomviewProfileRights::addErrorMessage('Vous n\'avez pas les droits pour effectuer cette action');
    return false;
}

if (isset($_POST)) {
    if (isset($_POST['deleteTab'])) {
            PluginMycustomviewSavedSearch::deleteSavedSearch($_POST['deleteTab']);
    }
    if (isset($_POST['orderTab'])) {
        PluginMycustomviewSavedSearch::moveSavedSearch($_POST['orderTab']);
        PluginMycustomviewSavedSearch::reorderSavedSearch();
    }

    if (isset($_POST['screenmodeTab'])) {
        foreach ($_POST['screenmodeTab'] as $data) {
            PluginMycustomviewSavedSearch::changeScreenMode($data['id'], $data['screenmode']);
        }
    }

    if (isset($_POST['heightTab'])) {
        foreach ($_POST['heightTab'] as $data) {
            PluginMycustomviewSavedSearch::changeHeight($data['id'], $data['height']);
        }
    }
    PluginMycustomviewProfileRights::addSuccessMessage('L&apos;affichage de vos fenêtres a bien été enregistré.');
}
