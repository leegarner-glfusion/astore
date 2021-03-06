<?php
/**
 * Default English Language file for the Astore plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2017 Lee Garner <lee@leegarner.com>
 * @package     astore
 * @version     v0.1.1
 * @license     http://opensource.org/licenses/gpl-2.0.php 
 *              GNU Public License v2 or later
 * @filesource
 */

/**
 * The plugin's lang array.
 * @global array $LANG_ASTORE
 */
$LANG_ASTORE = array(
'admin_title' => 'Amazon Store Administration',
'shop_now'  => 'Shop Now',
'view_details' => 'View Details',
'title'     => 'Description',
'items'     => 'Items',
'category'  => 'Category',
'categories' => 'Categories',
'admin' => 'Admin',
'name' => 'Name',
'order' => 'Order',
'mnu_newcat' => 'New Category',
'version'   => 'Version',
'new_asin'  => 'New Item No.',
'add_item'  => 'Add Item(s)',
'item_not_found' => 'The requested item could not be found, or an error was encountered. Please check the item number and try again.',
'import'    => 'Import',
'export'    => 'Export',
'clearcache' => 'Clear Cache',
'enter_asins' => 'ASINs, separated by commas',
'instr_import' => 'Enter a comma-separated string of ASIN numbers to import.<br />E.g. &quot;ABCDEFG,123456,abcdefg&quot;',
'instr_export' => 'Copy and save this string of item numbers, and use it to re-import the items later.',
'features'  => 'Features',
'unavailable' => 'Unavailable',
'more_offers' => 'See Offers',
'search_query' => 'Enter Search String',
'search' => 'Search',
'more_results' => 'More Results',
'back_to_store' => 'Back to Store',
'last_update' => 'Last Update',
'err_adm_import_size' => 'Your import string contained more than 10 items. The remaining items have been placed in the import field for you to submit again',
'enabled' => 'Enabled',
'disabled' => 'Disabled',
'msg_item_updated'  => 'Item has been %1$s',
'msg_item_nochange' => 'Item has not been changed',
'q_del_item' => 'Are you sure you want to delete this item?',
'first' => 'First',
'last' => 'Last',
'delete' => 'Delete',
'hlp_unchk_all' => 'Uncheck all',
'current_as_of' => 'Data current as of:',
'as_of' => 'As of',
'confirm_del' => 'Are you sure you want to delete the selected item(s)?',
'edit' => 'Edit',
'del_item' => 'Delete Item',
'editorial' => 'Editorial Review',
);

$LANG_ASTORE_AUTOTAG = array(
    'desc_astore' => 'Create a link to an item in the store or at Amazon.',
    'desc_astore_link' => 'Create a link to an item in the store or at Amazon.',
);

// Localization of the Admin Configuration UI
$LANG_configsections['astore'] = array(
    'label' => 'Astore',
    'title' => 'Astore Configuration',
);

$LANG_confignames['astore'] = array(
    'is_open' => 'Store is open?',
    'use_api' => 'Use Product API?',
    'aws_access_key' => 'AWS Access Key',
    'aws_secret_key' => 'AWS Secret key',
    'aws_assoc_id' => 'Associate Tag',
    'aws_region' => 'Amazon Region',
    'aws_cache_min' => 'Minutes to cache product info',
    'debug_aws' => 'Debug AWS requests?',
    'perpage' => 'Items to show per page',
    'auto_add_catalog' => 'Automatically add requested items to catalog?',
    'store_title' => 'Store Title',
    'max_feat_desc' => 'Max Featured Item Description (chars)',
    'max_blk_desc' => 'Max Item Description in blocks (chars)',
    'sort' => 'Sort Order',
    'notag_admins' => 'Block Associate ID if Admin',
    'notag_header' => 'Block Associate ID for Header',
    'cb_enable' => 'Enable Centerblock?',
    'grp_search' => 'Group that can search',
    'def_catid' => 'Default Category',
    'link_to' => 'Item links direct to ...',
    'disclaimer' => 'Disclaimer text',
    'full_disclaimer' => 'Full Disclaimer text',
);

$LANG_configsubgroups['astore'] = array(
    'sg_main' => 'Main Settings',
);

$LANG_fs['astore'] = array(
    'fs_main' => 'Main Astore Settings',
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['astore'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array(
        'Australia' => 'au',
        'Brazil' => 'br',
        'Canada' => 'ca',
        'France' => 'fr',
        'Germany' => 'de',
        'India' => 'in',
        'Italy' => 'it',
        'Japan' => 'jp',
        'Mexico' => 'mx',
        'Netherlands' => 'nl',
        'Singapore' => 'sg',
        'Spain' => 'es',
        'Turkey' => 'tr',
        'United Arab Emirates' => 'ae',
        'United Kingdom' => 'uk',
        'United States' => 'us',
    ),
    2 => array('Yes' => 1, 'No' => 0),
    3 => array('ASIN' => 'none', 'Date ASC' => 'fifo', 'Date DESC' => 'lifo'),
    4 => array(
        'Direct to Amazon' => 'amazon',
        'Internal Detail Page' => 'detail',
    ),
);
