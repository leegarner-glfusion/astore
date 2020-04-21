<?php
/**
 * Common elements for Amazon Items.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2017-2020 Lee Garner <lee@leegarner.com>
 * @package     astore
 * @version     v0.2.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Astore;


/**
 * Class for Amazon Items.
 * @package astore
 */
class Item
{
    /** Cache tag applied to all cached items from this plugin.
     * @var string */
    protected static $tag = 'astore';

    /** Data holder for search results.
     * @var object */
    protected $data = NULL;

    /** Item record ID.
     * @var integer */
    private $id = 0;

    /** Item short name/description.
     * @var string */
    private $title = '';

    /** Item ASIN identifier.
     * @var string */
    private $asin;

    /** URL to the Amazon ad.
     * @var string */
    private $url = '';

    /** Is the item allowed to be displayed?
     * @var boolean */
    private $enabled = 1;

    /** Is the item featured?
     * @var boolean */
    private $featured = 0;

    /** ASIN of the featured item, if any.
     * @var string */
    private $featured_asin = '';


    /**
     * Constructor. Sets up internal variables.
     *
     * @param   string|array    $id     Item id or record
     */
    public function __construct($id)
    {
        if (is_array($id)) {
            $this->setVars($id);
        } elseif (is_numeric($id)) {
            $this->id = (int)$id;
            if (!$this->Read()) {
                $this->id = 0;
            }
        }
    }


    private function Read()
    {
        global $_TABLES;

        $retval = false;
        $sql = "SELECT * FROM {$_TABLES['astore_catalog']}
            WHERE id = {$this->id}
            LIMIT 1";
        $res = DB_query($sql);
        if ($res) {
            $A = DB_fetchArray($res, false);
            if (is_array($A)) {
                $this->setVars($A);
                $retval = true;
            }
        }
        return $retval;
    }


    private function setVars($A)
    {
        $this->id = (int)$A['id'];
        $this->asin = trim($A['asin']);
        $this->title = trim($A['title']);
        $this->enabled = isset($A['enabled']) && $A['enabled'] ? 1 : 0;
        $this->url = trim($A['url']);
    }


    public function getPage($page=1, $orderby='id')
    {
        global $_TABLES;

        $retval = array();
        $perpage = 25;      // todo - config item

        switch ($orderby) {
        case 'id':
            $ord = "`id` ASC";
            break;
        case 'ts':
            $ord = '`ts` DESC';
            break;
        case 'rand':
            $ord = 'RAND()';
            break;
        }

        $start = ($page - 1) * $perpage;
        $sql = "SELECT * FROM {$_TABLES['astore_catalog']}
            WHERE enabled=1
            ORDER BY $ord
            LIMIT $start, $perpage";
        $res = DB_query($sql);
        if ($res) {
            while ($A = DB_fetchArray($res, false)) {
                $retval[] = new self($A);
            }
        }
        return $retval;
    }


    public static function getInstance($id)
    {
        return new self($id);
    }


    public function getNativeUrl()
    {
    }
    public function getUrl()
    {
        global $_CONF_ASTORE;

        $region = 'US';
        $language = 'en_US';
        /*if (
            (
                $_CONF_ASTORE['notag_header'] != '' &&
                isset($_SERVER['HTTP_' . strtoupper($_CONF_ASTORE['notag_header'])])
            ) ||
            ($_CONF_ASTORE['notag_admins'] && plugin_ismoderator_astore())
        ) {
            $tag = '';
            $tag = '&tracking_id=' . 'gadgetsfixit-20';
        } else {*/
            $tag = '&tracking_id=' . 'gadgetsfixit-20';
        //}

        $base_url = '<iframe style="width:120px;height:240px;" marginwidth="0" ' .
            'marginheight="0" scrolling="no" frameborder="0" ' .
            'src="//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=%s&source=ss&ref=as_ss_li_til&ad_type=product_link%s&language=%s&marketplace=amazon&region=%s&placement=%s&asins=%s&show_border=true&link_opens_in_new_window=true"></iframe>';
        $url = sprintf($base_url, $region, $tag, $language, $region, $this->asin, $this->asin);
        return $url;
    }


    /**
     * Return the ASIN, to allow public access to it.
     *
     * @return  string  Item ID
     */
    public function getASIN()
    {
        return $this->asin;
    }


