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

class PluginMycustomviewSavedSearch extends SavedSearch
{
    /**
     * Récupère la liste de toutes les recherches sauvegardées par l'utilisateur
     *
     * @return $result (data from DB)
     **/
    static function getSavedSearchListMcv()
    {
        global $DB;

        $id_user = Session::getLoginUserID();

        $query = "SELECT GS.id, name, GS.itemtype, GS.users_id, is_private, query
        FROM glpi_savedsearches GS
        LEFT JOIN glpi_savedsearches_users GSU ON GS.id = GSU.savedsearches_id
        WHERE GS.users_id = $id_user
        OR GS.is_private = 0
        LIMIT 50";

        $result = $DB->query($query);

        $array = array();

        foreach ($result as $data){
            array_push($array, $data);
        }

        return $array;
    }

    /**
     * Permet de récupérer toutes les recherches contenues dans le plugin mycustomview
     *
     * 
     * @return array 
     **/
    public static function debug_search()
    {
        global $DB;

        $id_user = Session::getLoginUserID();

        $query = "SELECT *
        FROM glpi_plugin_mycustomview_savedsearch_list
        WHERE user_id = $id_user
        ORDER BY `order` ASC";

        $result = $DB->query($query);

        $array = array();

        foreach ($result as $data){
            array_push($array, $data);
        }

        return $array;
    }

    /**
     * Permet de récupérer toutes les recherches de l'utilisateur
     *
     * 
     * @return array 
     **/
    public static function getAllSearches()
    {
        global $DB;

        $id_user = Session::getLoginUserID();

        $query = "SELECT *
        FROM glpi_savedsearches
        WHERE users_id = $id_user";

        $result = $DB->query($query);

        $array = array();

        foreach ($result as $data) {
            array_push($array, $data['id']);
        }

        return $array;
    }

    /**
    * Affiche la liste des recherches sauvegardées de l'utilisateur
    *
    * @return array
    */
    public static function getAllUserSavedSearchMcv()
    {
        global $DB;

        $id_user = Session::getLoginUserID();

        $query = "SELECT *
        FROM glpi_plugin_mycustomview_savedsearch_list
        WHERE user_id = $id_user";

        $result = $DB->query($query);

        $array = array();

        foreach ($result as $data) {
            array_push($array, $data);
        }

        return $array;
    }

    /**
     * Permet de corriger les recherches si besoin
     *
     * 
     * @return void
     **/
    public static function delete_bugged_search()
    {
        global $DB;

        $getAllUserSavedSearchMcv = self::getAllUserSavedSearchMcv();
        $allSearches = self::getAllSearches();
        $array = self::debug_search();

        $ndArray = [];

        foreach ($getAllUserSavedSearchMcv as $savedSearchId) {
            array_push($ndArray, $savedSearchId['savedsearch_id']);
        }

        // Supprime les recherches toujours enregistrées dans le plugin si elles ne font plus parties de GLPI
        foreach ($ndArray as $searches) {
            if (!in_array($searches, $allSearches)) {
                $DB->delete('glpi_plugin_mycustomview_savedsearch_list', 
                    [
                        'savedsearch_id' => $searches
                    ]
                );
            }
        }

        $arraytemp = array();
        $arraytempnok = array();

        // Vérifie que l'ordre est toujours linéaire
        foreach ($array as $test) {
            if(!in_array($test['order'], $arraytemp)) {
                array_push($arraytemp, $test['order']);
            } else {
                array_push($arraytempnok, $test['id']);
            }
        }

        // Supprime les recherches qui ont le même ordre dans le plugin
        foreach ($arraytempnok as $debug) {
            $DB->delete('glpi_plugin_mycustomview_savedsearch_list', 
                [
                    'id' => $debug
                ]
            );
        }

        $debug_search = self::debug_search();

        // Réapplique un ordre linéaire sur les recherches sauvegardées
        for ($i = 1; $i <= count($debug_search); $i++) {
            $a = $i - 1;

            if ($debug_search[$a]['order'] != $i) {
                $debug = $debug_search[$a]['id'];
                
                $DB->update('glpi_plugin_mycustomview_savedsearch_list', 
                    [
                        'order'      => $i
                    ], [
                        'id' => $debug
                    ]
                );
            }
        }
    }

    static function getSavedSearchById($id)
    {
        global $DB;

        $query = "SELECT *
        FROM glpi_savedsearches
        WHERE id = $id";

        $result = $DB->query($query);

        $array = array();

        foreach ($result as $data){
            array_push($array, $data);
        }

        return $array;
    }

    /**
     * Récupère la liste des recherches sauvegardées par l'utilisateur
     *
     * @return $result (Objet de DB)
     **/
    public static function getUserSavedSearchMcv()
    {
        global $DB;
        $id_user = Session::getLoginUserID();

        $query = "SELECT McvSL.id, McvSL.user_id, McvSL.savedsearch_id, McvSL.order, McvSL.screen_mode, McvSL.height
        FROM glpi_plugin_mycustomview_savedsearch_list McvSL
        LEFT JOIN glpi_savedsearches GS ON McvSL.savedsearch_id = GS.id
        WHERE user_id = $id_user
        AND GS.id IS NOT NULL
        ORDER BY `order` ASC";

        $result = $DB->query($query);

        $array = array();

        foreach ($result as $data) {
            array_push($array, $data);
        }

        return $array;
    }

