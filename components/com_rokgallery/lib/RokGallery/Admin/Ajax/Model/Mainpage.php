<?php
/**
 * @version   $Id: Mainpage.php 39519 2011-07-05 18:26:39Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokGalleryAdminAjaxModelMainPage extends RokCommon_Ajax_AbstractModel
{
    /**
     * Delete the file and all associated rows (done by foreign keys) and files
     * $params object should be a json like
     * <code>
     * {
     *  "page": 3,
     *  "items_per_page":6
     *  "filters": [{ type: "title", operator: "is not", query: "example"},{ type: "title", operator: "is not", query: "example"}]
     *  "get_remaining": true
     * }
     * </code>
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function getPage($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $html = '';
            $filters = array();
            foreach ($params->filters as $filter)
            {
                $filters[] = RokGallery_Filter_Item::createFromJson($filter);
            }
            $model = new RokGallery_Admin_MainPage();


            $order_by = (isset($params->order->order_by)) ? $params->order->order_by : null;
            $order_direction = (isset($params->order->order_direction)) ? $params->order->order_direction : null;


            $files = $model->getFiles($params->page, $params->items_per_page, $filters, $order_by, $order_direction);
            $pager = $model->getPager($params->page, $params->items_per_page, $filters, $order_by, $order_direction);
            $model->clearPager();
            $total_items_count = $pager->getResultsInPage();


            $current_page = $params->page;

            $next_page = ($current_page == 1) ? 3 : $current_page + 1;
            $next_page = ($current_page == $pager->getLastPage()) ? false : $next_page;
            $remaining_pages = ($next_page) ? $pager->getLastPage() - $current_page : 0;
            $items_per_page = RokGallery_Config::getOption(RokGallery_Config::OPTION_ADMIN_ITEMS_PER_PAGE, 6);
            $passed_items_per_page = $items_per_page;
            $items_per_page = ($current_page == 1) ? $items_per_page * 2 : $items_per_page;
            $items_per_row = RokGallery_Config::getOption(RokGallery_Config::OPTION_ADMIN_ITEMS_PER_ROW, 3);

            $that->files =& $files;
            $that->items_per_page = $items_per_page;
            $that->items_per_row = $items_per_row;
            ob_start();
            $that->row_entry_number = 0;
            $that->item_number = 1;
            $that->items_to_be_rendered = $pager->getResultsInPage();
            foreach ($that->files as $that->file):
                if (!isset($params->composite) || !isset($params->composite->context) || !isset($params->composite->layout)) {
                    echo RokCommon_Composite::get('com_rokgallery.default')->load('default_row.php', array('that' => $that));
                } else {
                    echo RokCommon_Composite::get($params->composite->context)->load($params->composite->layout . '.php', array('that' => $that));
                }
                $that->row_entry_number++;
                $that->item_number++;
            endforeach;
            $html .= ob_get_clean();


            if (isset($params->get_remaining) && $params->get_remaining) {
                for ($params->page++; $params->page <= $pager->getLastPage(); $params->page++)
                {
                    $more_files = $model->getFiles($params->page, $params->items_per_page, $filters, $order_by, $order_direction);
                    $pager = $model->getPager($params->page, $params->items_per_page, $filters, $order_by, $order_direction);
                    $model->clearPager();
                    $total_items_count = $total_items_count + $pager->getResultsInPage();

                    $current_page = $params->page;

                    $next_page = ($current_page == 1) ? 3 : $current_page + 1;
                    $next_page = ($current_page == $pager->getLastPage()) ? false : $next_page;
                    $remaining_pages = ($next_page) ? $pager->getLastPage() - $current_page : 0;
                    $items_per_page = RokGallery_Config::getOption(RokGallery_Config::OPTION_ADMIN_ITEMS_PER_PAGE, 6);
                    $passed_items_per_page = $items_per_page;
                    $items_per_page = ($current_page == 1) ? $items_per_page * 2 : $items_per_page;
                    $items_per_row = RokGallery_Config::getOption(RokGallery_Config::OPTION_ADMIN_ITEMS_PER_ROW, 3);

                    $that->files =& $more_files;
                    $that->items_per_page = $items_per_page;
                    $that->items_per_row = $items_per_row;
                    ob_start();
                    $that->row_entry_number = 0;
                    $that->item_number = 1;
                    $that->items_to_be_rendered = $pager->getResultsInPage();
                    foreach ($that->files as $that->file):
                        if (!isset($params->composite) || !isset($params->composite->context) || !isset($params->composite->layout)) {
                            echo RokCommon_Composite::get('com_rokgallery.default')->load('default_row.php', array('that' => $that));
                        } else {
                            echo RokCommon_Composite::get($params->composite->context)->load($params->composite->layout . '.php', array('that' => $that));
                        }
                        $that->row_entry_number++;
                        $that->item_number++;
                    endforeach;
                    $html .= ob_get_clean();
                }
            }


            $result->setPayload(array(
                                     'next_page' => $next_page,
                                     'last_page' => $pager->getLastPage(),
                                     'items_per_page' => $passed_items_per_page,
                                     'items_returned' => $total_items_count,
                                     'more_pages' => ($next_page == false) ? false : true,
                                     'remaining_pages' => $remaining_pages,
                                     'total_items_in_filter' => $pager->getNumResults(),
                                     'total_items_shown' => $pager->getLastIndice(),
                                     'total_items' => RokGallery_Model_FileTable::getTotalFileCount(),
                                     'html' => $html)
            );
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }
}
