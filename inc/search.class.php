<?php

/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2018 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * Search Class
 *
 * Generic class for Search Engine
 **/
class PluginMycustomviewSearch extends Search
{

   /**
    * Display result table for search engine for an type
    *
    * @param $itemtype item type to manage
    * @param $params search params passed to prepareDatasForSearch function
    *
    * @return nothing
    **/
   static function showListMcv($itemtype, $params)
   {
      $data = self::prepareDatasForSearch($itemtype, $params);
      self::constructSQL($data);
      // print_r($data);
      self::constructData($data);
      self::displayDataMcv($data);
   }



   /**
    * Display datas extracted from DB
    *
    * @param $data array of search datas prepared to get datas
    *
    * @return nothing
    **/
   static function displayDataMcv(array &$data)
   {
      global $CFG_GLPI;
      global $indexBloc;

      // Ticket::showVeryShort(35915, 'TicketTask');

      $item = null;
      if (class_exists($data['itemtype'])) {
         $item = new $data['itemtype']();
      }

      if (!isset($data['data']) || !isset($data['data']['totalcount'])) {
         return false;
      }
      // Contruct Pager parameters
      $globallinkto
         = Toolbox::append_params(
            [
               'criteria'
               => Toolbox::stripslashes_deep($data['search']['criteria']),
               'metacriteria'
               => Toolbox::stripslashes_deep($data['search']['metacriteria'])
            ],
            '&amp;'
         );

      $parameters = "sort=" . $data['search']['sort'] . "&amp;order=" . $data['search']['order'] . '&amp;' .
         $globallinkto;

      if (isset($_GET['_in_modal'])) {
         $parameters .= "&amp;_in_modal=1";
      }

      // // Global search header
      // if ($data['display_type'] == self::GLOBAL_SEARCH) {
      //    if ($data['item']) {
      //       echo "<div class='center'><h2>".$data['item']->getTypeName();
      //       // More items
      //       if ($data['data']['totalcount'] > ($data['search']['start'] + self::GLOBAL_DISPLAY_COUNT)) {
      //          echo " <a href='".$data['search']['target']."?$parameters'>".__('All')."</a>";
      //       }
      //       echo "</h2></div>\n";
      //    } else {
      //       return false;
      //    }
      // }

      // If the begin of the view is before the number of items
      if ($data['data']['count'] > 0) {
         // Display pager only for HTML
         // if ($data['display_type'] == self::HTML_OUTPUT) {
         //    // For plugin add new parameter if available
         //    if ($plug = isPluginItemType($data['itemtype'])) {
         //       $function = 'plugin_'.$plug['plugin'].'_addParamFordynamicReport';

         //       if (function_exists($function)) {
         //          $out = $function($data['itemtype']);
         //          if (is_array($out) && count($out)) {
         //             $parameters .= Toolbox::append_params($out, '&amp;');
         //          }
         //       }
         //    }
         $search_config_top    = "";
         $search_config_bottom = "";
         if (!isset($_GET['_in_modal'])) {

            $search_config_top = $search_config_bottom
               = "<div class='pager_controls'>";

            //       // $map_link = '';
            //       // if (null == $item || $item->maybeLocated()) {
            //       //    $map_link = "<input type='checkbox' name='as_map' id='as_map' value='1'";
            //       //    if ($data['search']['as_map'] == 1) {
            //       //       $map_link .= " checked='checked'";
            //       //    }
            //       //    $map_link .= "/>";
            //       //    $map_link .= "<label for='as_map'><span title='".__s('Show as map')."' class='pointer fa fa-globe-americas'
            //       //       onClick=\"toogle('as_map','','','');
            //       //                   document.forms['searchform".$data["itemtype"]."'].submit();\"></span></label>";
            //       // }
            //       // $search_config_top .= $map_link;

            if (Session::haveRightsOr('search_config', [
               DisplayPreference::PERSONAL,
               DisplayPreference::GENERAL
            ])) {
               $iframeName = 'search_config_top' . $indexBloc;
               $options_link = "<span class='fa fa-wrench pointer' title='" .
                  __s('Select default items to show') . "' onClick=\"$('#%id').dialog('open');\">
                     <span class='sr-only'>" .  __s('Select default items to show') . "</span></span>";

               $search_config_top .= str_replace('%id', $iframeName, $options_link);
               $search_config_bottom .= str_replace('%id', 'search_config_bottom', $options_link);

               $pref_url = $CFG_GLPI["root_doc"] . "/plugins/mycustomview/front/displaypreference.form.php?itemtype=" .
                  $data['itemtype'];
               $search_config_top .= Ajax::createIframeModalWindow(
                  $iframeName,
                  $pref_url,
                  [
                     'title'         => __('Select default items to show'),
                     'reloadonclose' => true,
                     'display'       => false
                  ]
               );
               $search_config_bottom .= Ajax::createIframeModalWindow(
                  'search_config_bottom',
                  $pref_url,
                  [
                     'title'         => __('Select default items to show'),
                     'reloadonclose' => true,
                     'display'       => false
                  ]
               );
            }
         }


         //    if ($item !== null && $item->maybeDeleted()) {
         //       // $delete_ctrl        = self::isDeletedSwitch($data['search']['is_deleted'], $data['itemtype']);
         //       // $search_config_top .= $delete_ctrl;
         //    }

         self::printPager(
            $data['search']['start'],
            $data['data']['totalcount'],
            $data['search']['target'],
            $parameters,
            $data['itemtype'],
            0,
            $search_config_top
         );

         $search_config_top    .= "</div>";
         $search_config_bottom .= "</div>";
         // }

         // Define begin and end var for loop
         // Search case
         $begin_display = $data['data']['begin'];
         $end_display   = $data['data']['end'];

         // Form to massive actions
         $isadmin = ($data['item'] && $data['item']->canUpdate());
         if (
            !$isadmin
            && InfoCom::canApplyOn($data['itemtype'])
         ) {
            $isadmin = (Infocom::canUpdate() || Infocom::canCreate());
         }
         if ($data['itemtype'] != 'AllAssets') {
            $showmassiveactions
               = count(MassiveAction::getAllMassiveActions(
                  $data['item'],
                  $data['search']['is_deleted']
               ));
         } else {
            $showmassiveactions = true;
         }

         if ($data['search']['as_map'] == 0) {
            $massformid = 'massform' . $data['itemtype'];
            if (
               $showmassiveactions
               && ($data['display_type'] == self::HTML_OUTPUT)
            ) {

               // Html::openMassiveActionsForm($massformid);
               // $massiveactionparams                  = $data['search']['massiveactionparams'];
               // $massiveactionparams['num_displayed'] = $end_display-$begin_display;
               // $massiveactionparams['fixed']         = false;
               // $massiveactionparams['is_deleted']    = $data['search']['is_deleted'];
               // $massiveactionparams['container']     = $massformid;

               // Html::showMassiveActions($massiveactionparams);
            }


            // SYSTEME DE MENAGE MANUEL (AVANT L'AJOUT DE LA FONCTIONNALITE DE DISPLAY PREFERENCE)
            // ----------------------------------------------------------
            // $keepValuesTabTicket = [
            //    __s('ID'), __s('Title'), __s('Requester')
            // ];

            // $keepValuesTabComputer = [
            //    __s('Name'), __s('Status'), 'Numéro de série', 'Modèle', __s('Location'), 'FusInv - Dernier inventaire', __s('User')
            // ];
            // $removeTabComputer = [
            //    __s('name')
            // ];
            // //Si c'est un ticket, on fait une refonte du tableau
            // if ($data['data']['cols'][0]['itemtype'] == 'Ticket') {

            //    $data = self::reformTabMcv($data, $keepValuesTabTicket);
            // }

            // //Si c'est un ordinateur, on fait une refonte du tableau plus poussée
            // if ($data['data']['cols'][0]['itemtype'] == 'Computer') {
            //    // Pour enlever le système d'opération
            //    foreach ($data['data']['cols'] as $key => $newTab) {
            //       if (isset($newTab['groupname']['name'])) {
            //          if (($newTab['groupname']['name'] == 'Système d\'exploitation') and ($newTab['name'] == 'Nom')) {
            //             unset($data['data']['cols'][$key]);
            //          }
            //       }
            //    }
            //    $data = self::reformTabMcv($data, $keepValuesTabComputer, $removeTabComputer);
            // }

            // ----------------------------------------------------------
            // ----------------------------------------------------------
            $nbTabFields = 0;

            foreach ($data['data']['cols'] as $key => $newTab) {
               //                if ($newTab['name'] == 'Description') {
               $nbTabFields++;
               // print_r($data['data']['cols'][$key]);
               // }
            }
          

            // Compute number of columns to display
            // Add toview elements
            $nbcols          = count($data['data']['cols']);

            // if (($data['display_type'] == self::HTML_OUTPUT)
            //    && $showmassiveactions) { // HTML display - massive modif
            //    $nbcols++;
            // }

            // Display List Header

            echo self::showHeader($data['display_type'], $end_display - $begin_display + 1, $nbcols);

            // New Line for Header Items Line
            $headers_line        = '';
            $headers_line_top    = '';
            $headers_line_bottom = '';

            $headers_line_top .= self::showBeginHeader($data['display_type']);
            $headers_line_top .= self::showNewLine($data['display_type']);

            if ($data['display_type'] == self::HTML_OUTPUT) {
               // $headers_line_bottom .= self::showBeginHeader($data['display_type']);
               $headers_line_bottom .= self::showNewLine($data['display_type']);
            }

            $header_num = 1;


            // CHECKBOX DANS LE HEADER 

            // if (($data['display_type'] == self::HTML_OUTPUT)
            //       && $showmassiveactions) { // HTML display - massive modif
            //    $headers_line_top
            //       .= self::showHeaderItem($data['display_type'],
            //                               Html::getCheckAllAsCheckbox($massformid),
            //                               $header_num, "", 0, $data['search']['order']);
            //    if ($data['display_type'] == self::HTML_OUTPUT) {
            //       $headers_line_bottom
            //          .= self::showHeaderItem($data['display_type'],
            //                                  Html::getCheckAllAsCheckbox($massformid),
            //                                  $header_num, "", 0, $data['search']['order']);
            //    }
            // }

            // Display column Headers for toview items
            $metanames = [];

            foreach ($data['data']['cols'] as $val) {
               $linkto = '';
               if (
                  !$val['meta']
                  && (!isset($val['searchopt']['nosort'])
                     || !$val['searchopt']['nosort'])
               ) {

                  $linkto = $data['search']['target'] . (strpos($data['search']['target'], '?') ? '&amp;' : '?') .
                     "itemtype=" . $data['itemtype'] . "&amp;sort=" .
                     $val['id'] . "&amp;order=" .
                     (($data['search']['order'] == "ASC") ? "DESC" : "ASC") .
                     "&amp;start=" . $data['search']['start'] . "&amp;" . $globallinkto;
               }

               $name = $val["name"];
               // var_dump($name);

               // prefix by group name (corresponding to optgroup in dropdown) if exists
               if (isset($val['groupname'])) {
                  $groupname = $val['groupname'];
                  if (is_array($groupname)) {
                     //since 9.2, getSearchOptions has been changed
                     $groupname = $groupname['name'];
                  }
                  $name  = "$groupname - $name";
               }

               // Not main itemtype add itemtype to display
               if ($data['itemtype'] != $val['itemtype']) {
                  if (!isset($metanames[$val['itemtype']])) {
                     if ($metaitem = getItemForItemtype($val['itemtype'])) {
                        $metanames[$val['itemtype']] = $metaitem->getTypeName();
                     }
                  }
                  $name = sprintf(
                     __('%1$s - %2$s'),
                     $metanames[$val['itemtype']],
                     $val["name"]
                  );
               }
               $headers_line .= self::showHeaderItem(
                  $data['display_type'],
                  $name,
                  $header_num,
                  $linkto,
                  (!$val['meta']
                     && ($data['search']['sort'] == $val['id'])),
                  $data['search']['order']
               );
            }

            // Add specific column Header
            if (isset($CFG_GLPI["union_search_type"][$data['itemtype']])) {
               $headers_line .= self::showHeaderItem(
                  $data['display_type'],
                  __('Item type'),
                  $header_num
               );
            }
            // End Line for column headers
            $headers_line        .= self::showEndLine($data['display_type']);

            $headers_line_top    .= $headers_line;
            if ($data['display_type'] == self::HTML_OUTPUT) {
               
               // DECOMMENTER POUR RE-AVOIR LE FOOTER
               // Ajout de balises tbody et tfoot pour régler le problème de sorting JS
               // $headers_line_bottom .= "</tbody><tfoot>";
               // $headers_line_bottom .= $headers_line;
            }

            $headers_line_top    .= self::showEndHeader($data['display_type']);
            // $headers_line_bottom .= self::showEndHeader($data['display_type']);

            echo $headers_line_top;

            // Init list of items displayed
            if ($data['display_type'] == self::HTML_OUTPUT) {
               Session::initNavigateListItems($data['itemtype']);
            }

            // Num of the row (1=header_line)
            $row_num = 1;

            $massiveaction_field = 'id';
            if (($data['itemtype'] != 'AllAssets')
               && isset($CFG_GLPI["union_search_type"][$data['itemtype']])
            ) {
               $massiveaction_field = 'refID';
            }

            $typenames = [];
            // Display Loop
            foreach ($data['data']['rows'] as $rowkey => $row) {
               // Column num
               $item_num = 1;
               $row_num++;
               // New line
               echo self::showNewLine(
                  $data['display_type'],
                  ($row_num % 2),
                  $data['search']['is_deleted']
               );

               $current_type       = (isset($row['TYPE']) ? $row['TYPE'] : $data['itemtype']);
               $massiveaction_type = $current_type;

               if (($data['itemtype'] != 'AllAssets')
                  && isset($CFG_GLPI["union_search_type"][$data['itemtype']])
               ) {
                  $massiveaction_type = $data['itemtype'];
               }

               // Add item in item list
               Session::addToNavigateListItems($current_type, $row["id"]);


               // CHECKBOX DE CHAQUE TR 


               // if (($data['display_type'] == self::HTML_OUTPUT)
               //       && $showmassiveactions) { // HTML display - massive modif
               //    $tmpcheck = "";

               //    if (($data['itemtype'] == 'Entity')
               //          && !in_array($row["id"], $_SESSION["glpiactiveentities"])) {
               //       $tmpcheck = "&nbsp;";

               //    } else if ($data['itemtype'] == 'User'
               //             && !Session::canViewAllEntities()
               //             && !Session::haveAccessToOneOfEntities(Profile_User::getUserEntities($row["id"], false))) {
               //       $tmpcheck = "&nbsp;";

               //    } else if (($data['item'] instanceof CommonDBTM)
               //                && $data['item']->maybeRecursive()
               //                && !in_array($row["entities_id"], $_SESSION["glpiactiveentities"])) {
               //       $tmpcheck = "&nbsp;";

               //    } else {
               //       $tmpcheck = Html::getMassiveActionCheckBox($massiveaction_type,
               //                                                 $row[$massiveaction_field]);
               //    }
               //    echo self::showItem($data['display_type'], $tmpcheck, $item_num, $row_num,
               //                         "width='10'");
               // }

               // Print other toview items
               foreach ($data['data']['cols'] as $col) {
                  $colkey = "{$col['itemtype']}_{$col['id']}";
                  if (!$col['meta']) {
                     echo self::showItem(
                        $data['display_type'],
                        $row[$colkey]['displayname'],
                        $item_num,
                        $row_num,
                        self::displayConfigItem(
                           $data['itemtype'],
                           $col['id'],
                           $row,
                           $colkey
                        )
                     );
                  } else { // META case
                     echo self::showItem(
                        $data['display_type'],
                        $row[$colkey]['displayname'],
                        $item_num,
                        $row_num
                     );
                  }
               }

               if (isset($CFG_GLPI["union_search_type"][$data['itemtype']])) {
                  if (!isset($typenames[$row["TYPE"]])) {
                     if ($itemtmp = getItemForItemtype($row["TYPE"])) {
                        $typenames[$row["TYPE"]] = $itemtmp->getTypeName();
                     }
                  }
                  echo self::showItem(
                     $data['display_type'],
                     $typenames[$row["TYPE"]],
                     $item_num,
                     $row_num
                  );
               }
               // End Line
               echo self::showEndLine($data['display_type']);
               // Flush ONLY for an HTML display (issue #3348)
               if ($data['display_type'] == self::HTML_OUTPUT) {
                  Html::glpi_flush();
               }
            }

            // Create title
            $title = '';
            if (($data['display_type'] == self::PDF_OUTPUT_LANDSCAPE)
               || ($data['display_type'] == self::PDF_OUTPUT_PORTRAIT)
            ) {
               $title = self::computeTitle($data);
            }

            if ($data['display_type'] == self::HTML_OUTPUT) {
               echo $headers_line_bottom;
            }
            // Display footer
            echo self::showFooter($data['display_type'], $title, $data['data']['count']);

            // Delete selected item
            // if ($data['display_type'] == self::HTML_OUTPUT) {
            //    if ($showmassiveactions) {
            //       $massiveactionparams['ontop'] = false;
            //       Html::showMassiveActions($massiveactionparams);
            //       // End form for delete item
            //       Html::closeForm();
            //    } else {
            //       echo "<br>";
            //    }
            // }
            // if ($data['display_type'] == self::HTML_OUTPUT) { // In case of HTML display
            //    Html::printPager($data['search']['start'], $data['data']['totalcount'],
            //                   $data['search']['target'], $parameters, '', 0,
            //                      $search_config_bottom);

            // }
         }
      } else {
         if (!isset($_GET['_in_modal'])) {
            echo "<div class='center pager_controls'>";
            if (null == $item || $item->maybeLocated()) {
               $map_link = "<input type='checkbox' name='as_map' id='as_map' value='1'";
               if ($data['search']['as_map'] == 1) {
                  $map_link .= " checked='checked'";
               }
               $map_link .= "/>";
               // $map_link .= "<label for='as_map'><span title='" . __s('Show as map') . "' class='pointer fa fa-globe-americas'
               // onClick=\"toogle('as_map','','','');
               // document.forms['searchform" . $data["itemtype"] . "'].submit();\"></span></label>";
               echo $map_link;
            }

            if ($item !== null && $item->maybeDeleted()) {
               // echo self::isDeletedSwitch($data['search']['is_deleted'], $data['itemtype']);
            }
            echo "</div>";
         }
         echo self::showError($data['display_type']);
      }
   }

