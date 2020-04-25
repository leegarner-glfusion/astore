<?php
/**
 * Display the detail page for a single item.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2020 Lee Garner <lee@leegarner.com>
 * @package     astore
 * @version     v0.2.0
 * @since       v0.2.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

require_once '../lib-common.php';

COM_setArgNames(array('asin'));
$asin = COM_getArgument('asin');
$content = '';

if (empty($asin)) {
    COM_404();
}

$Item = Astore\Item::getInstance($asin);
//var_dump($Item);die;
if (!$Item->isValid()) {
    COM_404();
}
$content = $Item->detailPage();
echo COM_siteHeader();
echo $content;
echo COM_siteFooter();

?>