    public static function countUserSavedSearchMcv()
    {
        global $DB;

        $id_user = Session::getLoginUserID();

        $query = "SELECT COUNT(*) as cpt
        FROM glpi_plugin_mycustomview_savedsearch_list McvL
        LEFT JOIN glpi_savedsearches GS ON McvL.savedsearch_id = GS.id
        WHERE user_id = $id_user
        AND GS.id IS NOT null";

        $result = $DB->query($query);

        foreach ($result as $data){
            return $data['cpt'];
        }
    }

    /**
     * Ajoute une savedSearch dans la liste de l'utilisateur la base de données
     *
     * @return void
     **/
    public static function addSavedSearch($id, $user, $order)
    {
        global $DB;

        $max_filters = PluginMycustomviewConfig::getMaxFilters();
        $check_max_filters = self::check_max_filters();
        $limit_filters = $check_max_filters['order'];

        if ($limit_filters >= $max_filters) {
            Session::addMessageAfterRedirect(
                __(
                    "La recherche n'a pas été sauvegardée",
                    "mycustomview"
                ),
                true,
                ERROR
            );
            return false;
        } else {
            $id_user = Session::getLoginUserID();

            $query = "SELECT COUNT(*) as tot
            FROM glpi_plugin_mycustomview_savedsearch_list
            WHERE user_id = $id_user";

            $result = $DB->query($query);

            foreach ($result as $data) {
                if ($data['tot'] != 0) {
                    $order = $order + 1;
                }
            }

            $insert_query = $DB->buildInsert('glpi_plugin_mycustomview_savedsearch_list',
                [
                    'user_id'      => new QueryParam(),
                    'savedsearch_id'  => new QueryParam(),
                    'order'      => new QueryParam(),
                    'screen_mode' => 0,
                    'height' => 650
                ]
            );
            
            $stmt = $DB->prepare($insert_query);
            $stmt->bind_param(
                'iii',
                $user,
                $id,
                $order
            );

            $stmt->execute();

            return $stmt;
        }
    }

    public static function checkUnicitySavedSearch($id)
    {
        $result = self::getUserSavedSearchMcv();

        foreach ($result as $data) {
            if ($data['savedsearch_id'] == $id) {
                return false;
            }
        }

        return true;
    }

    /**
     * Supprime une savedSearch de la liste de l'utilisateur de la base de données
     *
     * @return void
     */
    public static function deleteSavedSearch($delete_tabs)
    {
        global $DB;

        $delete_query = $DB->buildDelete('glpi_plugin_mycustomview_savedsearch_list',
            [
                'id'      => new QueryParam()
            ]
        );

        $stmt = $DB->prepare($delete_query);

        foreach ($delete_tabs as $row) {
            $stmt->bind_param(
                'i',
                $row
            );

            $stmt->execute();
        }
    }

    /**
     * Réorganise les valeurs 'order' de la liste d'un utilisateur
     *
     * @return void
     **/
    public static function reorderSavedSearch()
    {
        global $DB;

        $id_user = Session::getLoginUserID();

        $query = "SELECT *
        FROM glpi_plugin_mycustomview_savedsearch_list
        WHERE user_id = $id_user
        ORDER BY `order` ASC";

        $result = $DB->query($query);

        $i = 1;

        foreach ($result as $liste) {
            if ($liste['order'] == $i) {
                $i++;
            } else {
                $newOrder = $liste['order'];

                while ($newOrder != $i) {
                    $newOrder = $newOrder - 1;
                }

                $DB->update('glpi_plugin_mycustomview_savedsearch_list',
                    [
                        'order'      => $newOrder,
                    ],
                    [
                        'id' => $liste['id']
                    ]
                );

                $i++;
            }
        }
    }

    public static function moveSavedSearch($order_tabs)
    {
        global $DB;

        $update_query = $DB->buildUpdate('glpi_plugin_mycustomview_savedsearch_list',
            [
                'order'      => new QueryParam(),
            ],
            [
                'id' => new QueryParam()
            ]
        );

        $stmt = $DB->prepare($update_query);

        foreach ($order_tabs as $row) {
            $stmt->bind_param(
                'ii',
                $row['order'],
                $row['id']
            );

            $stmt->execute();
        }
    }

    public static function isUserInSettingsMcv()
    {
        global $DB;

        $table = 'glpi_plugin_mycustomview_user_settings';

        $result = $DB->request([
            'SELECT' =>
            "$table.*",
            'FROM' => $table,
            'WHERE' => ["$table.user_id"    => Session::getLoginUserID()]
        ]);

        if (count($result)) {
            return $result;
        } else {
            return false;
        }
    }

    public static function getUserSettings(){
        global $DB;

        $id_user = Session::getLoginUserID();

        $query = "SELECT id, user_id, default_page, list_limit, settings_hidden
        FROM glpi_plugin_mycustomview_user_settings 
        WHERE user_id = $id_user";

        $result = $DB->query($query);

        $array = array();

        foreach($result as $data) {
            array_push($array, $data);
        }

        return $array;
    }

