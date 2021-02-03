<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginMycustomviewBlocs extends PluginMycustomviewSearch
{

    public function showBlocs($max_filters)
    {
        // Limitation du nombre de caractère des colonnes (pour Description surtout) à 80 caractères
        global $CFG_GLPI;
        $CFG_GLPI['cut'] = 80;

        global $indexBloc;
        $indexBloc = 0;
        $search = new PluginMycustomviewSearch();
        $savedsearch = new PluginMycustomviewSavedSearch();
        $userName = PluginMycustomviewMyview::getUserNameMcv();

        echo Html::css("/plugins/mycustomview/css/theme.default.min.css");
        echo Html::css("/plugins/mycustomview/css/flexslider.css");
        echo Html::Scss("/plugins/mycustomview/css/mycustomview.scss");
        echo Html::script("/plugins/mycustomview/js/jquery.dad.js");
        echo Html::script("/plugins/mycustomview/js/jquery.tablesorter.min.js");
        echo Html::script("/plugins/mycustomview/js/jquery.tablesorter.widgets.min.js");
        echo Html::script("/plugins/mycustomview/js/jquery.flexslider.js");
        echo Html::script("/plugins/mycustomview/js/mycustomview.js");


        echo "<div class='fullscreen-dark-container mcv_display_none'></div>";
        echo "<div id = 'mcv_drag_drop' class='mcv_drag_drop'>";

        // ------- MODAL HELP  ----------- //

        echo "<div class='mcv_modal mcv_modal_bg_light mcv_modal_large mcv_modal_help mcv_display_none'>";
        echo "<button title='Fermer' class='mcv_modal_close p-none mcv_button_basic'><i class='fas fa-3x fa-window-close' data-modaltype='help' aria-hidden='true'></i></button>";
        echo "<div class='mcv_modal_bg_basic mcv_text_light mcv_modal_header'>";
        echo "<h2 class='text-center m-auto mcv_modal_header_text'>Comment ça marche ?</h2>";
        echo "</div>";

        // ---------- SLIDER ---------- //
        echo " <div class='flexslider'>";
        echo " <ul class='slides'>";
        echo "<li>";

        echo "<div class='p-5'>";
        echo "<p class='subtitle_modal m-5'>Etape 1 (Prérequis) : Création d'une ou plusieurs Recherche(s) sauvegardée(s)</p><br>";
        echo "<p><span class='font-bigger'>Votre onglet <b>« Ma vue personnalisée »</b> fait appel à la fonctionnalité de <b>« Recherches sauvegardées »</b> (équivalent à se créer des filtres personnalisés).</span><br><br>";
        echo "<u>Suivre la procédure ci-dessous pour créer une (ou plusieurs) recherches :<br><br></u>";
        echo "•	Dans le menu <b>Assistance / Tickets</b>, effectuez une nouvelle recherche en positionnant les filtres souhaités puis cliquez sur <b>« Rechercher »</b>. Il vous reste à sauvegarder votre recherche en cliquant sur le symbole en étoile :";
        echo "</p></div>";
        echo "  <img src='../plugins/mycustomview/images/img1.png' class='w-auto m-auto p-3 flexImages'/>";
        echo "</li>";

        echo "<li>";
        echo "<div class='p-5'><p>";
        echo "•	Nommez votre recherche et cliquez sur <b>Ajouter</b> :";
        echo "</p></div>";
        echo "  <img src='../plugins/mycustomview/images/img2.png' class='w-auto m-auto maxw-50 p-3 flexImages'/>";

        echo "<div class='p-5'><p>";
        echo "•	 Vous avez la possibilité de faire des recherches sur d’autres objets GLPI comme les ordinateurs (Menu <b>Parc / Ordinateurs</b>) :";
        echo "</p></div>";
        echo "  <img src='../plugins/mycustomview/images/img3.png' class='w-auto maxw-50 m-auto p-3 flexImages'/>";
        echo "  </li>";
        echo " <li>";
        echo "<div class='p-5'>";
        echo "<p class='subtitle_modal m-5'>Etape 2 : Construire sa vue personnalisée</p><br>";
        echo "<p>•	Dans la page d’accueil, cliquez sur l’onglet <b>« Ma vue personnalisée »</b>. Par défaut aucune recherche s’affiche :";
        echo "</p></div>";
        echo "  <img src='../plugins/mycustomview/images/img4.png' class='w-auto maxw-80 m-auto p-3 flexImages'/>";
        echo "<div class='p-5'><p>";
        echo "•	  Cliquez sur le symbole <b>« + »</b> face à la recherche sauvegardée que vous souhaitez afficher. Une fois sélectionnée, celle-ci s’affiche dans votre vue et est indiquée comme <b>« Déjà ajoutée »</b>";
        echo "</p></div>";
        echo "  <img src='../plugins/mycustomview/images/img5.png' class='w-auto maxw-80 m-auto p-3 flexImages'/>";
        echo "  <img src='../plugins/mycustomview/images/img6.png' class='w-auto maxw-80 m-auto p-3 flexImages'/>";
        echo "  </li>";

        echo "<li>";
        echo "<div class='p-5'>";
        echo "<p>•	Une fois vos recherches ajoutées, vous avez la possibilité en cliquant sur le bouton <b>« Modifier »</b> de :<br><br>
            -	Réorganiser vos fenêtres de recherche via un cliquer-Glisser :";
        echo "</p></div>";
        echo "  <img src='../plugins/mycustomview/images/img7.png' class='w-auto maxw-45 m-auto p-3 flexImages'/>";
        echo "</li>";

        echo "<li>";
        echo "<div class='p-5'><p>";
        echo "-  D’étendre ou de réduire votre fenêtre de recherche en utilisant les boutons « Fenêtre Large » ou « Fenêtre Réduite » :";
        echo "</p></div>";
        echo "  <img src='../plugins/mycustomview/images/img8.png' class='w-auto maxw-40 m-auto flexImages'/>";
        echo "<div class='p-5'><p>";
        echo "-  De renommer votre recherche en cliquant sur le symbole représentant un crayon :";
        echo "</p></div>";
        echo "  <img src='../plugins/mycustomview/images/img9.png' class='w-auto maxw-35 m-auto m-b-5 flexImages'/>";
        echo "<div class='p-5'><p>";
        echo "-  De supprimer de votre vue une recherche en cliquant sur le bouton <b>« Supprimer » </b>:";
        echo "</p></div>";
        echo "  <img src='../plugins/mycustomview/images/img10.png' class='w-auto maxw-10 m-auto flexImages'/>";
        echo "<div class='p-5'><p>";
        echo "<span style='color: red'><b>IMPORTANT</b></span> : Une fois vos modifications effectuées, enregistrez-les en utilisant en cliquant sur le bouton <b>« Enregistrer »</b> :";
        echo "</p></div>";
        echo "  <img src='../plugins/mycustomview/images/img11.png' class='w-auto maxw-10 m-auto flexImages'/>";
        echo " </li>";

        echo "<li>";
        echo "<div class='p-5'>";
        echo "<p class='subtitle_modal m-5'>Etape 3 : Personnaliser les champs visibles dans votre tableau</p><br>";
        echo "<p>•	Cliquez sur le symbole « Clef » pour sélectionner les champs souhaités :</b>";
        echo "</p></div>";
        echo "  <img src='../plugins/mycustomview/images/img12.png' class='w-auto maxw-40 m-auto p-3 flexImages'/>";
        echo "<div class='p-5'><p>";
        echo "•	Sélectionnez le/les champ(s) à afficher dans la fenêtre qui s’affiche puis validez en cliquant sur <b>« Ajouter »</b> : ";
        echo "</p></div>";
        echo "  <img src='../plugins/mycustomview/images/img13.png' class='w-auto maxw-60 m-auto p-3 flexImages'/>";
        echo " </li>";
        echo " <li>";
        echo "<div class='p-5'>";
        echo "<p class='subtitle_modal m-5'>Etape 4 : Ajouter l’onglet « Ma vue Personnalisée » comme page d’accueil</p><br>";
        echo "<p>•	Cochez la case « Page par défaut » si vous souhaitez avoir l’onglet « Ma vue personnalisée » comme page par défaut lors de vos prochaines connexions</b>";
        echo "</p></div>";
        echo "  <img src='../plugins/mycustomview/images/img14.png' class='w-auto maxw-100 m-auto p-3 flexImages'/>";
        echo "  </li>";
        echo " </ul>";
        echo "</div>";
        // ------------------------------ //
        echo "<div class='mcv_modal_footer d-flex flex-end'>";
        echo "</div>";
        echo "</div>";

        // -------- MODAL CHANGEMENT DE TITRE DE RECHERCHE ----------- //
        echo "<div class='mcv_modal mcv_modal_bg_light mcv_modal_very_small mcv_modal_edit_title mcv_display_none'>";
        echo "<button title='Fermer' class='mcv_modal_close p-none mcv_button_basic'><i class='fas fa-3x fa-window-close' data-modaltype='help' aria-hidden='true'></i></button>";
        echo "<div class='mcv_modal_bg_basic mcv_text_light mcv_modal_header'>";
        echo "<h2 class='text-center m-auto mcv_modal_header_text'>Modification du titre</h2>";
        echo "</div>";
        echo "<h3 class='mcv_modal_text_edit_title'>Saisissez le nouveau titre de cette recherche</h3>";
        echo "<input type='hidden' name='id_savedsearch'></input>";
        echo "<input type='text' class='savedsearch_title'></input>";

        echo "<button class='mcv_button mcv_button_success mcv_text_light mcv_change_title'>Valider</button>";
        echo "</div>";


        // ------- MODAL ANNULATION MODIFICATION ----------- //

        echo "<div class='mcv_modal mcv_modal_bg_light mcv_modal_very_small mcv_cancel_message mcv_display_none'>";
        echo "<button title='Fermer' class='mcv_modal_close p-none mcv_button_basic'><i class='fas fa-3x fa-window-close' data-modaltype='help' aria-hidden='true'></i></button>";
        echo "<div class='mcv_modal_bg_basic mcv_text_light mcv_modal_header'>";
        echo "<h2 class='text-center m-auto mcv_modal_header_text'>Attention</h2>";
        echo "</div>";
        echo "<p class='mcv_text_dark mcv_message_text text-center font-bigger'>Vous allez abandonner vos modifications (déplacements/suppressions de fenêtres).</br>Êtes-vous sûr de confirmer ?";
        echo "</p>";
        echo "<div class='mcv_modal_footer d-flex flex-end'>";
        echo "<button class='mcv_button mcv_button_dark mcv_text_light mcv_button_dark mcv_button_popup mcv_button_message_cancel'>Confirmer</button>";
        echo "</div>";
        echo "</div>";
        echo "</div>";

        // Barre de button modifier / annuler / enregistrer / defaut
        echo "<div class='mcv_manage_tab'>";

        echo "<div class='d-flex self-flex-start m-r-auto align-end'>";
        if (PluginMycustomviewSavedSearch::isDefaultPageOfUser()) {
            echo "<input name='isMcvDefault' type='checkbox' checked class='mcv_delete_default'>";
        } else {
            echo "<input name='isMcvDefault' type='checkbox' class='mcv_add_default'>";
        }
        echo "<b><label for='isMcvDefault' class='m-l-5'>Page par défaut</label></b>";
        echo "</div>";
        echo "<button id='mcv_cancel' class='mcv_button mcv_text_light mcv_button_warning mcv_cancel mcv_display_none' ><i class='fas fa-2x fa-times'></i>Annuler</button>";
        echo "<button id ='mcv_edit' class='mcv_edit mcv_button mcv_text_light mcv_button_basic'><i class='fas fa-2x fa-edit'></i>Modifier</button>";
        echo "<button id ='mcv_help' class ='mcv_button mcv_text_light mcv_button_help mcv_help mcv_help'><i class='fas fa-2x fa-question-circle'></i>Aide</button>";
        echo "<button id='mcv_save' class='mcv_button mcv_text_light mcv_button_success mcv_save mcv_display_none' ><i class='fas fa-2x fa-save'></i>Enregistrer</button>";
        echo "</div>";
        // ------- 

        // DIV DRAG AND DROP 
        echo "<div class='mcv_tab_container'>";
        $count = $savedsearch->countUserSavedSearchMcv();
        $maxReached = false;
        $idList = [];
        if ($count == 0) {
            echo "<h2 class='center w-100' >Vous n'avez actuellement aucun filtre sauvegardé dans cette vue.</h2>";
        } else {
            for ($i = 1; $i <= $count; $i++) {
                if ($liste = $savedsearch->getUserSavedSearchMcv($max_filters)) {
                    $screen_mode = isset($liste[$i - 1]['screen_mode']) ? $liste[$i - 1]['screen_mode'] : null;
                    if (isset($screen_mode)) {
                        if ($screen_mode == 0) {
                            $screen_mode_class = 'w-49';
                        }
                        if ($screen_mode == 1) {
                            $screen_mode_class = 'w-100';
                        }
                    } else {
                        $screen_mode_class = 'w-49';
                    }
                    echo " <div class='mcv_tab mcv_movable_items " . $screen_mode_class . "' data-number=" . $i . ">";
                    echo "<div class='mcv_transparent_view mcv_display_none'></div>";
                    // echo "<div class='mcv_absolute'>";
                    echo "<div class='draggable mcv_display_none'>";
                    echo "<h3>Maintenez-moi pour me déplacer</h3>";
                    // echo "</div>";
                    echo "</div>";

                    // Pour chaque SavedSearch de l'utilisateur, on va venir faire une comparaison :
                    // Si la donnée 'order' est égale à i, on vient créer un bouton supprimer et on va aller
                    // chercher en base de donnée la savedsearch en question pour l'afficher avec showList
                    // Une fois tous les affichages terminés, on va venir afficher la liste des recherches sauvegardées
                    // pour que l'utilisateur puisse en ajouter une (dernier bloc)

                    foreach ($liste as $data) {

                        if ($data['order'] == $i) {
                            echo "<div class='mcv_header_edit_mode mcv_display_none'>";
                            echo "</div>";
                            echo "<button class='mcv_button mcv_text_light mcv_button_warning mcv_delete mcv_display_none' data-id-savedsearch='" . $data['id'] . "'><i class='fas fa-2x fa-trash'></i>Supprimer</button>";
                            if (isset($data['screen_mode'])) {

                                if ($data['screen_mode'] == 0) {
                                    echo "<button class='mcv_button mcv_text_light mcv_button_dark mcv_screenmode mcv_display_none' data-screenmode= '1' data-id-savedsearch='" . $data['id'] . "'><i class='fas fa-2x fa-expand'></i><span>Fenêtre large</span></button>";
                                }
                                if ($data['screen_mode'] == 1) {
                                    echo "<button class='mcv_button mcv_text_light mcv_button_dark mcv_screenmode mcv_display_none' data-screenmode= '0' data-id-savedsearch='" . $data['id'] . "'><i class='fas fa-2x fa-compress'></i><span>Fenêtre réduite</span></button>";
                                }
                            }


                            $list = $savedsearch->getSavedSearchById($data['savedsearch_id']);
                            // Ajout des ID déjà présents dans une liste
                            array_push($idList, $data['savedsearch_id']);

                            foreach ($list as $array) {
                                $indexBloc++;
                                if ($array['name'] == '') {
                                    echo "<h2>Recherche sauvegardée n°" . $array['id'] . "</h2>";
                                    echo "<i title='Modifier le nom de cette recherche' class='fas fa-pen mcv_edit_title m-l-5 mcv_display_none' data-id='" . $array['id'] . "'></i>";
                                } else {
                                    echo "<div class='m-10 mcv_title_savedsearch'><a href='/front/" . strtolower($array['itemtype']) . ".php?" . $array['query'] . "' class='mcv_font_large'>" . $array['name'] . "</a>";
                                    echo "<i title='Modifier le nom de cette recherche' class='fas fa-pen mcv_edit_title m-l-5 mcv_display_none' data-id='" . $array['id'] . "'></i>";
                                    echo "</div>";
                                }
                                parse_str($array['query'], $p);
                                $search->showListMcv($array['itemtype'], $p);
                            }
                        }
                    }
                    // si on arrive a $i >= $max_filters sans avoir déclanché le tableau de sauvegarde, on ajoute le JS
                    if ($i >= $max_filters) {
                        $maxReached = true;
                        $savedsearch->addSavedSearchScriptMcv();
                    }
                } else {
                }
                echo "</div>";
            }
        }
        echo "</div>";
        // ------
        // si on est au nombre max de recherche, le tableau d'ajout ne s'affiche pas -> on affiche une aide a la place
        if ($maxReached) {
            $savedsearch->maxSavedSearchReached();
        } else {
            // si l'ordre n'est pas linéaire, on vient afficher UNE seule fois le tableau pour ajouter une savedsearch
            echo "<div class='center w-100 p-5'>";
            echo "<div>";
            echo "<div><button class='mcv_button mcv_text_light mcv_button_basic listToggle'><span>Afficher la liste</span><i class='fas fa-2x fa-chevron-down'></i></button></div>";
            $result = $savedsearch->getSavedSearchListMcv();
            $savedsearch->displaySavedSearchListMcv($result, $userName, $idList);
            $i = $max_filters;
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";
    }
}
