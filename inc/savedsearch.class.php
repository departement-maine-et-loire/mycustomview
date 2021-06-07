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
         'OR' => [
            [
               "$table.users_id"    => Session::getLoginUserID() 
            ], 
            [
               "$table.is_private" => 0
            ]
         ],
         'LIMIT' => 50
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
      global $DB;
      $result = $DB->request([
         'FROM'   => 'glpi_plugin_mycustomview_savedsearch_list',
         'COUNT'  => 'cpt',
         'WHERE'  => ['user_id' => Session::getLoginUserID()]
      ])->next();
      return $result['cpt'];
   }

   /**
    * Ajoute une savedSearch dans la liste de l'utilisateur la base de données
    *
    * @return void
    */
   public static function addSavedSearch($id, $user, $order)
   {
      global $DB;
      $insert_query = $DB->buildInsert(
         'glpi_plugin_mycustomview_savedsearch_list',
         [
            'user_id'      =>new QueryParam(),
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
   public static function deleteSavedSearch($delete_tabs)
   {
      global $DB;

      $delete_query = $DB->buildDelete(
         'glpi_plugin_mycustomview_savedsearch_list',
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

      foreach ($result as $liste) {
         if ($liste['order'] == $i) {
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

   public static function moveSavedSearch($order_tabs)
   {
      global $DB;

      $update_query = $DB->buildUpdate(
         'glpi_plugin_mycustomview_savedsearch_list',
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

   static public function getUserSettings(){
      global $DB;

      $table = 'glpi_plugin_mycustomview_user_settings';

      $result = $DB->request(['FROM' => $table, 'WHERE'  => ['user_id' => Session::getLoginUserID()]])->next();

      return $result;
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
         $DB->update(
            'glpi_plugin_mycustomview_user_settings',
            [
               'settings_hidden'     => $hidden,
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

   public static function changeHeight($id, $height)
   {
      global $DB;

      $DB->update(
         'glpi_plugin_mycustomview_savedsearch_list',
         [
            'height'      => $height,
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

   public static function getLimitNumberUser($id)
   {
      global $DB;
      $table = 'glpi_plugin_mycustomview_user_settings';

      $result = $DB->request([
         'SELECT' =>
         "$table.list_limit",
         'FROM' => $table,

         'WHERE' => [
            "$table.user_id"    => $id
         ],
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
      }
   }

   public static function createItemsNumber($id, $number)
   {
      global $DB;
      $table = 'glpi_plugin_mycustomview_user_settings';
      $DB->insert(
         $table,
         [
            'list_limit'      => $number,
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
         ],
         [
            'user_id' => $id
         ]
      );
      $_SESSION['glpilist_limit_mcv'] = $number;
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
      echo "<th>Utilisateur/Type de recherche</th>";
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
         echo "<td>";
         if ($data['users_id'] == Session::getLoginUserID()) {
            if($data['is_private'] == 0) {
               echo "Votre recherche publique";
            }
            else {
               echo $userName;
            }
         }
         else {
            echo "Recherche publique";
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
   public static function maxSavedSearchReached()
   {

      echo "<div class='center mcv_text_limit'>";
      echo "<h2>Vous avez atteint le nombre de recherches sauvegardées maximum sur cette page.</h2>";
      echo "</div>";
   }

   /**
    * Affiche le tableau correspondant au contenu d'une recherche sauvegardée
    *
    * @return $result (Objet de DB)
    */
   public static function displaySavedSearchMcv($data)
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

   public static function displayHelpModal()
   {
      echo "<div class='mcv_modal mcv_modal_bg_light mcv_modal_large mcv_modal_help mcv_display_none'>";
      echo "<button title='Fermer' class='mcv_modal_close p-none mcv_button_basic'><i class='fas fa-3x fa-window-close' data-modaltype='help' aria-hidden='true'></i></button>";
      echo "<div class='mcv_modal_bg_basic mcv_text_light mcv_modal_header'>";
      echo "<h2 class='text-center m-auto mcv_modal_header_text'>Comment ça marche ?</h2>";
      echo "</div>";

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
      echo "<p class='subtitle_modal m-5'>Etape 4 : Les options supplémentaires</p><br>";
      echo "<p>•	Cochez la case « Page par défaut » si vous souhaitez avoir l’onglet « Ma vue personnalisée » comme page par défaut lors de vos prochaines connexions</b>";
      echo "</p>";
      echo "<p>•	Sélectionnez le nombre d'éléments que vous souhaitez afficher par recherche</b>";
      echo "</p></div>";
      echo "  <img src='../plugins/mycustomview/images/img14.png' class='w-auto maxw-80 m-auto p-3 flexImages'/>";
      echo "<div class='p-5'>";
      echo "<p>•	Vous pouvez agrandir la taille de vos fenêtres en tirant le bouton tout en bas à droite (accessible en mode « Modification »)";
      echo "</p>";
      echo "</div>";
      echo " <img src='../plugins/mycustomview/images/img15.png' class='w-auto maxw-80 m-auto p-3 flexImages'/>";
      echo "  </li>";

      echo " </ul>";
      echo "</div>";
      echo "</div>";
   }

   public static function displayChangeTitleModal()
   {
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
   }

   public static function displayCancelModal()
   {

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
   }
}
