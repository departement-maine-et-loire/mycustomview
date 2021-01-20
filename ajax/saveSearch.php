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

// if (!Session::haveRight($_SESSION['glpiactiveprofile']['id'], READ)) {
//     echo $_SESSION['glpiactiveprofile']['id'];
//     var_dump('pas le droit');
//     return false;
// }

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

        //     $_SESSION['titleMessageMcv'] = 'Ajout réussi';
        //     $_SESSION['messageMcv'] = "Votre recherche a bien été ajoutée dans la liste. Vous pourrez la retrouver en dernière position. Si vous souhaitez la déplacer, cliquez sur &quot;Modifier&quot; puis déplacez-là";
    } else {
        // $_SESSION['titleMessageMcv'] = 'Erreur lors de l&apos;ajout';
        // $_SESSION['messageMcv'] = "Impossible d&apos;ajouter votre recherche à la liste. Celle-ci est déjà présente";
        Session::addMessageAfterRedirect(
            __('Search has not been saved'),
            false,
            ERROR
        );
        echo json_encode(['success' => false]);
    }
    Html::displayAjaxMessageAfterRedirect();
}