    /**
     * Add an item to the catalog if not already present.
     *
     * @param   string  $asin   Item number
     * @param   string  $title  Item title to store in catalog
     * @return  boolean         True on success, False on DB error
     */
    public function Save($A=NULL)
    {
        global $_TABLES;

        if (is_array($A)) {
            $this->setVars($A);
        }
        $sql = "INSERT INTO {$_TABLES['astore_catalog']} SET
            id = {$this->id},
            asin = '" . DB_escapeString($this->asin) . "',
            title = '" . DB_escapeString($this->title) . "',
            enabled = '{$this->isEnabled()}'
            ON DUPLICATE KEY UPDATE
            asin = '" . DB_escapeString($this->asin) . "',
            title = '" . DB_escapeString($this->title) . "',
            enabled = '{$this->isEnabled()}'";
        DB_query($sql);
        return DB_error() ? false : true;
    }


    public function isEnabled()
    {
        return $this->enabled ? 1 : 0;
    }


   /**
     * Log a debug message if aws_debug is enabled.
     *
     * @param   string  $text   Message to be logged
     * @param   boolean $force  True to log message regardless of debug setting
     */
    protected static function _debug($text, $force = false)
    {
        global $_CONF_ASTORE;

        if ($force || (isset($_CONF_ASTORE['debug_aws']) && $_CONF_ASTORE['debug_aws'])) {
            COM_errorLog('Astore:: ' . $text);
        }
    }


    /**
     * Decrypt the AWS secret key from the configuration.
     *
     * @return  string      Decrypted key
     */
    private static function _secretKey()
    {
        global $_CONF_ASTORE, $_VARS;
        static $secretkey = NULL;

        if ($secretkey === NULL) {
            if (isset($_VARS['guid']) && version_compare(GVERSION, '2.0.0', '<')) {
                $secretkey = COM_decrypt($_CONF_ASTORE['aws_secret_key'], $_VARS['guid']);
            } else {
                $secretkey = $_CONF_ASTORE['aws_secret_key'];
            }
        }
        return $secretkey;
    }


    /**
     * Determine if an item is available at all from Amazon.
     * Returns False if not available from Amazon nor from other sellers.
     *
     * @return  boolean     True if available, False if not
     */
    public function isAvailable()
    {
        $retval = true;
        if (isset($this->data->Offers->TotalOffers)) {
            $x = (int)$this->data->Offers->TotalOffers;
            if ($x == 0) {
                if (!isset($this->data->OfferSummary->LowestNewPrice)) {
                    $retval = false;
                }
            }
        }
        return $retval;
    }


    /**
     * Determine if this item is being sold by Amazon.
     *
     * @return  boolean True if available, False if sold only by others
     */
    private function _haveAmazonOffers()
    {
        if (!isset($this->data->Offers->TotalOffers)) {
            return false;
        } else {
            $x = $this->data->Offers->TotalOffers;
            if (!$x)
                return false;
        }
        return true;
    }


    /**
     * Display the products in a grid.
     *
     * @param   array   $items  Array of item objects
     * @return  string      HTML for the product page
     */
    public static function showProducts($items)
    {
        global $_CONF_ASTORE;

        if (!is_array($items)) {
            $items = array($items);
        }
        $T = new \Template(ASTORE_PI_PATH . '/templates');
        $T->set_file(array(
            'products' => 'productbox.thtml',
        ) );
        $T->set_block('products', 'productbox', 'pb');
        foreach ($items as $item) {
            if ($item->isError()) continue;
            if (!$item->isAvailable()) {
                $item->Disable();
                continue;
            }
            $T->set_var(array(
                'item_url'  => $item->DetailPageURL(),
                'lowestprice'   => $item->LowestPrice(),
                'listprice' => $item->ListPrice(),
                'title'     => COM_truncate($item->Title(),
                        $_CONF_ASTORE['max_blk_desc'], '...'),
                'img_url'   => $item->MediumImage()->URL,
                'img_width' => $item->MediumImage()->Width,
                'img_height' => $item->MediumImage()->Height,
                'formattedprice' => $item->LowestPrice(),
                'displayprice' => $item->DisplayPrice(),
                'iconset'   => $_CONF_ASTORE['_iconset'],
                'long_description' => '',
                'offers_url' => $item->OffersURL(),
                'available' => $item->isAvailable(),
                'is_prime' => $item->isPrime() ? true : false,
            ) );
            $T->parse('pb', 'productbox', true);
        }
        $T->parse('output', 'products');
        return $T->finish($T->get_var('output'));
    }


