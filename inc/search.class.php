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
     * Display result table for search engine for an type
     *
     * @param class-string<CommonDBTM> $itemtype Item type to manage
     * @param array  $params       Search params passed to
     *                             prepareDatasForSearch function
     * @param array  $forcedisplay Array of columns to display (default empty
     *                             = use display pref and search criteria)
     *
     * @return void
     **/
    public static function showListMcv(
        $itemtype,
        $params,
        array $forcedisplay = []
    ) {
        $data = self::getDatasMcv($itemtype, $params, $forcedisplay);

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
     * Get data based on search parameters
     *
     * @since 0.85
     *
     * @param class-string<CommonDBTM> $itemtype Item type to manage
     * @param array  $params        Search params passed to prepareDatasForSearch function
     * @param array  $forcedisplay  Array of columns to display (default empty = empty use display pref and search criteria)
     *
     * @return array The data
     **/
    public static function getDatasMcv($itemtype, $params, array $forcedisplay = [])
    {

        $data = self::prepareDatasForSearchMcv($itemtype, $params, $forcedisplay);
        self::constructSQL($data);
        self::constructData($data);

        return $data;
    }

    /**
     * Prepare search criteria to be used for a search
     *
     * @since 0.85
     *
     * @param class-string<CommonDBTM> $itemtype Item type
     * @param array  $params        Array of parameters
     *                               may include sort, order, start, list_limit, deleted, criteria, metacriteria
     * @param array  $forcedisplay  Array of columns to display (default empty = empty use display pref and search criterias)
     *
     * @return array prepare to be used for a search (include criteria and others needed information)
     **/
    public static function prepareDatasForSearchMcv($itemtype, array $params, array $forcedisplay = [])
    {
        global $CFG_GLPI;

        if (isset($_SESSION['glpilist_limit_mcv'])) {
            $limit = $_SESSION['glpilist_limit_mcv'];
        } else  {
            $limit = $_SESSION['glpilist_limit'];
        }

       // Default values of parameters
        $p['criteria']            = [];
        $p['metacriteria']        = [];
        $p['sort']                = ['nosort'];
        $p['order']               = ['DESC'];
        $p['start']               = 0;//
        $p['is_deleted']          = 0;
        $p['export_all']          = 0;
        if (class_exists($itemtype)) {
            $p['target']       = $itemtype::getSearchURL();
        } else {
            $p['target']       = Toolbox::getItemTypeSearchURL($itemtype);
        }
        $p['display_type']        = self::HTML_OUTPUT;
        $p['showmassiveactions']  = true;
        $p['dont_flush']          = false;
        $p['show_pager']          = true;
        $p['show_footer']         = false;
        $p['no_sort']             = false;
        $p['list_limit']          = $limit;
        $p['massiveactionparams'] = [];

        foreach ($params as $key => $val) {
            switch ($key) {
                case 'order':
                    if (!is_array($val)) {
                     // Backward compatibility with GLPI < 10.0 links
                        if (in_array($val, ['ASC', 'DESC'])) {
                              $p[$key] = [$val];
                        }
                        break;
                    }
                    $p[$key] = $val;
                    break;
                case 'sort':
                    if (!is_array($val)) {
                        // Backward compatibility with GLPI < 10.0 links
                        $val = (int) $val;
                        if ($val >= 0) {
                            $p[$key] = [$val];
                        }
                        break;
                    }
                    $p[$key] = $val;
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

        if ($itemtype != AllAssets::getType()) {
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
        if (!$CFG_GLPI['allow_search_view'] && !array_key_exists('globalsearch', $p)) {
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
            $displaypref = DisplayPreference::getForTypeUser($itemtype, Session::getLoginUserID());
            if (count($displaypref)) {
                foreach ($displaypref as $val) {
                    array_push($data['toview'], $val);
                }
            }
        } else {
            $data['toview'] = array_merge($data['toview'], $forcedisplay);
        }

        if (count($p['criteria']) > 0) {
           // use a recursive closure to push searchoption when using nested criteria
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

           // call the closure
            $parse_criteria($p['criteria']);
        }

        if (count($p['metacriteria'])) {
            $data['search']['no_search'] = false;
        }

       // Add order item
        $to_add_view = array_diff($p['sort'], $data['toview']);
        array_push($data['toview'], ...$to_add_view);

       // Special case for CommonITILObjects : put ID in front
        if (is_a($itemtype, CommonITILObject::class, true)) {
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
     * Display datas extracted from DB
     *
     * @param array $data Array of search datas prepared to get datas
     *
     * @return void
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