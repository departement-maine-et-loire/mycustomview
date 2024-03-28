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
   die("Sorry. You can't access this file directly");
}

use Glpi\Toolbox\Sanitizer;
use Glpi\Application\View\TemplateRenderer;

/**
 * Search Class
 *
 * Generic class for Search Engine
 **/
class PluginMycustomviewSearch extends Search
{
    /**
     * Modification de la fonction Search::showList() pour personnaliser la vue
     **/
    public static function showListMcv(
        $itemtype,
        $params,
        array $forcedisplay = []
    ) {
        $data = Search::getDatas($itemtype, $params, $forcedisplay);

        switch ($data['display_type']) {
            case self::CSV_OUTPUT:
            case self::PDF_OUTPUT_LANDSCAPE:
            case self::PDF_OUTPUT_PORTRAIT:
            case self::SYLK_OUTPUT:
            case self::NAMES_OUTPUT:
                self::outputData($data);
                break;
            case self::GLOBAL_SEARCH:
            case self::HTML_OUTPUT:
            default:
                self::displayDataMcv($data);
                break;
        }
    }

    /**
     * Modification de la fonction Search::displayData() pour personnaliser la vue
     *
     **/
    public static function displayDataMcv(array $data)
    {
        global $CFG_GLPI;

        if (!isset($data['data']) || !isset($data['data']['totalcount'])) {
            return false;
        }

        $search     = $data['search'];
        $itemtype   = $data['itemtype'];
        $item       = $data['item'];
        $is_deleted = $search['is_deleted'];

        foreach ($search['criteria'] as $key => $criteria) {
            if (isset($criteria['virtual']) && $criteria['virtual']) {
                unset($search['criteria'][$key]);
            }
        }

       // Contruct parameters
        $globallinkto  = Toolbox::append_params([
            'criteria'     => Sanitizer::unsanitize($search['criteria']),
            'metacriteria' => Sanitizer::unsanitize($search['metacriteria'])
        ], '&');

        $parameters = http_build_query([
            'sort'   => $search['sort'],
            'order'  => $search['order']
        ]);

        $parameters .= "&{$globallinkto}";

        if (isset($_GET['_in_modal'])) {
            $parameters .= "&_in_modal=1";
        }

        // For plugin add new parameter if available
        if ($plug = isPluginItemType($data['itemtype'])) {
            $out = Plugin::doOneHook($plug['plugin'], 'addParamFordynamicReport', $data['itemtype']);
            if (is_array($out) && count($out)) {
                $parameters .= Toolbox::append_params($out, '&');
            }
        }

        $prehref = $search['target'] . (strpos($search['target'], "?") !== false ? "&" : "?");
        $href    = $prehref . $parameters;

        Session::initNavigateListItems($data['itemtype'], '', $href);

        if (isset($_SESSION['glpilist_limit_mcv'])) {
            $limit = $_SESSION['glpilist_limit_mcv'];
        } else  {
            $limit = $_SESSION['glpilist_limit'];
        }

        // Affiche le contenu de la vue à l'aide d'un template GLPI
        TemplateRenderer::getInstance()->display('components/search/table.html.twig', [
            'data'                => $data,
            'union_search_type'   => $CFG_GLPI["union_search_type"],
            'rand'                => mt_rand(),
            'no_sort'             => $search['no_sort'] ?? false,
            'order'               => $search['order'] ?? [],
            'sort'                => $search['sort'] ?? [],
            'start'               => $search['start'] ?? 0,
            'limit'               => $limit,
            'count'               => $data['data']['totalcount'] ?? 0,
            'item'                => $item,
            'itemtype'            => $itemtype,
            'href'                => $href,
            'prehref'             => $prehref,
            'posthref'            => $globallinkto,
        ]);
        
        // Affiche les données du footer
        $begin_display = $data['data']['begin'] + 1;
        $end_display   = $data['data']['end'] + 1;
        $total_display = $data['data']['totalcount'];

        if ($total_display == 0) {
            $begin_display = 0;
        }

        echo '<div class="card-footer search-footer">';
        echo '<p style="text-align: center; margin: 0;">';
        echo sprintf(__('Showing %s to %s of %s rows'), $begin_display, $end_display, $total_display);
        echo '</p>';   
        echo '</div>';

        // Add items in item list
        foreach ($data['data']['rows'] as $row) {
            if ($itemtype !== AllAssets::class) {
                Session::addToNavigateListItems($itemtype, $row["id"]);
            } else {
                // In case of a global search, reset and empty navigation list to ensure navigation in
                // item header context is not shown. Indeed, this list does not support navigation through
                // multiple itemtypes, so it should not be displayed in global search context.
                Session::initNavigateListItems($row['TYPE'] ?? $data['itemtype']);
            }
        }

       // Clean previous selection
        $_SESSION['glpimassiveactionselected'] = [];
    }
}