    /**
     * Retrieve a single item.
     *
     * @param   string  $asin   Amazon item ID
     * @return  object          Data object
     */
    public static function Retrieve($asin)
    {
        global $_CONF_ASTORE;

        // Return from cache if found and not expired
        $data = Cache::get($asin);
        if (empty($data)) {
            $data = self::_getAmazon(array($asin));
            if (!empty($data) && $_CONF_ASTORE['auto_add_catalog']) {
                if (isset($data->ItemAttributes->Title)) {
                    $title = $data->ItemAttributes->Title;
                } else {
                    $title = '';
                }
                self::AddToCatalog($asin, $title);
            }
            if (isset($data[$asin])) {
                return $data[$asin];
            } else {
                return NULL;
            }
        } else {
            return $data;
        }
    }


    /**
     * Delete an item from the catalog and cache.
     *
     * @param   string  $asin   Item number
     */
    public static function Delete($id)
    {
        global $_TABLES;

        $id = (int)$id;
        DB_delete($_TABLES['astore_catalog'], 'id', $id);
    }


    /**
     * Get the number of items in the catalog.
     * Used for pagination.
     *
     * @param   boolean $enabled    True to count only enabled items
     * @return  integer     Count of items in the catalog table
     */
    public static function Count($enabled = true)
    {
        global $_TABLES;
        static $counts = array();
        if ($enabled) {
            $fld = 'enabled';
            $value = 1;
            $key = 1;
        } else {
            $fld = '';
            $value = '';
            $key = 0;
        }
        if (!isset($counts[$key])) {
            $counts[$key] = (int)DB_count($_TABLES['astore_catalog'], $fld, $value);
        }
        return $counts[$key];
    }


    /**
     * Get the number of pages.
     *
     * @return  integer     Number of pages
     */
    public function Pages()
    {
        global $_CONF_ASEARCH;

        $count = self::Count();

        if (!isset($_CONF_ASTORE['perpage']) ||
            $_CONF_ASTORE['perpage'] < 1) {
            $_CONF_ASTORE['perpage'] = 10;
        }
        return ceil($count / $_CONF_ASTORE['perpage']);
    }



    /**
     * Remove any associate-related tags from the product URL for admins.
     * This is to avoid artifically inflating the click count at Amazon
     * during testing by admins.
     * If the configured header is present, or an admin is logged in and
     * admins should not see associate links, then strip the associate infl.
     *
     * @param   string  $url    Product URL
     * @return  string          URL without associate tags
     */
    private static function stripAWStag($url)
    {
        global $_CONF_ASTORE;

        if (($_CONF_ASTORE['notag_header'] != '' &&
            isset($_SERVER['HTTP_' . strtoupper($_CONF_ASTORE['notag_header'])])) ||
            $_CONF_ASTORE['notag_admins'] && plugin_ismoderator_astore()) {
            return preg_replace('/\?.*/', '', $url);
        } else {
            return $url;
        }
    }


    /**
     * Disable a catalog item when it is unavailable.
     *
     * @uses    self::toggle()
     */
    private function Disable()
    {
        self::toggle(1, 'enabled', $this->asin);
    }


    /**
     * Toggle a field in the catalog.
     *
     * @param   integer $oldval     Original value to be changed
     * @param   string  $field      Field name
     * @param   string  $id         Item ID
     * @return  integer     New value, or old value in case of error
     */
    public static function toggle($oldval, $field, $id)
    {
        global $_TABLES;

        $oldval = $oldval == 0 ? 0 : 1;
        $newval = $oldval == 0 ? 1 : 0;
        $field = DB_escapeString($field);
        $asin = DB_escapeString($asin);
        $sql = "UPDATE {$_TABLES['astore_catalog']}
            SET $field = $newval
            WHERE id = $id";
        DB_query($sql);
        if (DB_error()) {
            return $oldval;
        } else {
            return $newval;
        }
    }