   public static function reformTabMcv($data, $keepValuesTab, $removeTab = null)
   {
      foreach ($data['data']['cols'] as $key => $newTab) {
         $delete = false;
         $match = false;
         // suppression forcée
         if (isset($removeTab)) {
            foreach ($removeTab as $removeValue) {
               if ($newTab['name'] == $removeValue) {
                  $delete = true;
               }
               if ($delete == true) {
                  unset($data['data']['cols'][$key]);
               }
            }
         }
         // on garde uniquement les éléments nécessaires
         foreach ($keepValuesTab as $keepValue) {
            if ($newTab['name'] == $keepValue) {
               $match = true;
            }
         }
         if ($match == false) {
            unset($data['data']['cols'][$key]);
         }
      }
      return $data;
   }


   /**
    * Prepare search criteria to be used for a search
    *
    * @since 0.85
    *
    * @param $itemtype            item type                                                                                                                                                                      ,nbjh   ;gvv   !:ù
    * @param $params        array of parameters
    *                             may include sort, order, start, list_limit, deleted, criteria, metacriteria
    * @param $forcedisplay  array of columns to display (default empty = empty use display pref and search criterias)
    *
    * @return array prepare to be used for a search (include criterias and others needed informations)
    **/
   static function prepareDatasForSearch($itemtype, array $params, array $forcedisplay = [])
   {
      global $CFG_GLPI;

      // Default values of parameters
      $p['criteria']            = [];
      $p['metacriteria']        = [];
      $p['sort']                = '2'; //
      $p['order']               = 'DESC'; //
      $p['start']               = 0; //
      $p['is_deleted']          = 0;
      $p['export_all']          = 0;
      if (class_exists($itemtype)) {
         $p['target']       = $itemtype::getSearchURL();
      } else {
         $p['target']       = Toolbox::getItemTypeSearchURL($itemtype);
      }
      $p['display_type']        = self::HTML_OUTPUT;
      $p['list_limit']          = $_SESSION['glpilist_limit'];
      $p['massiveactionparams'] = [];

      foreach ($params as $key => $val) {
         switch ($key) {
            case 'order':
               if (in_array($val, ['ASC', 'DESC'])) {
                  $p[$key] = $val;
               }
               break;
            case 'sort':
               $p[$key] = intval($val);
               if ($p[$key] <= 0) {
                  $p[$key] = 1;
               }
               break;
            case 'is_deleted':
               if ($val == 1) {
                  $p[$key] = '1';
               }
               break;
            default:
               $p[$key] = $val;
               break;
         }
      }

      // Set display type for export if define
      if (isset($p['display_type'])) {
         // Limit to 10 element
         if ($p['display_type'] == self::GLOBAL_SEARCH) {
            $p['list_limit'] = self::GLOBAL_DISPLAY_COUNT;
         }
      }

      if ($p['export_all']) {
         $p['start'] = 0;
      }

      $data             = [];
      $data['search']   = $p;
      $data['itemtype'] = $itemtype;

      // Instanciate an object to access method
      $data['item'] = null;

      if ($itemtype != 'AllAssets') {
         $data['item'] = getItemForItemtype($itemtype);
      }

      $data['display_type'] = $data['search']['display_type'];

      if (!$CFG_GLPI['allow_search_all']) {
         foreach ($p['criteria'] as $val) {
            if (isset($val['field']) && $val['field'] == 'all') {
               Html::displayRightError();
            }
         }
      }
      if (!$CFG_GLPI['allow_search_view']) {
         foreach ($p['criteria'] as $val) {
            if (isset($val['field']) && $val['field'] == 'view') {
               Html::displayRightError();
            }
         }
      }

      /// Get the items to display
      // Add searched items

      $forcetoview = false;
      if (is_array($forcedisplay) && count($forcedisplay)) {
         $forcetoview = true;
      }
      $data['search']['all_search']  = false;
      $data['search']['view_search'] = false;
      // If no research limit research to display item and compute number of item using simple request
      $data['search']['no_search']   = true;

      $data['toview'] = self::addDefaultToView($itemtype, $params);
      $data['meta_toview'] = [];
      if (!$forcetoview) {
         // Add items to display depending of personal prefs
         $displaypref = PluginMycustomviewDisplayPreference::getForTypeUser($itemtype, Session::getLoginUserID());
         if (count($displaypref)) {
            foreach ($displaypref as $val) {
               array_push($data['toview'], $val);
            }
         }
      } else {
         $data['toview'] = array_merge($data['toview'], $forcedisplay);
      }

      if (count($p['criteria']) > 0) {
         // use a recursive clojure to push searchoption when using nested criteria
         $parse_criteria = function ($criteria) use (&$parse_criteria, &$data) {
            foreach ($criteria as $criterion) {
               // recursive call
               if (isset($criterion['criteria'])) {
                  $parse_criteria($criterion['criteria']);
               } else {
                  // normal behavior
                  if (
                     isset($criterion['field'])
                     && !in_array($criterion['field'], $data['toview'])
                  ) {
                     if (
                        $criterion['field'] != 'all'
                        && $criterion['field'] != 'view'
                        && (!isset($criterion['meta'])
                           || !$criterion['meta'])
                     ) {
                        array_push($data['toview'], $criterion['field']);
                     } else if ($criterion['field'] == 'all') {
                        $data['search']['all_search'] = true;
                     } else if ($criterion['field'] == 'view') {
                        $data['search']['view_search'] = true;
                     }
                  }

                  if (
                     isset($criterion['value'])
                     && (strlen($criterion['value']) > 0)
                  ) {
                     $data['search']['no_search'] = false;
                  }
               }
            }
         };

         // call the clojure
         $parse_criteria($p['criteria']);
      }

      if (count($p['metacriteria'])) {
         $data['search']['no_search'] = false;
      }

      // Add order item
      if (!in_array($p['sort'], $data['toview'])) {
         array_push($data['toview'], $p['sort']);
      }

      // Special case for Ticket : put ID in front
      if ($itemtype == 'Ticket') {
         array_unshift($data['toview'], 2);
      }

      $limitsearchopt   = self::getCleanedOptions($itemtype);
      // Clean and reorder toview
      $tmpview = [];
      foreach ($data['toview'] as $val) {
         if (isset($limitsearchopt[$val]) && !in_array($val, $tmpview)) {
            $tmpview[] = $val;
         }
      }
      $data['toview']    = $tmpview;
      $data['tocompute'] = $data['toview'];

      // Force item to display
      if ($forcetoview) {
         foreach ($data['toview'] as $val) {
            if (!in_array($val, $data['tocompute'])) {
               array_push($data['tocompute'], $val);
            }
         }
      }

      return $data;
   }

