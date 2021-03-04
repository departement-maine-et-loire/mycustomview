<?php

include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (!(PluginMycustomviewProfileRights::isSuperAdmin())) {
    PluginMycustomviewProfileRights::addErrorMessage('Vous n\'avez pas les droits pour effectuer cette action');
    return false;
}

if (isset($_POST['data'])) {
    if(PluginMycustomviewProfileRights::updateProfile($_POST['data'])){
        PluginMycustomviewProfileRights::addSuccessMessage('Les droits du profil ont bien été modifiés.');

}
else {
    PluginMycustomviewProfileRights::addErrorMessage('Impossible de modifier le super-admin');
    return false;
}
} else {
    PluginMycustomviewProfileRights::addErrorMessage( 'Une erreur est survenue lors de la modification');
}