    /**
     * Show the admin list.
     *
     * @param   string  $import_fld     ID of item to import
     * @return  string  HTML for item list
     */
    function adminList($import_fld = '')
    {
        global $LANG_ADMIN, $LANG_ASTORE, $LANG01,
            $_TABLES, $_CONF, $_CONF_ASTORE;

        USES_lib_admin();

        $retval = '';
        $form_arr = array();

        $header_arr = array(
            array(
                'text' => 'ID',
                'field' => 'id',
                'sort' => true,
            ),
            array(
                'text' => $LANG01[4],
                'field' => 'edit',
                'sort' => false,
                'align' => 'center',
            ),
            array(
                'text' => $LANG_ASTORE['title'],
                'field' => 'title',
                'sort' => false,
            ),
            array(
                'text' => $LANG_ADMIN['enabled'],
                'field' => 'enabled',
                'sort' => 'false',
                'align' => 'center',
            ),
            array(
                'text' => $LANG_ASTORE['last_update'],
                'field' => 'ts',
                'sort' => 'true',
                'nowrap' => true,
            ),
            array(
                'text' => $LANG_ADMIN['delete'],
                'field' => 'delete',
                'sort' => false,
                'align' => 'center',
            ),
        );

        $text_arr = array(
            'has_extras' => false,
            'form_url' => ASTORE_ADMIN_URL . '/index.php',
        );

        $options = array(
            'chkdelete' => 'true',
            'chkfield' => 'id',
        );
        $defsort_arr = array(
            'field' => 'id',
            'direction' => 'asc',
        );
        $query_arr = array(
            'table' => 'astore_catalog',
            'sql' => "SELECT * FROM {$_TABLES['astore_catalog']}",
        );

        $T = new \Template(ASTORE_PI_PATH . '/templates');
        $T->set_file('form', 'newitem.thtml');
        $T->set_var(array(
            'import_fld' => $import_fld,
        ) );
        $T->parse('output', 'form');
        $retval .= $T->finish($T->get_var('output'));
        $retval .= ADMIN_list(
            'astore_itemadminlist',
            array(__CLASS__, 'getAdminField'),
            $header_arr,
            $text_arr, $query_arr, $defsort_arr, '', '', $options, $form_arr
        );
        return $retval;
    }


    public function Edit()
    {
        $T = new \Template(ASTORE_PI_PATH . '/templates');
        $T->set_file('form', 'edit.thtml');
        $T->set_var(array(
            'id' => $this->id,
            'asin' => $this->asin,
            'title' => $this->title,
            'ena_chk' => $this->isEnabled() ? 'checked="checked"' : '',
            'url' => $this->url,
        ) );
        $T->parse('output', 'form');
        return $T->finish($T->get_var('output'));
    }


    /**
     * Get the correct display for a single field in the astore admin list.
     *
     * @param   string  $fieldname  Field variable name
     * @param   string  $fieldvalue Value of the current field
     * @param   array   $A          Array of all field names and values
     * @param   array   $icon_arr   Array of system icons
     * @return  string              HTML for field display within the list cell
     */
    public static function getAdminField($fieldname, $fieldvalue, $A, $icon_arr)
    {
        global $_CONF, $LANG_ACCESS, $_CONF_ASTORE;

        $retval = '';

        switch($fieldname) {
        case 'edit':
            $retval = COM_createLink(
                '<i class="uk-icon uk-icon-edit"></i>',
                ASTORE_ADMIN_URL . '/index.php?edit=' . $A['id']
            );
            break;
        case 'delete':
            $retval = COM_createLink(
                '<i class="uk-icon uk-icon-remove uk-text-danger"></i>',
                ASTORE_ADMIN_URL . "/index.php?delitem={$A['asin']}",
                array(
                     'onclick' => "return confirm('Do you really want to delete this item?');",
                ) );
            break;

        case 'title':
            if (empty($fieldvalue)) {
                $retval = '<i class="uk-icon uk-icon-exclamation-triangle ast-icon-danger"></i>&nbsp;<span class="ast-icon-danger">Invalid Item</span>';
            } else {
                $retval = $fieldvalue;
            }
            break;

        case 'enabled':
            $chk = $fieldvalue == 1 ? 'checked="checked"' : '';
            $retval = '<input type="checkbox" data-uk-tooltip class="" value="1" ' . $chk .
                "onclick='ASTORE_toggle(this,\"{$A['asin']}\",\"{$fieldname}\");' />" . LB;
            break;

        default:
            $retval = $fieldvalue;
            break;
        }
        return $retval;
    }

}

?>
