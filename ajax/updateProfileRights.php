<?php

include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (!(PluginMycustomviewProfileRights::isSuperAdmin())) {
    Session::addMessageAfterRedirect(
        'Vous n\'avez pas les droits pour effectuer cette action',
        false,
        ERROR
    );
    echo json_encode(['success' => false]);
    Html::displayAjaxMessageAfterRedirect();
    return false;
}

if (isset($_POST['data'])) {
    if(PluginMycustomviewProfileRights::updateProfile($_POST['data'])){

    Session::addMessageAfterRedirect(
        'Les droits du profil ont bien été modifiés.',
        false,
        INFO
    );
    echo json_encode(['success' => true]); 
}
else {
    Session::addMessageAfterRedirect(
        'Impossible de modifier le super-admin',
        false,
        ERROR
    );
    echo json_encode(['success' => false]);
    Html::displayAjaxMessageAfterRedirect();
    return false;
}
} else {
    Session::addMessageAfterRedirect(
        'Une erreur est survenue lors de la modification',
        false,
        ERROR
    );
    echo json_encode(['success' => false]);
}

Html::displayAjaxMessageAfterRedirect();
