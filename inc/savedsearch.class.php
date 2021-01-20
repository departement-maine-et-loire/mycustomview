<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMycustomviewSavedSearch extends SavedSearch
{

   /**
    * Récupère la liste de toutes les recherches sauvegardées par l'utilisateur
    *
    * @return $result (data from DB)
    */
   static function getSavedSearchListMcv()
   {
      global $DB;

      $table = 'glpi_savedsearches';
      $utable = 'glpi_savedsearches_users';

      $result = $DB->request([
         'SELECT' =>
         "$table.*",
         'FROM' => $table,
         'LEFT JOIN' => [
            $utable => [
               'ON' => [
                  $utable  => 'savedsearches_id',
                  $table   => 'id'
               ]
            ]
         ],
         'WHERE' => [
            "$table.users_id"    => Session::getLoginUserID()
         ],
         'LIMIT' => 20
      ]);

      return $result;
   }

   static function getSavedSearchById($id)
   {

      global $DB;

      $table = 'glpi_savedsearches';
      $result = $DB->request([
         'SELECT' =>
         "$table.*",
         'FROM' => $table,

         'WHERE' => [
            "$table.id"    => $id
         ]
      ]);

      return $result;
   }

   /**
    * Affiche la liste des recherches sauvegardées de l'utilisateur
    *
    * @return void
    */
   static function displaySavedSearchListMcv($result, $userName, $idList)
   {

      echo "<div class='center mcv_tab_limit'>";
      echo "<table border='0' class='tab_cadrehov'>";
      echo "<thead>";
      echo "<tr class='tab_bg_2'>";
      echo "<th>Nom</th>";
      echo "<th>Type d'élément</th>";
      echo "<th>Utilisateur</th>";
      echo "<th class='sorter-false'>Ajouter <i class='fa fa-plus' title='Sauvegarder cette recherche dans cette vue'></i></th>";
      echo "</thead>";
      echo "<tbody>";

      foreach ($result as $data) {
         $isAlreadyAdded = false;
         if ($data['name'] == '') {
            $data['name'] = $data['id'];
         }
         echo "<tr class='center'>";
         echo "<td><a href='/front/" . strtolower($data['itemtype']) . ".php?" . $data['query'] . "'>" . $data['name'] . "</a></td>";
         echo "<td>" . $data['itemtype'] . "</td>";
         echo "<td>" . $userName . "</td>";
         if (isset($idList)) {

            foreach ($idList as $id) {

               if ($id == $data['id']) {
                  $isAlreadyAdded = true;
                  break;
               }
            }
            if ($isAlreadyAdded) {
               echo "<td><b>Déjà ajoutée</b></td>";
            } else {
               echo "<td class='save_search_mcv'><i style ='cursor: pointer' class='fa fa-plus' data-id-saved-search='" . $data['id'] . "' title='Sauvegarder cette recherche dans cette vue'></i></td>";
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
    * Ajoute le javascript nécessaire au fonctionnement de la page
    *
    * @return void
    */
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
         var data = [];
         deleteTab = [];
         screenmodeTab = [];
         var number;
         var id;
         var dif = false;
         tabs.each(function(index) {
            id = $(this).children('.mcv_delete').data('id-savedsearch');
            if ($(this).hasData('screenmode')) {
                screenmode = $(this).data('screenmode');
                screenmodeTab.push({id :id, screenmode :screenmode});
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
            data.push({id :id, order :newNumber});
            }
         });

         if(deleteMode){
            deleteTab.push(deleteTabId);
         }

         $.ajax({
            url: '" . $CFG_GLPI['root_doc'] . "/plugins/mycustomview/ajax/deleteAndMoveSearch.php',
            type: 'POST',
            data: {data:data, deleteTab:deleteTab, screenmodeTab:screenmodeTab},
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
      ";


      echo Html::scriptBlock($js);
   }

   static function displayMessageModal($message)
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
    */
   public function maxSavedSearchReached()
   {

      echo "<div class='center mcv_text_limit'>";
      echo "<h2>Vous avez atteint le nombre de recherches sauvegardées maximum sur cette page.</h2>";
      echo "<h3>Afin de pouvoir en ajouter d'autres, veuillez en supprimer une ou plusieurs grâce au bouton 'Modifier', puis sélectionnez le bouton 'Supprimer' sur vos recherches sauvegardées dans cette vue. N'oubliez pas d'enregistrer vos modifications.</h3>";
      echo "</div>";
   }

   /**
    * Affiche le tableau correspondant au contenu d'une recherche sauvegardée
    *
    * @return $result (Objet de DB)
    */
   public function displaySavedSearchMcv($data)
   {
      echo "<button class='mcv_delete mcv_display_none'><i class='fas fa-2x fa-trash'></i>Supprimer</button>";
      echo '<h1>Futur affichage dun tableau ! En construction</h1>';
      echo 'Voici un tableau de la SavedSearch numéro ' . $data['savedsearch_id'];
      echo '<br>';
      echo 'Lordre de ce tableau est le : ' . $data['order'];
      echo "<div class='center mcv_savedsearch_container' data-id-savedsearch='" . $data['id'] . "'>";
      echo "<table border='0' class='tab_cadrehov'>";
      echo "<thead>";
      echo "<tr class='tab_bg_2'>";
      echo "<th>Nom</th>";
      echo "<th>Type d'élément</th>";
      echo "<th>Utilisateur</th>";
      echo "<th><a href='#' class='fa fa-star' style='color:white' title='Sauvegarder cette recherche dans cette vue'></a></th>";
      echo "</thead>";
      echo "</table>";
      echo "</div>";
   }

   /**
    * Récupère la liste des recherches sauvegardées par l'utilisateur
    *
    * @return $result (Objet de DB)
    */
   public static function getUserSavedSearchMcv($max_filters = null)
   {
      global $DB;
      $data = [];

      $table = 'glpi_plugin_mycustomview_savedsearch_list';
      $result = $DB->request([
         'SELECT' =>
         "$table.*",
         'FROM' => $table,

         'WHERE' => [
            "$table.user_id"    => Session::getLoginUserID()
         ],
         'LIMIT' => $max_filters,
         'ORDER' => "$table.order ASC"
      ]);

      foreach ($result as $dataRow) {
         array_push($data, $dataRow);
      }
      return $data;
   }

   public static function countUserSavedSearchMcv()
   {

      $result = self::getUserSavedSearchMcv();
      return count($result);
   }

   /**
    * Ajoute une savedSearch dans la liste de l'utilisateur la base de données
    *
    * @return void
    */
   public function addSavedSearch($id, $user, $order)
   {
      global $DB;
      $DB->insert(
         'glpi_plugin_mycustomview_savedsearch_list',
         [
            'user_id'      => $user,
            'savedsearch_id'  => $id,
            'order'      => $order,
            'screen_mode' => 0
         ]
      );
      return true;
   }

   static function checkUnicitySavedSearch($id)
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
   public static function deleteSavedSearch($id)
   {
      global $DB;
      $DB->delete(
         'glpi_plugin_mycustomview_savedsearch_list',
         [
            'id'      => $id
         ]
      );
   }

   /**
    * Réorganise les valeurs 'order' de la liste d'un utilisateur
    *
    * @return void
    */
   public static function reorderSavedSearch()
   {
      global $DB;

      $table = 'glpi_plugin_mycustomview_savedsearch_list';
      $result = $DB->request([
         'SELECT' =>
         "$table.*",
         'FROM' => $table,

         'WHERE' => [
            "$table.user_id"    => Session::getLoginUserID()
         ],
         'ORDER' => "$table.order ASC"
      ]);

      $i = 1;
      $changeOrder = false;

      foreach ($result as $liste) {
         if ($liste['order'] == $i) {
            $changeOrder = false;
            $i++;
         } else {
            $newOrder = $liste['order'];
            while ($newOrder != $i) {
               $newOrder = $newOrder - 1;
            }
            $DB->update(
               'glpi_plugin_mycustomview_savedsearch_list',
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

   public static function moveSavedSearch($values)
   {
      global $DB;

      foreach ($values as $tab) {
         foreach ($tab as $update) {

            if (isset($update["order"]) && isset($update['id'])) {
               $DB->update(
                  'glpi_plugin_mycustomview_savedsearch_list',
                  [
                     'order'      => $update["order"],
                  ],
                  [
                     'id' => $update['id']
                  ]
               );
            }
         }
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

         'WHERE' => [
            "$table.user_id"    => Session::getLoginUserID()
         ],
      ]);
      if (count($result)) {
         return $result;
      } else {
         return false;
      }
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

   public static function changeDefaultPage($default)
   {
      global $DB;

      $exist = self::isUserInSettingsMcv();
      if ($exist) {
         $DB->update(
            'glpi_plugin_mycustomview_user_settings',
            [
               'default_page'     => $default,
            ],
            [
               'user_id' => Session::getLoginUserID()
            ]
         );
      } else {
         $DB->insert(
            'glpi_plugin_mycustomview_user_settings',
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

      $DB->update(
         'glpi_plugin_mycustomview_savedsearch_list',
         [
            'screen_mode'      => $screenmode,
         ],
         [
            'id' => $id
         ]
      );
   }

   public static function changeSavedSearchTitle($id, $title)
   {
      global $DB;

      $DB->update(
         'glpi_savedsearches',
         [
            'name'      => $title,
         ],
         [
            'id' => $id
         ]
      );
   }

   public static function getPersonalFields()
   {
   }
}