    public static function isDefaultPageOfUser()
    {
        $result = self::isUserInSettingsMcv();

        if (!($result)) {
            return false;
        }

        foreach ($result as $liste) {
            if ($liste['default_page'] == 1) {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function areSettingsHidden()
    {
        $result = self::isUserInSettingsMcv();

        if (!($result)) {
            return false;
        }

        foreach ($result as $liste) {
            if ($liste['settings_hidden'] == 1) {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function changeSettingsVisibility($hidden)
    {
        global $DB;

        $exist = self::isUserInSettingsMcv();
        if ($exist) {
            $DB->update('glpi_plugin_mycustomview_user_settings',
                [
                    'settings_hidden'     => $hidden,
                ], [
                    'user_id' => Session::getLoginUserID()
                ]
            );
        } else {
            $DB->insert('glpi_plugin_mycustomview_user_settings',
                [
                    'user_id'      => Session::getLoginUserID(),
                    'settings_hidden'  => $hidden,
                ]
            );
        }
    }

    public static function changeDefaultPage($default)
    {
        global $DB;

        $exist = self::isUserInSettingsMcv();

        if ($exist) {
            $DB->update('glpi_plugin_mycustomview_user_settings',
                [
                    'default_page'     => $default,
                ], [
                    'user_id' => Session::getLoginUserID()
                ]
            );
        } else {
            $DB->insert('glpi_plugin_mycustomview_user_settings',
                [
                    'user_id'      => Session::getLoginUserID(),
                    'default_page'  => 1,
                ]
            );
        }
    }

    public static function changeScreenMode($id, $screenmode)
    {
        global $DB;

        $DB->update('glpi_plugin_mycustomview_savedsearch_list',
            [
                'screen_mode'      => $screenmode,
            ], [
                'id' => $id
            ]
        );
    }

    public static function changeHeight($id, $height)
    {
        global $DB;

        $DB->update('glpi_plugin_mycustomview_savedsearch_list',
            [
                'height'      => $height,
            ], [
                'id' => $id
            ]
        );
    }

    public static function changeSavedSearchTitle($id, $title)
    {
        global $DB;

        $DB->update('glpi_savedsearches',
            [
                'name'      => $title,
            ], [
                'id' => $id
            ]
        );
    }

    public static function getLimitNumberUser($id)
    {
        global $DB;

        $table = 'glpi_plugin_mycustomview_user_settings';

        $result = $DB->request([
            'SELECT' =>
            "$table.list_limit",
            'FROM' => $table,
            'WHERE' => ["$table.user_id"    => $id]
        ]);

        return $result;
    }

    public static function getListLimitForUser($id, $number = 10)
    {
        $result = self::getLimitNumberUser($id);

        // Si il y a déjà des settings pour l'utilisateur
        if (count($result)) {
            foreach ($result as $liste) {
                self::changeItemsNumber(Session::getLoginUserID(), $number);
                $_SESSION['glpilist_limit_mcv'] = $number;
            }
        } else {
            if (self::isUserInSettingsMcv()) {
                self::changeItemsNumber(Session::getLoginUserID(), $number);
            } else {
                self::createItemsNumber(Session::getLoginUserID(), $number);
            }
            $_SESSION['glpilist_limit_mcv'] = $number;
        }
    }

    public static function createItemsNumber($id, $number)
    {
        global $DB;

        $table = 'glpi_plugin_mycustomview_user_settings';
        $DB->insert($table,
            [
                'list_limit' => $number,
                'user_id' => $id
            ]
        );

        $_SESSION['glpilist_limit_mcv'] = $number;
    }

    public static function changeItemsNumber($id, $number)
    {
        global $DB;
        
        $table = 'glpi_plugin_mycustomview_user_settings';

        $DB->update(
            $table,
            [
                'list_limit'      => $number,
            ], [
                'user_id' => $id
            ]
        );
    }

    /**
     * Affiche la liste des recherches sauvegardées de l'utilisateur
     *
     * @return void
     **/
    public static function displaySavedSearchListMcv($result, $userName, $idList)
    {
        global $indexBloc;

        $theServer = $_SERVER["HTTP_REFERER"];
        $theServer = str_replace("central.php", "", $theServer);
        $max_filters = PluginMycustomviewConfig::getMaxFilters();

        if ($indexBloc >= $max_filters) {
            echo "<div style='text-align: center;'>";
            echo "<h2>".__("Vous avez atteint le nombre de recherches sauvegardées maximum sur cette page", "mycustomview") . ".</h2>";
            echo "<h2>".__("Veuillez supprimer au moins une vue si vous souhaitez en rajouter d'autres", "mycustomview") . ".</h2>";
            echo "</div>";
        }

        echo "<div class='center mcv_tab_limit'>";
        echo "<table border='0' class='tab_cadrehov'>";
        echo "<thead>";
        echo "<tr class='tab_bg_2'>";
        echo "<th>" .__("Nom", "mycustomview") . "</th>";
        echo "<th>" .__("Type d'élément", "mycustomview") . "</th>";
        echo "<th>" .__("Utilisateur/Type de recherche", "mycustomview") . "</th>";
        echo "<th class='sorter-false'>" .__("Ajouter", "mycustomview") . " <i class='fa fa-plus' title='" .__("Sauvegarder cette recherche dans cette vue", "mycustomview") . "'></i></th>";
        echo "</thead>";
        echo "<tbody>";

        foreach ($result as $data) {
            $isAlreadyAdded = false;

            if ($data['name'] == '') {
                $data['name'] = $data['id'];
            }

            echo "<tr class='center'>";
            echo "<td><a href='$theServer" . strtolower($data['itemtype']) . ".php?" . $data['query'] . "'>" . $data['name'] . "</a></td>";
            echo "<td>" . $data['itemtype'] . "</td>";
            echo "<td>";

            if ($data['users_id'] == Session::getLoginUserID()) {
                if ($data['is_private'] == 0) {
                    echo __("Votre recherche publique", "mycustomview");
                }

                else {
                    echo $userName;
                }
            } else {
                echo __("Recherche publique", "mycustomview");
            }

            echo "</td>";

            if (isset($idList)) {
                foreach ($idList as $id) {
                    if ($id == $data['id']) {
                        $isAlreadyAdded = true;
                        break;
                    }
                }

                if ($isAlreadyAdded) {
                    echo "<td><b>" .__("Déjà ajoutée", "mycustomview") . "</b></td>";
                } else {      
                    echo "<td class='save_search_mcv'><i style ='cursor: pointer' class='fa fa-plus' data-id-saved-search='" . $data['id'] . "' title='" .__("Sauvegarder cette recherche dans cette vue", "mycustomview") . "'></i></td>";      
                }
            }

            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
        echo "</div>";

        self::addSavedSearchScriptMcv();
    }

    /**
     * Vérifie que la limite ne dépasse pas le max filter
     *
     * @return $data int
     **/
    public static function check_max_filters()
    {
        global $DB;

        $id_user = Session::getLoginUserID();

        $query = "SELECT `order`
        FROM glpi_plugin_mycustomview_savedsearch_list
        WHERE user_id = $id_user
        ORDER BY `order` DESC
        LIMIT 1";

        $result = $DB->query($query);

        foreach ($result as $data) {
            return $data;
        }
    }

    /**
     * Ajoute le javascript nécessaire au fonctionnement de la page
     *
     * @return void
     **/
    public static function addSavedSearchScriptMcv()
    {
        global $CFG_GLPI;

        $js = "
        $.fn.hasData = function(key) {
            return (typeof $(this).data(key) != 'undefined');
            };
        //  --------------
        //  AJOUT D'UNE SAVED SEARCH DANS LA LISTE --> BDD
        //  --------------
    
        $('.save_search_mcv').on('click', function(){
            var id = $(this).find('i').data('id-saved-search');
            var order = $('.mcv_tab').last().data('number');
            if (order == null){
                order = 0;
            }
            order = order + 1;
            $.ajax({
                url: '" . $CFG_GLPI['root_doc'] . "/plugins/mycustomview/ajax/saveSearch.php',
                type: 'GET',
                data: 'id=' + id + '&user=' + " . Session::getLoginUserID() . " + '&order=' + order,
                success:function(data) {
                    window.location.reload();
                    displayAjaxMessageAfterRedirect();
                            
                }
            });
        });
    
        //  --------------
        // ENREGISTREMENT DU NOUVEL ORDRE DES TABLEAUX --> BDD
        //  --------------
    
        $('#mcv_save').on('click', function(){
    
            var tabs = $('.mcv_tab:not(:hidden)');
            var orderTab = [];
            deleteTab = [];
            screenmodeTab = [];
            heightTab = [];
            var number;
            var id;
            var dif = false;
            tabs.each(function(index) {
                id = $(this).children('.mcv_delete').data('id-savedsearch');
                if ($(this).hasData('screenmode')) {
                    screenmode = $(this).data('screenmode');
                    screenmodeTab.push({id :id, screenmode :screenmode});
                }
                var height = $(this).height();
                var dataHeight = $(this).data('height');
                if (height != dataHeight) {
                    heightTab.push({id: id, height :height});
                }
                number = $(this).data('number');
                
                // Il faut utiliser attr('data-nom') plutot que data('nom')
                $(this).attr('data-number', index+1);
                newNumber = $(this).attr('data-number');
                if (number == newNumber) {
                    dif = false;
                }
                else {
                    dif = true;
                }
    
                if(dif) {
                orderTab.push({id :id, order :newNumber});
                }
            });
    
            $.ajax({
                url: '" . $CFG_GLPI['root_doc'] . "/plugins/mycustomview/ajax/deleteAndMoveSearch.php',
                type: 'POST',
                data: {orderTab:orderTab, deleteTab:deleteTabId, screenmodeTab:screenmodeTab, heightTab:heightTab},
                success:function(data) {
                window.location.reload();
                }
            });
        });
    
        //  --------------
        // SUPPRESSION D'UNE SAVED SEARCH DANS LA VUE --> BDD
        //  --------------
        
        var deleteMode = false;
        var deleteTabId = [];
        $('.mcv_delete').on('click', function(){
        deleteMode = true;
        var id = $(this).data('id-savedsearch');
        deleteTabId.push(id);
        var tabFadeOut = $(this).parent();
        tabFadeOut.fadeOut(600);
        });
    
        $('.mcv_cancel').on('click', function() {
        if (deleteMode) {
            $('.mcv_tab').removeAttr('style');
            deleteMode = false;
            deleteTabId = [];
            }
        });
    
        //  --------------
        // AJOUT DE LA PAGE PAR DEFAULT --> BDD
        //  --------------
    
        $('.mcv_add_default').on('click', function(){
            $.ajax({
                url: '" . $CFG_GLPI['root_doc'] . "/plugins/mycustomview/ajax/defaultPage.php',
                type: 'GET',
                data: 'default=1',
                success:function(data) {
                    window.location.reload();
                }
            });
        });

        //  --------------
        // AJOUT DE LA PAGE PAR DEFAULT --> BDD
        //  --------------

        $('#mcv_show').on('click', function(){
            $.ajax({
                url: '" . $CFG_GLPI['root_doc'] . "/plugins/mycustomview/ajax/changeSettingsVisibility.php',
                type: 'GET',
                data: 'hidden=0',
                success:function(data) {
                }
            });
        });

        $('#mcv_hide').on('click', function(){
            $.ajax({
                url: '" . $CFG_GLPI['root_doc'] . "/plugins/mycustomview/ajax/changeSettingsVisibility.php',
                type: 'GET',
                data: 'hidden=1',
                success:function(data) {
                }
            });
        });
    
        //  --------------
        // SUPPRESSION DE LA PAGE PAR DEFAULT --> BDD
        //  --------------
    
        $('.mcv_delete_default').on('click', function(){
            $.ajax({
                url: '" . $CFG_GLPI['root_doc'] . "/plugins/mycustomview/ajax/defaultPage.php',
                type: 'GET',
                data: 'default=0',
                success:function(data) {
                    window.location.reload();
                }
            });
        });
    
        // --------------
        // CHANGEMENT DU TITRE D'UNE RECHERCHE SAUVEGARDEE 
        // --------------
    
        $('.mcv_change_title').on('click', function() {
            var id = $(\"input[name='id_savedsearch']\").val();
            var newTitle = $('.savedsearch_title').val();
            $.ajax({
                url: '" . $CFG_GLPI['root_doc'] . "/plugins/mycustomview/ajax/changeSavedSearchTitle.php',
                type: 'GET',
                data: 'id=' + id + '&newTitle=' + newTitle,
                success:function(data) {
                    window.location.reload();
                }
            });
        });
    
        // --------------
        // CHANGEMENT DU NOMBRE D'ELEMENT MAXIMUM PAR VUE
        // --------------
    
        $('.mcv_nb_items').on('change', function() {
            var actualNumber = $('.mcv_nb_items').data('session-items-number');
            var number = $(this).val();
            var id = $('#user-id').val();
            if (number !== actualNumber) {
                $.ajax({
                    url: '" . $CFG_GLPI['root_doc'] . "/plugins/mycustomview/ajax/changeItemsNumberPerView.php',
                    type: 'GET',
                    data: 'id=' + id + '&number=' + number,
                    success:function(data) {
                    window.location.reload();
                    }
                });
            }
        });
        ";

        echo Html::scriptBlock($js);
    }

    public static function displayMessageModal($message)
    {
        $js2 = "
        $('.mcv_display_message').removeClass('mcv_display_none');
    
        ";

        echo Html::scriptBlock($js2);

        return $message;
    }

    /**
     * Affiche une aide pour ré-afficher le tableau d'ajout de recherches sauvegardées si jamais la liste est complète.
     *
     * @return void
     **/
    public static function maxSavedSearchReached()
    {
        Session::addMessageAfterRedirect(
            __(
                "Vous avez atteint le nombre de recherches sauvegardées maximum sur cette page",
                "mycustomview"
            ),
            true,
            ERROR
        );
    }

    /**
     * Affiche le tableau correspondant au contenu d'une recherche sauvegardée
     *
     * @return $result (Objet de DB)
     **/
    public static function displaySavedSearchMcv($data)
    {
        echo "<button class='mcv_delete mcv_display_none'><i class='fas fa-2x fa-trash'></i>" .__("Supprimer","mycustomview") . "</button>";
        echo "<h1>" .__("Futur affichage d'un tableau ! En construction","mycustomview") . "</h1>";
        echo __("Voici un tableau de la SavedSearch numéro","mycustomview") . " " . $data['savedsearch_id'];
        echo "<br>";
        echo __("L'ordre de ce tableau est le","mycustomview") . " : " . $data['order'];
        echo "<div class='center mcv_savedsearch_container' data-id-savedsearch='" . $data['id'] . "'>";
        echo "<table border='0' class='tab_cadrehov'>";
        echo "<thead>";
        echo "<tr class='tab_bg_2'>";
        echo "<th>" .__("Nom","mycustomview") . "</th>";
        echo "<th>" .__("Type d'élément","mycustomview") . "</th>";
        echo "<th>" .__("Utilisateur","mycustomview") . "</th>";
        echo "<th><a href='#' class='fa fa-star' style='color:white' title='" .__("Sauvegarder cette recherche dans cette vue","mycustomview") . "'></a></th>";
        echo "</thead>";
        echo "</table>";
        echo "</div>";
    }

    public static function displayHelpModal()
    {
        echo "<div style='z-index: 10002;' class='mcv_modal mcv_modal_bg_light mcv_modal_large mcv_modal_help mcv_display_none' role='dialog'>";
        echo "<button title='" .__("Fermer","mycustomview") . "' class='mcv_modal_close p-none mcv_button_basic'><i class='fas fa-3x fa-window-close' data-modaltype='help' aria-hidden='true'></i></button>";
        echo "<div class='mcv_modal_bg_basic mcv_text_light mcv_modal_header'>";
        echo "<h2 class='text-center m-auto mcv_modal_header_text'>" .__("Comment ça marche","mycustomview") . " ?</h2>";
        echo "</div>";

        echo " <div class='flexslider'>";
        echo " <ul class='slides'>";

        echo "<li>";
        echo "<div class='p-5'>";
        echo "<p class='subtitle_modal m-5'>" .__("Etape 1 (Prérequis) : Création d'une ou plusieurs Recherche(s) sauvegardée(s)","mycustomview") . " </p><br>";
        echo "<p><span class='font-bigger'>" .__("Votre onglet","mycustomview") . " <b>« " .__("Ma vue personnalisée","mycustomview") . " »</b> " .__("fait appel à la fonctionnalité de","mycustomview") . " <b>« " .__("Recherches sauvegardées","mycustomview") . " »</b> " .__("(équivalent à se créer des filtres personnalisés)","mycustomview") . ".</span><br><br>";
        echo "<u>" .__("Suivre la procédure ci-dessous pour créer une (ou plusieurs) recherches","mycustomview") . " :<br><br></u>";
        echo "•	" .__("Dans le menu","mycustomview") . " <b>" .__("Assistance / Tickets","mycustomview") . "</b>, " .__("effectuez une nouvelle recherche en positionnant les filtres souhaités puis cliquez sur","mycustomview") . " <b>« " .__("Rechercher","mycustomview") . " »</b> " .__(". Il vous reste à sauvegarder votre recherche en cliquant sur le symbole en étoile","mycustomview") . " :";
        echo "</p></div>";

        if ($_SESSION['glpilanguage'] == 'fr_FR') {
                echo "  <img src='../plugins/mycustomview/images/fr/Aide_MCV_VF_1.png' style='max-height: 300px;' class='w-auto m-auto p-3 flexImages'/>";
        } else {
                echo "  <img src='../plugins/mycustomview/images/en/Help_MCV_EV_1.png' style='max-height: 300px;' class='w-auto m-auto p-3 flexImages'/>";
        }

        echo "</li>";

        echo "<li>";
        echo "<div class='p-5'><p style='margin: 0;'>";
        echo "•	" .__("Nommez votre recherche et cliquez sur","mycustomview") . " <b>" .__("Ajouter","mycustomview") . "</b> :";
        echo "</p></div>";

        if ($_SESSION['glpilanguage'] == 'fr_FR') {
            echo "  <img src='../plugins/mycustomview/images/fr/Aide_MCV_VF_2.png' style='max-height: 250px;' class='w-auto m-auto p-3 flexImages'/>";
        } else {
            echo "  <img src='../plugins/mycustomview/images/en/Help_MCV_EV_2.png' style='max-height: 250px;' class='w-auto m-auto p-3 flexImages'/>";
        }

        echo "<div class='p-5'><p style='margin: 0;'>";
        echo "•	 " .__("Vous avez la possibilité de faire des recherches sur d'autres objets GLPI comme les ordinateurs (Menu","mycustomview") . " <b>" .__("Parc / Ordinateurs","mycustomview") . ")</b> :";
        echo "</p></div>";

        if ($_SESSION['glpilanguage'] == 'fr_FR') {
            echo "  <img src='../plugins/mycustomview/images/fr/Aide_MCV_VF_3.png' style='max-height: 300px;' class='w-auto m-auto p-3 flexImages'/>";
        } else {
            echo "  <img src='../plugins/mycustomview/images/en/Help_MCV_EV_3.png' style='max-height: 300px;' class='w-auto m-auto p-3 flexImages'/>";
        }

        echo "  </li>";

        echo " <li>";
        echo "<div class='p-5'>";
        echo "<p class='subtitle_modal m-5'>" .__("Etape 2 : Construire sa vue personnalisée","mycustomview") . "</p><br>";
        echo "<p>•	" .__("Dans la page d'accueil, cliquez sur l'onglet","mycustomview") . " <b>« " .__("Ma vue personnalisée","mycustomview") . " » </b>" .__("(Par défaut aucune recherche ne s'affiche)","mycustomview") . ". " . __("Puis cliquez sur le bouton", "mycustomview") . " « <strong>" . strtoupper(__("Modifier", "mycustomview")) . "</strong> » :";
        echo "</p></div>";

        if ($_SESSION['glpilanguage'] == 'fr_FR') {
            echo "  <img src='../plugins/mycustomview/images/fr/Aide_MCV_VF_4.png' class='w-auto m-auto p-3 flexImages'/>";
        } else {
            echo "  <img src='../plugins/mycustomview/images/en/Help_MCV_EV_4.png' class='w-auto m-auto p-3 flexImages'/>";
        }

        echo "<div class='p-5'><p>";
        echo "•	  " .__("En bas de page, cliquez sur le bouton","mycustomview") . " <b>« " . strtolower(__("Afficher la liste", "mycustomview")) . " " . "»</b> " .__("puis cliquez sur le symbole","mycustomview") . " <b>« + »</b> " .__("face à la recherche sauvegardée que vous souhaitez afficher.", "mycustomview");
        echo "<br>";
        echo __("Une fois sélectionnée, celle-ci s'affiche dans votre vue et est indiquée comme","mycustomview") . " <b>« " .__("Déjà ajoutée","mycustomview") . " »</b>.";
        echo "</p></div>";

        if ($_SESSION['glpilanguage'] == 'fr_FR') {
            echo "  <img src='../plugins/mycustomview/images/fr/Aide_MCV_VF_5.png' class='w-auto m-auto p-3 flexImages'/>";
            echo "  <img src='../plugins/mycustomview/images/fr/Aide_MCV_VF_6.png' class='w-auto m-auto p-3 flexImages'/>";
        } else {
            echo "  <img src='../plugins/mycustomview/images/en/Help_MCV_EV_5.png' class='w-auto m-auto p-3 flexImages'/>";
            echo "  <img src='../plugins/mycustomview/images/en/Help_MCV_EV_6.png' class='w-auto m-auto p-3 flexImages'/>";
        }

        echo "  </li>";

        echo "<li>";
        echo "<div class='p-5'>";
        echo "<p>•	" .__("Une fois vos recherches ajoutées, vous avez la possibilité en cliquant sur le bouton","mycustomview") . " <b>« " .__("Modifier","mycustomview") . " »</b> " .__("de","mycustomview") . " :</p>
            <p style='padding-left:2rem;'>-	" .__("Réorganiser vos fenêtres de recherche via un cliquer-Glisser","mycustomview") . " :</p>";
        echo "</div>";

        if ($_SESSION['glpilanguage'] == 'fr_FR') {
            echo "  <img src='../plugins/mycustomview/images/fr/Aide_MCV_VF_7.png' style='max-height: 400px;' class='w-auto m-auto p-3 flexImages'/>";
        } else {
            echo "  <img src='../plugins/mycustomview/images/en/Help_MCV_EV_7.png' style='max-height: 400px;' class='w-auto m-auto p-3 flexImages'/>";
        }

        echo "</li>";

        echo "<li>";
        echo "<div class='p-5'><p style='padding-left:2rem;'>";
        echo "-  " .__("D'étendre ou de réduire votre fenêtre de recherche en utilisant les boutons « Fenêtre Large » ou « Fenêtre Réduite »","mycustomview") . " :";
        echo "</p></div>";

        if ($_SESSION['glpilanguage'] == 'fr_FR') {
            echo "  <img src='../plugins/mycustomview/images/fr/Aide_MCV_VF_8.png' class='w-auto m-auto p-3 flexImages'/>";
        } else {
            echo "  <img src='../plugins/mycustomview/images/en/Help_MCV_EV_8.png' class='w-auto m-auto p-3 flexImages'/>";
        }

        echo "<div class='p-5'><p style='padding-left:2rem;'>";
        echo "-  " .__("De renommer votre recherche en cliquant sur le symbole représentant un crayon","mycustomview") . " :";
        echo "</p></div>";

        if ($_SESSION['glpilanguage'] == 'fr_FR') {
            echo "  <img src='../plugins/mycustomview/images/fr/Aide_MCV_VF_9.png' class='w-auto m-auto p-3 flexImages'/>";
        } else {
            echo "  <img src='../plugins/mycustomview/images/en/Help_MCV_EV_9.png' class='w-auto m-auto p-3 flexImages'/>";
        }

        echo "<div class='p-5'><p style='padding-left:2rem;'>";
        echo "-  " .__("De supprimer de votre vue une recherche en cliquant sur le bouton <b>« Supprimer »</b>","mycustomview") . " : ";
        echo "</p></div>";

        if ($_SESSION['glpilanguage'] == 'fr_FR') {
            echo "  <img src='../plugins/mycustomview/images/fr/Aide_MCV_VF_10.png' class='w-auto m-auto p-3 flexImages'/>";
        } else {
            echo "  <img src='../plugins/mycustomview/images/en/Help_MCV_EV_10.png' class='w-auto m-auto p-3 flexImages'/>";
        }

        echo "<div class='p-5' style='text-align: center;'><p>";
        echo "<span style='color: red'><b>" .__("IMPORTANT","mycustomview") . "</b></span> : " .__("Une fois vos modifications effectuées, enregistrez-les en cliquant sur le bouton <b>« Enregistrer »</b>", "mycustomview") . " : ";
        echo "</p></div>";

        if ($_SESSION['glpilanguage'] == 'fr_FR') {
            echo "  <img src='../plugins/mycustomview/images/fr/Aide_MCV_VF_11.png' class='w-auto m-auto p-3 flexImages'/>";
        } else {
            echo "  <img src='../plugins/mycustomview/images/en/Help_MCV_EV_11.png' class='w-auto m-auto p-3 flexImages'/>";
        }

        echo " </li>";

        echo "<li>";
        echo "<div class='p-5'>";
        echo "<p class='subtitle_modal m-5'>" .__("Etape 3 : Personnaliser les champs visibles dans votre vue","mycustomview") . "</p><br>";
        echo "<p>•	" .__("Cliquez sur le symbole « Clef » pour sélectionner les champs souhaités","mycustomview") . " :</b>";
        echo "</p></div>";

        if ($_SESSION['glpilanguage'] == 'fr_FR') {
            echo "  <img src='../plugins/mycustomview/images/fr/Aide_MCV_VF_12.png' class='w-auto m-auto p-3 flexImages'/>";
        } else {
            echo "  <img src='../plugins/mycustomview/images/en/Help_MCV_EV_12.png' class='w-auto m-auto p-3 flexImages'/>";
        }

        echo "<div class='p-5'><p>";
        echo "•	" .__("Sélectionnez « Vue Personnelle » puis le champ à afficher et cliquer sur le bouton <b>« Ajouter »</b>","mycustomview") . " : ";
        echo "</p></div>";

        if ($_SESSION['glpilanguage'] == 'fr_FR') {
            echo "  <img src='../plugins/mycustomview/images/fr/Aide_MCV_VF_13.png' class='w-auto m-auto p-3 flexImages'/>";
        } else {
            echo "  <img src='../plugins/mycustomview/images/en/Help_MCV_EV_13.png' class='w-auto m-auto p-3 flexImages'/>";
        }

        echo " </li>";

        echo " <li>";
        echo "<div class='p-5'>";
        echo "<p class='subtitle_modal m-5'>" .__("Etape 4 : Les options supplémentaires","mycustomview") . "</p><br>";
        echo "<p>•	" .__("Cochez la case « Page par défaut » pour positionner l'onglet « Ma vue personnalisée » comme page par défaut.","mycustomview") . "</b>";
        echo "</p>";
        echo "<p>•	" .__("Sélectionnez le nombre d'éléments que vous souhaitez afficher par recherche","mycustomview") . ".";
        echo "</p></div>";

        if ($_SESSION['glpilanguage'] == 'fr_FR') {
            echo "  <img src='../plugins/mycustomview/images/fr/Aide_MCV_VF_14.png' class='w-auto m-auto p-3 flexImages'/>";
        } else {
            echo "  <img src='../plugins/mycustomview/images/en/Help_MCV_EV_14.png' class='w-auto m-auto p-3 flexImages'/>";
        }

        echo "<div class='p-5'>";
        echo "<p>•	" .__("Vous pouvez agrandir votre vue en étirant la fenêtre bas à droite (accessible en mode « Modification »)","mycustomview");
        echo "</p>";
        echo "</div>";

        if ($_SESSION['glpilanguage'] == 'fr_FR') {
            echo "  <img src='../plugins/mycustomview/images/fr/Aide_MCV_VF_15.png' class='w-auto m-auto p-3 flexImages'/>";
        } else {
            echo "  <img src='../plugins/mycustomview/images/en/Help_MCV_EV_15.png' style='max-width: 500px;' class='w-auto m-auto p-3 flexImages'/>";
        }

        echo "  </li>";
        echo " </ul>";
        echo "</div>";
        echo "</div>";
    }

    public static function displayChangeTitleModal()
    {
        echo "<div style='z-index: 10002; text-align: center;' class='mcv_modal mcv_modal_bg_light mcv_modal_very_small mcv_modal_edit_title mcv_display_none' role='dialog'>";
        echo "<button title='" .__("Fermer","mycustomview") . "' class='mcv_modal_close p-none mcv_button_basic'><i class='fas fa-3x fa-window-close' data-modaltype='help' aria-hidden='true'></i></button>";
        echo "<div class='mcv_modal_bg_basic mcv_text_light mcv_modal_header'>";
        echo "<h2 class='text-center m-auto mcv_modal_header_text'>" .__("Modification du titre","mycustomview") . "</h2>";
        echo "</div>";
        echo "<h3 class='mcv_modal_text_edit_title'>" .__("Saisissez le nouveau titre de cette recherche","mycustomview") . "</h3>";
        echo "<input type='hidden' name='id_savedsearch'></input>";
        echo "<input type='text' class='savedsearch_title'></input>";

        echo "<button class='mcv_button mcv_button_success mcv_text_light mcv_change_title'>" .__("Valider","mycustomview") . "</button>";
        echo "</div>";
    }

    public static function displayCancelModal()
    {
        echo "<div style='z-index: 10002; position:fixed;' class='mcv_modal mcv_modal_bg_light mcv_modal_very_small mcv_cancel_message mcv_display_none'>";
        echo "<button title='" .__("Fermer","mycustomview") . "' class='mcv_modal_close p-none mcv_button_basic'><i class='fas fa-3x fa-window-close' data-modaltype='help' aria-hidden='true'></i></button>";
        echo "<div class='mcv_modal_bg_basic mcv_text_light mcv_modal_header'>";
        echo "<h2 class='text-center m-auto mcv_modal_header_text'>" .__("Attention","mycustomview") . "</h2>";
        echo "</div>";
        echo "<p style='padding:1rem .5rem;' class='mcv_text_dark mcv_message_text text-center font-bigger'>" .__("Annuler la modification ? Aucun changement ne sera sauvegardé","mycustomview") . ".</br>" .__("Êtes-vous sûr de confirmer","mycustomview") . " ?";
        echo "</p>";
        echo "<div class='mcv_modal_footer d-flex flex-end'>";
        echo "<button class='mcv_button mcv_button_dark mcv_text_light mcv_button_dark mcv_button_popup mcv_button_message_cancel'>" .__("Confirmer","mycustomview") . "</button>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
}