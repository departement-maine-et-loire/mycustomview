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
            ['itemtype'  => 'PluginMyCustomViewUse',
                  'label'     => __('Modification (création et suppression)', 'mycustomview'),
                  'field'     => 'plugin_mycustomview_use',
                  'rights'    => [ALLSTANDARDRIGHT => __('Read')]]];
          return $rights;
    }

    function cleanProfiles($ID) {

        global $DB;
        $query = "DELETE FROM `glpi_profiles`
                  WHERE `profiles_id`='$ID'
                  AND `name` LIKE '%plugin_mycustomview%'";
        $DB->query($query);
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
        echo "<div class='firstbloc'>";
        if ($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE])) {
           $profile = new Profile();
           echo "<form method='post' action='".$profile->getFormURL()."'>";
        }
  
        $profile = new Profile();
        $profile->getFromDB($ID);
  
        $rights = self::getAllRights();
        $profile->displayRightsChoiceMatrix(
            $rights,
            [
               'canedit'       => $canedit,
               'default_class' => 'tab_bg_2',
               'title'         => __('General')
            ]
        );
        
        if ($canedit) {
           echo "<div class='center'>";
           echo Html::hidden('id', ['value' => $ID]);
           echo Html::submit(_sx('button', 'Save'), ['name' => 'update', 'class' => 'btn btn-primary']);
           echo "</div>\n";
           Html::closeForm();
        }
        echo "</div>";
    }
}