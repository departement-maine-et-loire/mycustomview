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

        if(isset($_SESSION['session_message_norepeat'])) {
            $_SESSION['session_message_norepeat'] = false;
        }

        echo Html::css("/plugins/mycustomview/css/flexslider.css");
        echo Html::Scss("/plugins/mycustomview/css/mycustomview.scss");
        echo Html::script("/plugins/mycustomview/js/jquery.dad.js");
        echo Html::script("/plugins/mycustomview/js/jquery.tablesorter.min.js");
        echo Html::script("/plugins/mycustomview/js/jquery.tablesorter.widgets.min.js");
        echo Html::script("/plugins/mycustomview/js/jquery.flexslider.js");
        echo Html::script("/plugins/mycustomview/js/mycustomview.js");
        echo Html::css("/plugins/mycustomview/css/theme.default.min.css");

        echo "<div class='fullscreen-dark-container mcv_display_none'></div>";
        echo "<div id = 'mcv_drag_drop' class='mcv_drag_drop'>";

        // ------- MODAL HELP  ----------- //

        PluginMycustomviewSavedSearch::displayHelpModal();

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
        $settings_hidden = PluginMycustomviewSavedSearch::areSettingsHidden();

        if ($settings_hidden) {
            echo "<div class='mcv_settings'>";
        }
        else {
            echo "<div class='mcv_settings mcv_display_none'>";
        }
        
        echo "<button id='mcv_show' title='Afficher les réglages' class='mcv_button mcv_text_light mcv_button_basic mcv_show '><i style='margin-left: 5px' class='fas fa-cog'></i></button>";
        echo "</div>";
        if ($settings_hidden) {
            echo "<div class='mcv_manage_tab mcv_display_none'>";
        }
        else {
            echo "<div class='mcv_manage_tab'>";
        }
        echo "<input type='hidden' id='user-id' value='" . $_SESSION['glpiID'] . "'>";
        echo "<div class='d-flex flex-column flex-start'>";
        echo "<div class='d-flex self-flex-start m-r-auto align-start m-b-5'>";
        $sessionItemsNumber = isset($_SESSION['glpilist_limit_mcv']) ? $_SESSION['glpilist_limit_mcv'] : 10;
        echo "<label for='mcv_nb_items'>Nombre d'élément par recherche : </label>";
        echo "<select id='mcv_nb_items' class='mcv_nb_items' name='mcv_nb_items' data-session-items-number='" . $sessionItemsNumber . "'>";
        echo "<option value='5'>5</option>";
        echo "<option value='10'>10</option>";
        echo "<option value='15'>15</option>";
        echo "<option value='20'>20</option>";
        echo "<option value='25'>25</option>";
        echo "<option value='30'>30</option>";
        echo "</select>";
        echo "</div>";
        echo "<div class='d-flex self-flex-start m-r-auto align-end'>";
        if (PluginMycustomviewSavedSearch::isDefaultPageOfUser()) {
            echo "<input id ='isMcvDefault' name='isMcvDefault' type='checkbox' checked class='mcv_delete_default'>";
        } else {
            echo "<input id ='isMcvDefault' name='isMcvDefault' type='checkbox' class='mcv_add_default'>";
        }
        echo "<b><label for='isMcvDefault' class='m-l-5'>Page par défaut</label></b>";
        echo "</div>";
        echo "</div>";
        echo "<button id='mcv_cancel' class='mcv_button mcv_text_light mcv_button_warning mcv_cancel mcv_display_none' ><i class='fas fa-2x fa-times'></i>Annuler</button>";
        echo "<button id ='mcv_edit' class='mcv_edit mcv_button mcv_text_light mcv_button_basic'><i class='fas fa-2x fa-edit'></i>Modifier</button>";
        echo "<button id ='mcv_help' class ='mcv_button mcv_text_light mcv_button_help mcv_help'><i class='fas fa-2x fa-question-circle'></i>Aide</button>";
        echo "<button id ='mcv_hide' title='Masquer les réglages' class ='mcv_button mcv_text_light mcv_button_basic'><i style='margin-left:5px'class='fas fa-eye-slash'></i></button>";
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
                    $height = isset($liste[$i - 1]['height']) ? $liste[$i - 1]['height'] : null;
                    $dataHeight = isset($liste[$i - 1]['height']) ? $liste[$i - 1]['height'] : 650;
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
                    if ($height !== null) {
                        echo " <div class='mcv_tab mcv_movable_items " . $screen_mode_class . "' data-number=" . $i . " style ='height:" . $height . "px' data-height='" . $dataHeight . "'>";
                    } else {
                        echo " <div class='mcv_tab mcv_movable_items " . $screen_mode_class . "' data-number=" . $i . ">";
                    }
                    echo "<div class='mcv_transparent_view mcv_display_none'></div>";
                    echo "<div class='draggable mcv_display_none'>";
                    echo "<h3>Maintenez-moi pour me déplacer</h3>";
                    echo "<h3>Vous pouvez agrandir la vue en bas à droite de la fenêtre de recherche</h3>";
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
