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

class PluginMycustomviewProfile extends Profile
{
    static $rightname = "profile";

    static function getAllRights() {
        $rights = [
            // N'est utile que si on prend en compte le droit de lecture
            // ['itemtype'  => 'PluginMyCustomViewRead',
            //       'label'     => __('Read', 'mycustomview'),
            //       'field'     => 'plugin_mycustomview_read',
            //       'rights'    => [READ => __('Read')]],
            ['itemtype'  => 'PluginMyCustomViewUse',
                  'label'     => __('Use', 'mycustomview'),
                  'field'     => 'plugin_mycustomview_use',
                  'rights'    => [READ => __('Read')]]];
          return $rights;
    }

    /**
     * Add an additional tab
     *
     * @param object $item         Ticket
     * @param int    $withtemplate 0
     * 
     * @return nametab
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            return __("Ma vue personnalisée", "mycustomview");
        }
        return '';
    }

    /**
     * If we are on profiles, an additional tab is displayed
     * 
     * @param object $item         Profile
     * @param int    $tabnum       1
     * @param int    $withtemplate 0
     * 
     * @return true
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            $profile = new self();
            $ID = $item->getField('id');

            $profile->showFormMcv($ID);
        }

        return true;
    }

    /**
     * @param $profile
     */
    static function addDefaultProfileInfos($profiles_id, $rights) {

        $profileRight = new ProfileRight();
        foreach ($rights as $right => $value) {
            if (!countElementsInTable(
                'glpi_profilerights',
                ['profiles_id' => $profiles_id, 'name' => $right]
            )) {
                $myright['profiles_id'] = $profiles_id;
                $myright['name']        = $right;
                $myright['rights']      = $value;
                $profileRight->add($myright);

                //Add right to the current session
                $_SESSION['glpiactiveprofile'][$right] = $value;
            }
        }
    }

    /**
     * @param $ID  integer
     */
    static function createFirstAccess($profiles_id) {

        include_once Plugin::getPhpDir('mycustomview')."/inc/profile.class.php";
        foreach (self::getAllRights() as $right) {
            // N'est utile que si on prend en compte le droit de lecture
            // self::addDefaultProfileInfos($profiles_id, ['plugin_mycustomview_read' => READ]);
            self::addDefaultProfileInfos($profiles_id, ['plugin_mycustomview_use' => ALLSTANDARDRIGHT]);
        }
    }

    public static function checkProfileRight($showProfileId) {
        global $DB;
        
        $result = $DB->request([
            'SELECT' => ['rights'],
            'FROM' => 'glpi_profilerights',
            'WHERE' => ['name' => ['LIKE', 'plugin_mycustomview%'], 'profiles_id' => $showProfileId],
            'ORDER' => 'rights DESC',
            'LIMIT' => 1
        ]);

        foreach($result as $data){
            return $data['rights'];
        }
    }

    public function showFormMcv($ID)
    {
        global $CFG_GLPI;

        $profiles_id = $_SESSION['glpiactiveprofile']['id'];

        $showProfileId = $_GET['id'];
        $checked = self::checkProfileRight($showProfileId);
        
        if (Session::haveRight("profile", UPDATE)) {
            echo "<form action='../plugins/mycustomview/inc/profile.php' method='post'>";
        }
        echo "<div align='center'>";
            echo "<table class='tab_cadre_fixehov'>";
                echo "<tr class='tab_bg_1'><th colspan='4'>" .__("Modifier les droits", "mycustomview") . "</th></tr>\n";
                // N'est utile que si on prend en compte le droit de lecture
                // echo "<tr class='tab_bg_2'>";
                //     echo "<td width='20%'>" .__("Lecture (affichage de la vue)", "mycustomview") . "</td>";
                //     echo "<td colspan='5'>";
                //     echo "<input type='checkbox' id='readProfileMcv' name='readProfileMcv' class='form-check-input' value='readProfileMcv'";
                //     if (!(Session::haveRight("profile", UPDATE))) {
                //         echo " disabled ";
                //     }
                //     if ($checked == 31 || $checked == 1) {
                //         echo "checked>";
                //     } else {
                //         echo ">";
                //     }

                //     echo "</td></tr>\n";
                echo "<tr class='tab_bg_2'>";
                    echo "<td width='20%'>" .__("Modification (création et suppression)", "mycustomview") . "</td>";
                    echo "<td colspan='5'>";
                    echo "<input type='checkbox' id='updateProfileMcv' name='updateProfileMcv' class='form-check-input' value = 'updateProfileMcv'";
                    if (!(Session::haveRight("profile", UPDATE))) {
                        echo " disabled ";
                    }
                    if ($checked == 31) {
                        echo "checked>";
                    } else {
                        echo ">";
                    }
                echo "</td></tr>\n";
            echo "</table>";

            echo Html::hidden("id_profil", ["value" => "$showProfileId"]);

            echo "<div class='center'>";
                echo Html::hidden('id', ['value' => $ID, 'id' => 'profileId']);
                if (Session::haveRight("profile", UPDATE)) {
                    echo Html::submit(_sx('button', 'Save'), ['name' => 'update', 'class' => 'btn btn-primary', 'id' => 'updateMcv']);
                }
            echo "</div>\n";
        echo "</div>";
        if (Session::haveRight("profile", UPDATE)){
            Html::closeForm();
        }

        // Permets de cocher ou décocher les droits en fonction de la sélection
        // N'est utile que si on prend en compte le droit de lecture
        // $js = "
        //     readProfileMcv = document.querySelector('#readProfileMcv')
        //     updateProfileMcv = document.querySelector('#updateProfileMcv')

        //     if (updateProfileMcv.checked) {
        //         readProfileMcv.checked = true
        //     }

        //     if (!readProfileMcv.checked) {
        //         updateProfileMcv.checked = false
        //     }

        //     readProfileMcv.addEventListener('change', function (event) {
        //         if (!readProfileMcv.checked) {
        //             updateProfileMcv.checked = false
        //         }
        //     })

        //     updateProfileMcv.addEventListener('change', function (event) {
        //         if (updateProfileMcv.checked) {
        //             readProfileMcv.checked = true
        //         }
        //     })
        // ";

        // echo Html::scriptBlock($js);
    }
}