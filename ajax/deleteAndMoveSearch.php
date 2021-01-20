<?php

include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if(!(PluginMycustomviewProfileRights::canUpdate())) {
    Session::addMessageAfterRedirect(
       'Vous n\'avez pas les droits pour effectuer cette action',
        false,
        ERROR
    );
    echo json_encode(['success' => false]);
    Html::displayAjaxMessageAfterRedirect();
    return false;
}




if (isset($_POST)) {
    if (isset($_POST['deleteTab'])) {
        // print_r($_POST['deleteTab']);
        foreach ($_POST['deleteTab'] as $id) {
            PluginMycustomviewSavedSearch::deleteSavedSearch($id);
        }
        
    }
    if (isset($_POST['data'])) {
        // print_r($_POST['data']);
         PluginMycustomviewSavedSearch::moveSavedSearch($_POST);
         PluginMycustomviewSavedSearch::reorderSavedSearch();
        // $_SESSION['titleMessageMcv'] = 'Déplacements enregistrés';
        // $_SESSION['messageMcv'] = "La nouvelle disposition de vos fenêtres a bien été enregistrée.";
    }

    if (isset($_POST['screenmodeTab'])) {
        // print_r($_POST['screenmodeTab']);
        foreach($_POST['screenmodeTab'] as $data) {
            PluginMycustomviewSavedSearch::changeScreenMode($data['id'], $data['screenmode']);
        }
        
    }


    Session::addMessageAfterRedirect(
        'L&apos;affichage de vos fenêtres a bien été enregistré.',
        false,
        INFO
    );
    echo json_encode(['success' => true]);

    Html::displayAjaxMessageAfterRedirect();
}
