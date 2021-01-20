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

class PluginMycustomviewProfile extends Profile
{

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if ($item->getType() == 'Profile') {
            return "Ma vue personnalisée";
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == 'Profile') {
            $ID   = $item->getID();
            $profile = new self();
            if (!isset($_SESSION['glpi_plugin_mycustomview_profile']['id'])) {
                PluginMycustomviewProfileRights::changeProfile();
            }
            $profile->showFormMcv($ID);
        }

        return true;
    }

    public function showFormMcv($ID)
    {
        global $CFG_GLPI;

        $profiles_id = $_SESSION['glpiactiveprofile']['id'];

        echo "<div align='center'>";
        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr class='tab_bg_1'><th colspan='4'>Modifier les droits</th></tr>\n";
        echo "<tr class='tab_bg_2'>";
        echo "<td width='20%'>Lecture (affichage de la vue)</td>";
        echo "<td colspan='5'>";
        echo "<input type='checkbox' id='readProfileMcv' name='readProfileMcv'";
        if (!(PluginMycustomviewProfileRights::isSuperAdmin())) {
            echo " disabled ";
        }
        if (PluginMycustomviewProfileRights::canProfileView($ID)) {
            echo "checked>";
        } else {
            echo ">";
        }

        echo "</td></tr>\n";
        echo "<tr class='tab_bg_2'>";
        echo "<td width='20%'>Modification (création et suppression)</td>";
        echo "<td colspan='5'>";
        echo "<input type='checkbox' id='updateProfileMcv' name='updateProfileMcv'";
        if (!(PluginMycustomviewProfileRights::isSuperAdmin())) {
            echo " disabled ";
        }
        if (PluginMycustomviewProfileRights::canProfileUpdate($ID)) {
            echo "checked>";
        } else {
            echo ">";
        }
        echo "</td></tr>\n";
        echo "</table>";
        echo "</table>";
        echo "<div class='center'>";
        echo Html::hidden('id', ['value' => $ID, 'id' => 'profileId']);
        if (PluginMycustomviewProfileRights::isSuperAdmin()) {
            echo Html::submit(_sx('button', 'Save'), ['name' => 'update', 'id' => 'updateMcv']);
        }
        echo "</div>\n";
        echo "</div>";

        $js = "console.log('test')
        $('#updateMcv').on('click', function(){
            data = [];
            var readValue = $('#readProfileMcv').is(':checked');
            var updateValue = $('#updateProfileMcv').is(':checked');
            var id = $('#profileId').val();
            data.push({'readValue': readValue, 'updateValue': updateValue, 'id': id});
            $.ajax({
               url: '" . $CFG_GLPI['root_doc'] . "/plugins/mycustomview/ajax/updateProfileRights.php',
               type: 'POST',
               data: {data:data},
               success:function(data) {
                  window.location.reload();
               }
            });
         });

         $('#updateProfileMcv').on('click', function() {
            var updateValue = $('#updateProfileMcv').is(':checked');
            if (updateValue) {

                var readValue = $('#readProfileMcv').is(':checked');
                if (!(readValue)){
                    $('#readProfileMcv').prop( 'checked', true );
                }
            }
         });

         $('#readProfileMcv').on('click', function() {
            var readValue = $('#readProfileMcv').is(':checked');
            if (!(readValue)) {

                var updateValue = $('#updateProfileMcv').is(':checked');
                if (updateValue){
                    $('#updateProfileMcv').prop( 'checked', false );
                }
            }
         });
        ";
        echo Html::scriptBlock($js);
    }
}
