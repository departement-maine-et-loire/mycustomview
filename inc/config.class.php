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

class PluginMycustomviewConfig extends CommonDBTM
{


    /**
     * Get configuration infos from database
     * Return max_filters if exists, else return "false"
     * @global type $DB
     * @return boolean
     */
    public static function getConfiguration()
    {
        global $DB;
        $result = $DB->request([
            'FROM' => 'glpi_plugin_mycustomview_config',
            'LIMIT' => 1
        ]
        );
        //$query = "SELECT * FROM glpi_plugin_mycustomview_config";

        if ($result)
        {
            if (count($result) > 0)
            {
               foreach($result as $data){
                    if(isset($data['id'])) {
                        return [$data['max_filters'], $data['id']];
                    }
                }  
            }
            return false;
        }
    }

    public static function getMaxFilters()
    {
        $data = self::getConfiguration();
        $max_filters = $data[0];

        return $max_filters;
        
    }

     /**
     * Set configuration infos in database
     * @global type $DB
     * @param $id
     * @param $max_filters
     */
    public function setConfiguration($max_filters, $id=null)
    {
        global $DB;

        if($id != null){
                if(isset($max_filters)){
                $DB->update(
                    'glpi_plugin_mycustomview_config', [
                        'max_filters' => $max_filters
                    ], [
                        'WHERE' => ['id' => $id]
                        ]
                    );
                    // OLD METHOD
                //$query = "UPDATE glpi_plugin_mycustomview_config SET max_filters = '$max_filters' WHERE id = '1'";
                //$DB->query($query) or die($DB->error());
            }
        } 
        else {
            $DB->insert(
                'glpi_plugin_mycustomview_config', [
                    'max_filters' => $max_filters
                ]
                );
            // OLD METHOD
            //$query = "INSERT INTO glpi_plugin_mycustomview_config (max_filters) VALUES ('$max_filters')";
            //$DB->query($query) or die($DB->error());

        }
    }

    public function showForm($id, $options= [] ){

        global $CFG_GLPI;
        $modify = false;
        $create = false;
        $max_filters = "";
        $configData = self::getConfiguration();
        $id_max_filters = $configData[1];
        $max_filters = $configData[0]; 
   
        if (!Session::haveRight("profile",1)) {
           return false;
        }

        if(isset($_POST['max_filters'])) {
            $change_max_filters = self::setConfiguration($_POST['max_filters'], $id_max_filters);
            $configData = self::getConfiguration();
            $id_max_filters = $configData[1];
            $max_filters = $configData[0]; 

        }

        // si max_filters est défini, on est en train de modif
        if($max_filters != "") {
            $modify = true;
            $createUpdate = __("Enregistrer", "mycustomview");
        }
        else {
            $create = true;
            $createUpdate = __("Ajouter", "mycustomview");
        }
        
        if (!Session::haveRight("profile",READ)) {
            return false;
         }
   
         $canedit = Session::haveRight("profile", CREATE);
         $prof = new Profile();
         if ($id){
            $prof->getFromDB($id);
         }
        
        echo "<div align='center'>"; 
        echo "<form action='./config.form.php' method='post'>\n";
        echo "<table class='tab_cadre_fixe' style='margin: 0; margin-top: 5px;'>\n";
        echo " <tr><th colspan='2'>$createUpdate " .__("le nombre de filtres maximum pour la page \"Ma vue personnalisée\"", "mycustomview") . ".</th></tr>\n";
        echo "<td style='width: 30%'><label for ='max_filters'>" .__("Nombres de filtres", "mycustomview") . " : </label></td>";
        echo "<td style='width: 70%'><input type ='number' min='1' max='30' id='max_filters' value= '$max_filters' name='max_filters' placeholder='Min : 1 / Max : 30' required</td>";
        echo "</table>\n";
        if(Session::haveRight("profile", CREATE)){
            echo "<input type='submit' style='margin-top : 10px' name='$createUpdate' class='submit' ".
        "value='$createUpdate'>";
        }
        else {
            echo "<div class='warning' style='margin-top:10px; width:70%'><i class='fa fa-exclamation-triangle fa'></i>";
            echo __("Vous n'avez pas les droits pour modifier les données de cette page.", "mycustomview");
            echo "</div>";
        }
        Html::closeForm();
        echo "</div>"; 
    }
}