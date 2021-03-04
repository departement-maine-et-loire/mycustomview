<?php

include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();


if (!(PluginMycustomviewProfileRights::canUpdate())) {
    PluginMycustomviewProfileRights::addErrorMessage('Vous n\'avez pas les droits pour effectuer cette action');
    return false;
}

if (isset($_GET)) {
    $id = $_GET['id'];
    $user = $_GET['user'];
    $order = $_GET['order'];
    $savedsearch = new PluginMycustomviewSavedSearch();
    $isUnique = $savedsearch->checkUnicitySavedSearch($id);
    if ($isUnique) {
        // $savedsearch->addSavedSearch($id, $user, $order);
        if ($savedsearch->addSavedSearch($id, $user, $order)) {
            Session::addMessageAfterRedirect(
                __('Search has been saved'),
                false,
                INFO
            );
            echo json_encode(['success' => true]);
        } else {
            Session::addMessageAfterRedirect(
                __('Search has not been saved'),
                false,
                ERROR
            );
            echo json_encode(['success' => false]);
            Html::displayAjaxMessageAfterRedirect();
            return false;
        }
    } else {
        Session::addMessageAfterRedirect(
            __('Search has not been saved'),
            false,
            ERROR
        );
        echo json_encode(['success' => false]);
    }
    Html::displayAjaxMessageAfterRedirect();
}