   /**
    * Print generic Header Column
    *
    * @param $type            display type (0=HTML, 1=Sylk,2=PDF,3=CSV)
    * @param $value           value to display
    * @param &$num            column number
    * @param $linkto          link display element (HTML specific) (default '')
    * @param $issort          is the sort column ? (default 0)
    * @param $order           order type ASC or DESC (defaut '')
    * @param $options  string options to add (default '')
    *
    * @return string to display
    **/
   static function showHeaderItem(
      $type,
      $value,
      &$num,
      $linkto = "",
      $issort = 0,
      $order = "",
      $options = ""
   ) {
      $out = "";
      switch ($type) {
         case self::PDF_OUTPUT_LANDSCAPE: //pdf

         case self::PDF_OUTPUT_PORTRAIT:
            global $PDF_TABLE;
            $PDF_TABLE .= "<th $options>";
            $PDF_TABLE .= Html::clean($value);
            $PDF_TABLE .= "</th>\n";
            break;

         case self::SYLK_OUTPUT: //sylk
            global $SYLK_HEADER, $SYLK_SIZE;
            $SYLK_HEADER[$num] = self::sylk_clean($value);
            $SYLK_SIZE[$num]   = Toolbox::strlen($SYLK_HEADER[$num]);
            break;

         case self::CSV_OUTPUT: //CSV
            $out = "\"" . self::csv_clean($value) . "\"" . $_SESSION["glpicsv_delimiter"];
            break;

         default:
            $class = "";
            if ($issort) {
               $class = "order_$order";
            }
            $out = "<th $options class='$class'>";

            $out .= $value;

            $out .= "</th>\n";
      }
      $num++;
      return $out;
   }

   
   /**
    * Print pager for search option (first/previous/next/last)
    *
    * @param $start                       from witch item we start
    * @param $numrows                     total items
    * @param $target                      page would be open when click on the option (last,previous etc)
    * @param $parameters                  parameters would be passed on the URL.
    * @param $item_type_output            item type display - if >0 display export PDF et Sylk form
    *                                     (default 0)
    * @param $item_type_output_param      item type parameter for export (default 0)
    * @param $additional_info             Additional information to display (default '')
    *
    * @return nothing (print a pager)
    *
   **/
  static function printPager($start, $numrows, $target, $parameters, $item_type_output = 0,
  $item_type_output_param = 0, $additional_info = '') {
global $CFG_GLPI;

$list_limit = $_SESSION['glpilist_limit'];

// Human readable count starts here

$current_start = $start+1;

// And the human is viewing from start to end
$current_end = $current_start+$list_limit-1;
if ($current_end > $numrows) {
$current_end = $numrows;
}

// Empty case
if ($current_end == 0) {
$current_start = 0;
}

// Print it
echo "<div><table class='tab_cadre_pager'>";
echo "<tr>";

if (!empty($additional_info)) {
echo "<td class='tab_bg_2' width='15%'>";
echo $additional_info;
echo "</td>";
}

echo "<td width='20%' class='tab_bg_2 b' style='text-align:left'>";
//TRANS: %1$d, %2$d, %3$d are page numbers
printf(__('From %1$d to %2$d of %3$d'), $current_start, $current_end, $numrows);
echo "</td>\n";

// End pager
echo "</tr></table></div>";
}

}
