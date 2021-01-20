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
 
if(isset($_GET['default'])) {
    PluginMycustomviewSavedSearch::changeDefaultPage($_GET['default']);
    // $_SESSION['titleMessageMcv'] = 'Page par défaut';
    if($_GET['default'] == 1){
        Session::addMessageAfterRedirect(
            'Cette page est devenue votre page par défaut à la connexion.',
            false,
            INFO
        );
        echo json_encode(['success' => true]);
        // $_SESSION['messageMcv'] = "La page &quot;Ma vue personnalisée&quot; est devenue la page par défaut à la connexion. ";
    }
    else if($_GET['default'] == 0) {
            Session::addMessageAfterRedirect(
                'Cette page n&apos;est plus votre page par défaut à la connexion.',
                false,
                INFO
            );
        // $_SESSION['messageMcv'] = "La page &quot;Ma vue personnalisée&quot; n&apos;est plus votre page par défaut à la connexion. ";
    }

    Html::displayAjaxMessageAfterRedirect();


}
