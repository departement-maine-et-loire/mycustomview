<?php


include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (!(PluginMycustomviewProfileRights::canUpdate())) {
    PluginMycustomviewProfileRights::addErrorMessage('Vous n\'avez pas les droits pour effectuer cette action');
    return false;
}

if (isset($_GET['hidden'])) {
    PluginMycustomviewSavedSearch::changeSettingsVisibility($_GET['hidden']);
    if(isset($_SESSION['session_message_norepeat'])) {
        if (!($_SESSION['session_message_norepeat'])) {
            PluginMycustomviewProfileRights::addSuccessMessage('Vos paramètres ont bien été modifiés.');
            $_SESSION['session_message_norepeat'] = true;
        }
    }
    else {
        PluginMycustomviewProfileRights::addSuccessMessage('Vos paramètres ont bien été modifiés.');
        $_SESSION['session_message_norepeat'] = true;
    }
    
}
