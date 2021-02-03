<?php


include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (!(PluginMycustomviewProfileRights::canUpdate())) {
    Session::addMessageAfterRedirect(
        'Vous n\'avez pas les droits pour effectuer cette action',
        false,
        ERROR
    );
    echo json_encode(['success' => false]);
    Html::displayAjaxMessageAfterRedirect();
    return false;
}

if (isset($_GET['id']) && isset($_GET['screenmode'])) {
    PluginMycustomviewSavedSearch::changeScreenMode($_GET['id'], $_GET['screenmode']);

    Session::addMessageAfterRedirect(
        'La taille de votre fenêtre a bien été modifiée.',
        false,
        INFO
    );
    echo json_encode(['success' => true]);

    Html::displayAjaxMessageAfterRedirect();
}
