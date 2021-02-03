<?php


if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}
class PluginMycustomviewProfileRights extends CommonDBTM
{

   static function hasChangeProfile()
   {
      if ((isset($_SESSION['glpi_plugin_mycustomview_profile']['id']))) {
         if ($_SESSION['glpiactiveprofile']['id'] != $_SESSION['glpi_plugin_mycustomview_profile']['id']) {
            $_SESSION['glpi_plugin_mycustomview_profile']['id'] = $_SESSION['glpiactiveprofile']['id'];
            return true;
         } else {
            return false;
         }
      } else {
         $_SESSION['glpi_plugin_mycustomview_profile']['id'] = $_SESSION['glpiactiveprofile']['id'];
      }
   }

   static function setSessionProfileId()
   {
      if (!isset($_SESSION['glpi_plugin_mycustomview_profile'])) {
         $_SESSION['glpi_plugin_mycustomview_profile']['id'] = $_SESSION['glpiactiveprofile']['id'];
      }
   }

   static function getSessionRights()
   {
      global $DB;

      $result = $DB->request([
         'SELECT' =>
         "right",
         'FROM' => 'glpi_plugin_mycustomview_profile_rights',
         'WHERE' => [
            "profile"    => $_SESSION['glpiactiveprofile']['id']
         ],
      ]);
      foreach ($result as $data) {
         $right = $data['right'];
         return $right;
      }
   }

   static function getProfileRight($ID)
   {
      global $DB;

      $result = $DB->request([
         'SELECT' =>
         "right",
         'FROM' => 'glpi_plugin_mycustomview_profile_rights',
         'WHERE' => [
            "profile"    => $ID
         ],
      ]);
      foreach ($result as $data) {
         $right = $data['right'];
         return $right;
      }
   }

   static function setSessionProfileRights()
   {
      $_SESSION['glpi_plugin_mycustomview_profile']['right'] = self::getSessionRights();
   }

   static function changeProfile()
   {
      self::hasChangeProfile();
      self::setSessionProfileRights();
   }

   static function canUpdate()
   {

      if (isset($_SESSION["glpi_plugin_mycustomview_profile"])) {
         return ($_SESSION['glpi_plugin_mycustomview_profile']['right'] == 'w'
            || $_SESSION["glpi_plugin_mycustomview_profile"]['right'] == 'su');
      }
      return false;
   }

   static function canView()
   {

      if (isset($_SESSION["glpi_plugin_mycustomview_profile"])) {
         return ($_SESSION["glpi_plugin_mycustomview_profile"]['right'] == 'w'
            || $_SESSION["glpi_plugin_mycustomview_profile"]['right'] == 'r'
            || $_SESSION["glpi_plugin_mycustomview_profile"]['right'] == 'su');
      }
      return false;
   }

   static function canProfileUpdate($ID)
   {
      $right = self::getProfileRight($ID);
      if (isset($right)) {
         return ($right == 'w'
            || $right == 'su');
      }
      return false;
   }

   static function canProfileView($ID)
   {
      $right = self::getProfileRight($ID);
      if (isset($right)) {
         return ($right == 'w'
            || $right == 'r'
            || $right == 'su');
      }
      return false;
   }

   static function isSuperAdmin()
   {
      if (isset($_SESSION["glpi_plugin_mycustomview_profile"])) {
         return $_SESSION["glpi_plugin_mycustomview_profile"]['right'] == 'su';
      }
      return false;
   }

   static function createAdminAccess($ID)
   {
      global $DB;

      $myProfile = new self();
      // si le profil n'existe pas déjà dans la table profile de mon plugin
      if (!$myProfile->getFromDB($ID)) {
         // ajouter un champ dans la table comprenant l'ID du profil de la personne connecté et le droit d'écriture
         $DB->insert(
            'glpi_plugin_mycustomview_profile_rights',
            [
               'profile' => $ID,
               'right' => 'su'
            ]
         );
      }
   }

   static function updateProfile($data)
   {
      global $DB;
      foreach ($data as $value) {
         $read = $value['readValue'];
         $update = $value['updateValue'];
         $id = $value['id'];
         $right = 'no';
         if ($read == 'true') {
            $right = 'r';
         }
         if ($update == 'true') {
            $right = 'w';
         }

         if ($id == 4) {
            return false;
         }
         var_dump('Read : ' . $read);
         var_dump('update : ' . $update);
         var_dump('right : ' . $right);

         $DB->updateOrInsert(
            'glpi_plugin_mycustomview_profile_rights',
            [
               'profile'      => $id,
               'right'  => $right
            ],
            [
               'profile' => $id
            ]
         );

         return true;
      }
   }
}
