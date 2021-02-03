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

if (isset($_GET['default'])) {
    PluginMycustomviewSavedSearch::changeDefaultPage($_GET['default']);
    if ($_GET['default'] == 1) {
        Session::addMessageAfterRedirect(
            'Cette page est devenue votre page par défaut à la connexion.',
            false,
            INFO
        );
        echo json_encode(['success' => true]);
    } else if ($_GET['default'] == 0) {
        Session::addMessageAfterRedirect(
            'Cette page n&apos;est plus votre page par défaut à la connexion.',
            false,
            INFO
        );
    }

    Html::displayAjaxMessageAfterRedirect();
}
