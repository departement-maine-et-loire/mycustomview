<?php

/*
 -------------------------------------------------------------------------
 Servicecatalog plugin for GLPI
 Copyright (C) 2003-2019 by the Servicecatalog Development Team.

 https://forge.indepnet.net/projects/servicecatalog
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Servicecatalog.

 Servicecatalog is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Servicecatalog is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Servicecatalog. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMycustomviewDisplayPreference extends DisplayPreference
{

   function defineTabs($options = [])
   {

      $ong = [];
      $this->addStandardTab(__CLASS__, $ong, $options);
      $ong['no_all_tab'] = true;
      return $ong;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      if ($item->getType() == 'PluginMycustomviewDisplayPreference') {
         return "Ma vue personnalisée";
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      global $CFG_GLPI;

      $pref = new self();
      $pref->showFormPersoMcv('/plugins/mycustomview/front/displaypreference.form.php',  $_GET["displaytype"]);
      return true;
   }

   static function showForm()
   {
      $pref = new self();
      $pref->showFormPersoMcv('/plugins/mycustomview/front/displaypreference.form.php',  $_GET["displaytype"]);
   }




   /**
    * Print the search config form
    *
    * @param $target    form target
    * @param $itemtype  item type
    *
    * @return nothing
    **/
   function showFormPersoMcv($target, $itemtype)
   {
      global $CFG_GLPI, $DB;

      $searchopt = Search::getCleanedOptions($itemtype);
      if (!is_array($searchopt)) {
         return false;
      }

      $item = null;
      if ($itemtype != 'AllAssets') {
         $item = getItemForItemtype($itemtype);
      }

      $IDuser = Session::getLoginUserID();

      echo "<div class='center' id='tabsbody' >";
      // Defined items
      $iterator = $DB->request([
         'FROM'   => $this->getTable(),
         'WHERE'  => [
            'itemtype'  => $itemtype,
            'users_id'  => $IDuser
         ],
         'ORDER'  => 'rank'
      ]);

      $numrows = count($iterator);

      if ($numrows == 0) {
         echo "<table class='tab_cadre_fixe'><tr><th colspan='4'>";
         echo "<form method='post' action='$target'>";
         echo "<input type='hidden' name='itemtype' value='$itemtype'>";
         echo "<input type='hidden' name='users_id' value='$IDuser'>";
         echo __('No personal criteria. Create personal parameters?') . "<span class='small_space'>";
         echo "<input type='submit' name='activate' value=\"" . __('Create') . "\"
              class='submit'>";
         echo "</span>";
         Html::closeForm();
         echo "</th></tr></table>\n";
      } else {
         $already_added = self::getForTypeUser($itemtype, $IDuser);

         echo "<table class='tab_cadre_fixe'><tr><th colspan='4'>";
         echo "<form method='post' action='$target'>";
         echo "<input type='hidden' name='itemtype' value='$itemtype'>";
         echo "<input type='hidden' name='users_id' value='$IDuser'>";
         echo __('Select default items to show') . "<span class='small_space'>";
         echo "<input type='submit' name='disable' value=\"" . __('Delete') . "\"
              class='submit'>";
         echo "</span>";
         Html::closeForm();
         echo "</th></tr>";
         echo "<tr class='tab_bg_1'><td colspan='4' class='center'>";
         echo "<form method='post' action=\"$target\">";
         echo "<input type='hidden' name='itemtype' value='$itemtype'>";
         echo "<input type='hidden' name='users_id' value='$IDuser'>";
         $group  = '';
         $values = [];
         foreach ($searchopt as $key => $val) {
            if (!is_array($val)) {
               $group = $val;
            } else if (count($val) === 1) {
               $group = $val['name'];
            } else if (
               $key != 1
               && !in_array($key, $already_added)
               && (!isset($val['nodisplay']) || !$val['nodisplay'])
            ) {
               $values[$group][$key] = $val["name"];
            }
         }

         if ($values) {
            Dropdown::showFromArray('num', $values);
            echo "<span class='small_space'>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</span>";
         }
         Html::closeForm();
         echo "</td></tr>\n";

         // print first element
         echo "<tr class='tab_bg_2'>";
         echo "<td class='center' width='50%'>" . $searchopt[1]["name"] . "</td>";
         echo "<td colspan='3'>&nbsp;</td>";
         echo "</tr>";

         // print entity
         if (
            Session::isMultiEntitiesMode()
            && (isset($CFG_GLPI["union_search_type"][$itemtype])
               || ($item && $item->maybeRecursive())
               || (count($_SESSION["glpiactiveentities"]) > 1))
            && isset($searchopt[80])
         ) {

            echo "<tr class='tab_bg_2'>";
            echo "<td class='center' width='50%'>" . $searchopt[80]["name"] . "</td>";
            echo "<td colspan='3'>&nbsp;</td>";
            echo "</tr>";
         }

         $i = 0;
         if ($numrows) {
            while ($data = $iterator->next()) {
               if (($data["num"] != 1) && isset($searchopt[$data["num"]])) {
                  echo "<tr class='tab_bg_2'>";
                  echo "<td class='center' width='50%' >";
                  echo $searchopt[$data["num"]]["name"] . "</td>";

                  if ($i != 0) {
                     echo "<td class='center middle'>";
                     echo "<form method='post' action='$target'>";
                     echo "<input type='hidden' name='id' value='" . $data["id"] . "'>";
                     echo "<input type='hidden' name='users_id' value='$IDuser'>";
                     echo "<input type='hidden' name='itemtype' value='$itemtype'>";
                     echo "<button type='submit' name='up'" .
                        " title=\"" . __s('Bring up') . "\"" .
                        " class='unstyled pointer'><i class='fa fa-arrow-up'></i></button>";
                     Html::closeForm();
                     echo "</td>\n";
                  } else {
                     echo "<td>&nbsp;</td>";
                  }

                  if ($i != ($numrows - 1)) {
                     echo "<td class='center middle'>";
                     echo "<form method='post' action='$target'>";
                     echo "<input type='hidden' name='id' value='" . $data["id"] . "'>";
                     echo "<input type='hidden' name='users_id' value='$IDuser'>";
                     echo "<input type='hidden' name='itemtype' value='$itemtype'>";
                     echo "<button type='submit' name='down'" .
                        " title=\"" . __s('Bring down') . "\"" .
                        " class='unstyled pointer'><i class='fa fa-arrow-down'></i></button>";
                     Html::closeForm();
                     echo "</td>\n";
                  } else {
                     echo "<td>&nbsp;</td>";
                  }

                  if (!isset($searchopt[$data["num"]]["noremove"]) || $searchopt[$data["num"]]["noremove"] !== true) {

                     echo "<td class='center middle'>";
                     echo "<form method='post' action='$target'>";
                     echo "<input type='hidden' name='id' value='" . $data["id"] . "'>";
                     echo "<input type='hidden' name='users_id' value='$IDuser'>";
                     echo "<input type='hidden' name='itemtype' value='$itemtype'>";
                     echo "<button type='submit' name='purge'" .
                        " title=\"" . _sx('button', 'Delete permanently') . "\"" .
                        " class='unstyled pointer'><i class='fa fa-times-circle'></i></button>";
                     Html::closeForm();
                     echo "</td>\n";
                  } else {
                     echo "<td>&nbsp;</td>\n";
                  }
                  echo "</tr>";
                  $i++;
               }
            }
         }
         echo "</table>";
      }
      echo "</div>";
   }



   /**
    * Active personal config based on global one
    *
    * @param $input  array parameter (itemtype,users_id)
    **/
   function activatePersoMcv(array $input)
   {
      global $DB;

      if (!Session::haveRight(self::$rightname, self::PERSONAL)) {
         return false;
      }

      $iterator = $DB->request([
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'itemtype'  => $input['itemtype'],
            'users_id'  => 0
         ]
      ]);

      if (count($iterator)) {
         while ($data = $iterator->next()) {
            unset($data["id"]);
            $data["users_id"] = $input["users_id"];
            $this->fields     = $data;
            $this->addToDB();
         }
      } else {
         // No items in the global config
         $searchopt = Search::getOptions($input["itemtype"]);
         if (count($searchopt) > 1) {
            $done = false;

            foreach ($searchopt as $key => $val) {
               if (
                  is_array($val)
                  && ($key != 1)
                  && !$done
               ) {

                  $data["users_id"] = $input["users_id"];
                  $data["itemtype"] = $input["itemtype"];
                  $data["rank"]     = 1;
                  $data["num"]      = $key;
                  $this->fields     = $data;
                  $this->addToDB();
                  $done = true;
               }
            }
         }
      }
   }
}
