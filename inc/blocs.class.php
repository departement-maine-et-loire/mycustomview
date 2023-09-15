<?php
/*
 -------------------------------------------------------------------------
 MyCustomView plugin for GLPI
 Copyright (C) 2023 by the MyCustomView Development Team.

 https://github.com/pluginsGLPI/mycustomview
 -------------------------------------------------------------------------

 LICENSE

 This file is part of MyCustomView.

 MyCustomView is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 MyCustomView is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with MyCustomView. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginMycustomviewBlocs extends PluginMycustomviewSearch
{

    public static function showBlocs($max_filters)
    {
        // Limitation du nombre de caractère des colonnes (pour Description surtout) à 80 caractères
        global $CFG_GLPI;
        $theServer = $_SERVER["HTTP_REFERER"];
        $theServer = str_replace("central.php", "", $theServer);
        $CFG_GLPI['cut'] = 80;
        // Hauteur basique d'une recherche sauvegardée
        global $baseHeight;
        $baseHeight = 650;
        // Valeur de notre recherche sauvegardée, utile pour l'ID de notre DisplayPreference
        global $indexBloc;
        $indexBloc = 0;
        $userName = PluginMycustomviewMyview::getUserNameMcv();
        
        $pluginmycustomviewsavedsearch = new PluginMycustomviewSavedSearch();
        $user_settings = $pluginmycustomviewsavedsearch->getUserSettings();
        
        if(count($user_settings) != 0){
            $settings_hidden = $user_settings[0]['settings_hidden'];
            $list_limit = $user_settings[0]['list_limit'];
            $_SESSION['glpilist_limit_mcv'] = $list_limit;
            if ($user_settings[0]['default_page'] == 1) {
                $default_page = true;
            } else {
                $default_page = false;
            }
        } else {
            $settings_hidden = 0;
            $list_limit = 0;
            $default_page = false;
        }
        
        if (isset($_SESSION['session_message_norepeat'])) {
            $_SESSION['session_message_norepeat'] = false;
        }

        //AJOUT DES CSS, SCSS, SCRIPTS
        PluginMycustomviewMyview::addScriptAndStyleSheet();

        echo "<div class='fullscreen-dark-container mcv_display_none'></div>";
        echo "<div id = 'mcv_drag_drop' class='mcv_drag_drop'>";

        // ------- MODAL HELP  ----------- //
        PluginMycustomviewSavedSearch::displayHelpModal();

        // -------- MODAL CHANGEMENT DE TITRE DE RECHERCHE ----------- //
        PluginMycustomviewSavedSearch::displayChangeTitleModal();

        // ------- MODAL ANNULATION MODIFICATION ----------- //
        PluginMycustomviewSavedSearch::displayCancelModal();

        // Barre de button modifier / annuler / enregistrer / defaut
        if ($settings_hidden) {
            echo "<div class='mcv_settings'>";
        } else {
            echo "<div class='mcv_settings mcv_display_none'>";
        }

        echo "<button id='mcv_show' title='" .__("Afficher les réglages", "mycustomview") . "' class='mcv_button mcv_text_light mcv_button_basic mcv_show '><i style='margin-left: 5px' class='fas fa-cog'></i></button>";
        echo "</div>";
        if ($settings_hidden) {
            echo "<div class='mcv_manage_tab mcv_display_none'>";
        } else {
            echo "<div class='mcv_manage_tab'>";
        }
        echo "<input type='hidden' id='user-id' value='" . $_SESSION['glpiID'] . "'>";
        echo "<div class='d-flex flex-column flex-start'>";
        echo "<div class='d-flex self-flex-start m-r-auto align-start m-b-5'>";
        echo "<label for='mcv_nb_items'>" .__("Nombre d'élément par recherche", "mycustomview") . " : </label>";
        echo "<select id='mcv_nb_items' class='mcv_nb_items' name='mcv_nb_items' data-session-items-number='" . $list_limit . "'>";
        echo "<option value='5'>5</option>";
        echo "<option value='10'>10</option>";
        echo "<option value='15'>15</option>";
        echo "<option value='20'>20</option>";
        echo "<option value='25'>25</option>";
        echo "<option value='30'>30</option>";
        echo "</select>";
        echo "</div>";
        echo "<div class='d-flex self-flex-start m-r-auto align-end'>";
        if ($default_page) {
            echo "<input id ='isMcvDefault' style='z-index: 200; position: absolute; margin: -.5rem 0 0 0' name='isMcvDefault' type='checkbox' checked class='mcv_delete_default'>";
        } else {
            echo "<input id ='isMcvDefault' style='z-index: 200; position: absolute; margin: -.5rem 0 0 0' name='isMcvDefault' type='checkbox' class='mcv_add_default'>";
        }
        echo "<label for='isMcvDefault' style='z-index: 200; position: absolute; margin: -.7rem 0 0 1rem;' class='m-l-5'><b>" .__("Page par défaut", "mycustomview") . "</b></label>";
        echo "</div>";
        echo "</div>";
        echo "<button id='mcv_cancel' class='mcv_button mcv_text_light mcv_button_warning mcv_cancel mcv_display_none' ><i class='fas fa-2x fa-times'></i>" .__("Annuler", "mycustomview") . "</button>";
        echo "<button id ='mcv_edit' class='mcv_edit mcv_button mcv_text_light mcv_button_basic'><i class='fas fa-2x fa-edit'></i>" .__("Modifier", "mycustomview") . "</button>";
        echo "<button id ='mcv_help' class ='mcv_button mcv_text_light mcv_button_help mcv_help'><i class='fas fa-2x fa-question-circle'></i>" .__("Aide", "mycustomview") . "</button>";
        echo "<button id ='mcv_hide' title='" .__("Masquer les réglages", "mycustomview") . "' class ='mcv_button mcv_text_light mcv_button_basic'><i style='margin-left:5px'class='fas fa-eye-slash'></i></button>";
        echo "<button id='mcv_save' class='mcv_button mcv_text_light mcv_button_success mcv_save mcv_display_none' ><i class='fas fa-2x fa-save'></i>" .__("Enregistrer", "mycustomview") . "</button>";
        echo "</div>";
        // ------- 

        // DIV DRAG AND DROP 
        echo "<div class='mcv_tab_container'>";
        $count = PluginMycustomviewSavedSearch::countUserSavedSearchMcv();
        $maxReached = false;
        $idList = [];

        // $count = 0;

        if ($count == 0) {
            echo "<h1 class='center w-100'>" .__("Vous n'avez actuellement aucun filtre sauvegardé dans cette vue", "mycustomview") . ".</h1>";
            echo "<h2 class='center w-100'>" .__("Cliquez sur afficher la liste et ajoutez des recherches sauvegardées à votre vue", "mycustomview") . ".</h2>";
        } else {
            // ------- DEBUG SEARCH ----------- //
            PluginMycustomviewSavedSearch::delete_bugged_search();

            $liste = PluginMycustomviewSavedSearch::getUserSavedSearchMcv($max_filters);
            for ($i = 0; $i < $count; $i++) {
                
                if($i >= $max_filters){
                    break;
                }
                if ($liste) {
                    $screen_mode = isset($liste[$i]['screen_mode']) ? $liste[$i]['screen_mode'] : null;
                    $height = isset($liste[$i]['height']) ? $liste[$i]['height'] : null;
                    $dataHeight = isset($liste[$i]['height']) ? $liste[$i]['height'] : $baseHeight;
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
                    echo "<div style='margin: -10rem;' class='draggable mcv_display_none'>";
                    echo "<h3>" .__("Maintenez-moi pour me déplacer", "mycustomview") . "</h3>";
                    echo "<h3>" .__("Vous pouvez agrandir la vue en bas à droite de la fenêtre de recherche", "mycustomview") . "</h3>";
                    echo "</div>";

                    // Pour chaque SavedSearch de l'utilisateur, on va venir faire une comparaison :
                    // Si la donnée 'order' est égale à i, on vient créer un bouton supprimer et on va aller
                    // chercher en base de donnée la savedsearch en question pour l'afficher avec showList
                    // Une fois tous les affichages terminés, on va venir afficher la liste des recherches sauvegardées
                    // pour que l'utilisateur puisse en ajouter une (dernier bloc)
                    $data_order  = array_map('intval', str_split($liste[$i]['order']));

                    if ($data_order[0] == $i + 1) {
                        echo "<div class='mcv_header_edit_mode mcv_display_none'>";
                        echo "</div>";
                        echo "<button class='mcv_button mcv_text_light mcv_button_warning mcv_delete mcv_display_none' data-id-savedsearch='" . $liste[$i]['id'] . "'><i class='fas fa-2x fa-trash'></i>" .__("Supprimer", "mycustomview") . "</button>";
                        if (isset($liste[$i]['screen_mode'])) {

                            if ($liste[$i]['screen_mode'] == 0) {
                                echo "<button class='mcv_button mcv_text_light mcv_button_dark mcv_screenmode mcv_display_none' data-screenmode= '1' data-id-savedsearch='" . $liste[$i]['id'] . "'><i class='fas fa-2x fa-expand'></i><span>" .__("Fenêtre large", "mycustomview") . "</span></button>";
                            }
                            if ($liste[$i]['screen_mode'] == 1) {
                                echo "<button class='mcv_button mcv_text_light mcv_button_dark mcv_screenmode mcv_display_none' data-screenmode= '0' data-id-savedsearch='" . $liste[$i]['id'] . "'><i class='fas fa-2x fa-compress'></i><span>" .__("Fenêtre réduite", "mycustomview") . "</span></button>";
                            }
                        }


                        $list = PluginMycustomviewSavedSearch::getSavedSearchById($liste[$i]['savedsearch_id']);
                            // Ajout des ID déjà présents dans une liste
                        array_push($idList, $liste[$i]['savedsearch_id']);
                        foreach ($list as $array) {
                            $indexBloc++;
                        
                            if ($array['name'] == '') {
                                echo "<h2 style='text-align:center; padding:.5rem 0; margin: 0;'><a href='$theServer" . strtolower($array['itemtype']) . ".php?" . $array['query'] . "'>" .__("Recherche sauvegardée n°", "mycustomview") . $array['id'] . "</a>
                                <i title='" .__("Modifier le nom de cette recherche", "mycustomview") . "' class='fas fa-pen mcv_edit_title m-l-5 mcv_display_none' data-id='" . $array['id'] . "'></i></h2>";
                                
                            } else {
                                echo "<h2 style='text-align:center; padding:.5rem 0; margin: 0;'><a href='$theServer" . strtolower($array['itemtype']) . ".php?" . $array['query'] . "'>" . $array['name'] . "</a>
                                <i title='" .__("Modifier le nom de cette recherche", "mycustomview") . "' class='fas fa-pen mcv_edit_title m-l-5 mcv_display_none' data-id='" . $array['id'] . "'></i></h2>";
                            }

                            // Affichage du bouton pour les préférences
                            echo "<button class='btn btn-sm btn-icon btn-ghost-secondary show_displaypreference_modal me-0 me-sm-1' id='" . $array['itemtype'] . "'";
                            echo "title='" . __("Select default items to show") . "' data-bs-toggle='tooltip' data-bs-placement='bottom'>";
                            echo "<i class='ti fa-lg ti-tool'></i>";
                            echo "</button>";

                            parse_str($array['query'], $p);
                            unset($p['order'], $p['sort']);

                            PluginMycustomviewSearch::showListMcv($array['itemtype'], $p);
                        }
                    }
                }

                // si on arrive a $i >= $max_filters sans avoir déclenché le tableau de sauvegarde, on ajoute le JS
                if ($i >= $max_filters) {
                    $maxReached = true;
                    PluginMycustomviewSavedSearch::addSavedSearchScriptMcv();
                }

                echo "</div>";
            }
        }
        echo "</div>";
        // ------
        // si on est au nombre max de recherche, le tableau d'ajout ne s'affiche pas -> on affiche une aide a la place
        if ($maxReached) {
            PluginMycustomviewSavedSearch::maxSavedSearchReached();
        } else {
            // si l'ordre n'est pas linéaire, on vient afficher UNE seule fois le tableau pour ajouter une savedsearch
            echo "<div id='editShowList' style='display: none;' class='center w-100 p-5'>";
            echo "<div>";

            if ($indexBloc < $max_filters) {
                echo 
                "<div>
                    <button class='mcv_button mcv_text_light mcv_button_basic listToggle'>
                        <span>" .__("Afficher la liste", "mycustomview") . "</span>
                        <i class='fas fa-2x fa-chevron-down'>
                        </i>
                    </button>
                </div>";

                $hideL = __('Masquer la liste', 'mycustomview');
                $showL = __('Afficher la liste', 'mycustomview');

                // Bouton afficher / Masquer la liste pour corriger la traduction
                echo
                "
                <script>
                    // Faire apparaitre/disparaitre le tableau des recherches sauvegardées
                    $('.listToggle').on('click', function () {
                        $('.mcv_tab_limit').toggle(800);
                        if ($(this).hasClass('toggle_active')) {
                            $('span', this).html('$showL');
                            $(this).removeClass('toggle_active');
                            $('i', this).removeClass('fa-chevron-up').addClass('fa-chevron-down');
                        } else {
                            $('span', this).html('$hideL');
                            $(this).addClass('toggle_active');
                            $('i', this).removeClass('fa-chevron-down').addClass('fa-chevron-up');
                        }
                    });
                </script>
                ";
            }

            $result = PluginMycustomviewSavedSearch::getSavedSearchListMcv();
            PluginMycustomviewSavedSearch::displaySavedSearchListMcv($result, $userName, $idList);
            $i = $max_filters;
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";

        $windowS = __("Fenêtre réduite", "mycustomview");
        $windowL = __("Fenêtre large", "mycustomview");

        echo "
        <script>
            $('.mcv_screenmode').on('click', function () {
                var parentDiv = $(this).parents('.mcv_tab');
                $('#mcv_cancel').attr('data-message', 1);
            
                if (parentDiv.hasClass('w-49')) {
                    parentDiv.addClass('w-100').removeClass('w-49');
                    $('span', this).html('$windowS');
                    $('i', this).removeClass('fa-expand').addClass('fa-compress');
                    parentDiv.attr('data-screenmode', 1);
                } else if (parentDiv.hasClass('w-100')) {
                    parentDiv.addClass('w-49').removeClass('w-100');
                    $('span', this).html('$windowL');
                    $('i', this).removeClass('fa-compress').addClass('fa-expand');
                    parentDiv.attr('data-screenmode', 0);
                }
            });
        </script>
        ";

        // On recrée en dehors de la boucle l'affichage des préférences afin d'éviter de déclencher plusieurs fois le script
        echo "
        <script>
        $(document).ready(function() {
            mcv_display_prefmodals = document.querySelectorAll('.show_displaypreference_modal');
            mcv_display_prefmodals.forEach(function(mcv_display_prefmodal) {
        
                mcv_display_prefmodal.addEventListener('click', function (event) {
                    event.preventDefault();
                    mcv_itemtype = mcv_display_prefmodal.id
                    const mcv_modal_fade = document.createElement('div');
                    mcv_modal_fade.className = 'modal fade';
                    mcv_modal_fade.id = 'displayprefence_modal';
                    mcv_modal_fade.role = 'dialog';
        
                    const mcv_modal_dialog = document.createElement('div');
                    mcv_modal_dialog.className = 'modal-dialog modal-lg';
        
                    const mcv_modal_content = document.createElement('div');
                    mcv_modal_content.className = 'modal-content';
        
                    const mcv_modal_header = document.createElement('div');
                    mcv_modal_header.className = 'modal-header';
        
                    const mcv_modal_title = document.createElement('h4');
                    mcv_modal_title.className = 'modal-title';
                    mcv_modal_title.innerHTML = '" .__("Select default items to show") . "'; // <-----------------------------------------
        
                    const mcv_btn_close = document.createElement('button');
                    mcv_btn_close.setAttribute('aria-label', '" . __("Close modal") . "'); // <-----------------------------------------
                    mcv_btn_close.type = 'button';
                    mcv_btn_close.className = 'btn-close';
                    mcv_btn_close.setAttribute('data-bs-dismiss', 'modal');
        
                    const mcv_modal_body = document.createElement('div');
                    mcv_modal_body.className = 'modal-body';
        
                    const mcv_modal_ratio = document.createElement('div');
                    mcv_modal_ratio.className = 'ratio ratio-4x3';
        
                    const mcv_iframe = document.createElement('iframe');
                    mcv_iframe.setAttribute('src', 'displaypreference.form.php?itemtype=' + mcv_itemtype);
        
                    document.querySelector('body').appendChild(mcv_modal_fade)
                    mcv_modal_fade.appendChild(mcv_modal_dialog)
                    mcv_modal_dialog.appendChild(mcv_modal_content)
                    mcv_modal_content.appendChild(mcv_modal_header)
                    mcv_modal_header.appendChild(mcv_modal_title)
                    mcv_modal_header.appendChild(mcv_btn_close)
                    mcv_modal_content.appendChild(mcv_modal_body)
                    mcv_modal_body.appendChild(mcv_modal_ratio)
                    mcv_modal_ratio.appendChild(mcv_iframe)
        
                    $('#displayprefence_modal').modal('show');
                });
            })
        
            $('body').on('hide.bs.modal', '#displayprefence_modal', function() {
                location.reload();
            });
        })
        </script>
        ";
    }
}