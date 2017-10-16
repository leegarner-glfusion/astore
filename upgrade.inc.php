<?php
/**
*   Upgrade routines for the Astore plugin.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2017 Lee Garner <lee@leegarner.com>
*   @package    astore
*   @version    0.0.2
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/

require_once __DIR__ . '/install_defaults.php';

/**
*   Perform the upgrade starting at the current version.
*
*   @param  string  $current_ver    Current installed version to be upgraded
*   @return integer                 Error code, 0 for success
*/
function astore_do_upgrade()
{
    global $_TABLES, $_CONF_ASTORE, $_PLUGIN_INFO;

    $pi_name = $_CONF_ASTORE['pi_name'];

    if (isset($_PLUGIN_INFO[$_CONF_ASTORE['pi_name']])) {
        $code_ver = plugin_chkVersion_astore();
        if (is_array($_PLUGIN_INFO[$_CONF_ASTORE['pi_name']])) {
            // glFusion 1.6.6+
            $current_ver = $_PLUGIN_INFO[$_CONF_ASTORE['pi_name']]['pi_version'];
        } else {
            $current_ver = $_PLUGIN_INFO[$_CONF_ASTORE['pi_name']];
        }
        if (COM_checkVersion($current_ver, $code_ver)) {
            // Already updated to the code version, nothing to do
            return true;
        }
    } else {
        // Error determining the installed version
        return false;
    }
    $installed_ver = plugin_chkVersion_astore();
    $conf = config::get_instance();

    // Final extra check to catch code-only patch versions
    if (!COM_checkVersion($current_ver, $installed_ver)) {
        if (!astore_do_update_version($installed_ver)) return false;
    }
    return true;
}


/**
*   Update the plugin version.
*   Done at each update step to keep the version up to date
*
*   @param  string  $version    Version to set
*   @return boolean     True on success, False on failure
*/
function astore_do_update_version($version)
{
    global $_TABLES, $_CONF_ASTORE;

    // now update the current version number.
    DB_query("UPDATE {$_TABLES['plugins']} SET
            pi_version = '{$version}',
            pi_gl_version = '{$_CONF_ASTORE['gl_version']}',
            pi_homepage = '{$_CONF_ASTORE['pi_url']}'
        WHERE pi_name = 'astore'");

    if (DB_error()) {
        COM_errorLog("Error updating the astore Plugin version to $version",1);
        return false;
    } else {
        COM_errorLog("Succesfully updated the astore Plugin version to $version!",1);
        return true;
    }
}


/**
*   Actually perform any sql updates
*   @param string $version  Version being upgraded TO
*   @param array  $sql      Array of SQL statement(s) to execute
*/
function astore_do_upgrade_sql($version)
{
    global $_TABLES, $_CONF_ASTORE, $ASTORE_UPGRADE, $_DB_dbms;

    require_once ASTORE_PI_PATH . "/sql/{$_DB_dbms}_install.php";

    // If no sql statements passed in, return success
    if (!is_array($ASTORE_UPGRADE[$version]))
        return true;

    // Execute SQL now to perform the upgrade
    COM_errorLOG("--Updating Astore to version $version");
    foreach($ASTORE_UPGRADE[$version] as $sql) {
        COM_errorLOG("Astore Plugin $version update: Executing SQL => $sql");
        DB_query($sql, '1');
        if (DB_error()) {
            COM_errorLog("SQL Error during Astore Plugin update",1);
            return false;
            break;
        }
    }
    return true;
}


/**
*   Upgrade to version 0.1.0.
*   Adds configuration item for centerblock replacing home page.
*/
function astore_upgrade_0_1_0()
{
    global $_CONF_ASTORE, $ASTORE_DEFAULT;

    // Add new configuration items
    $c = config::get_instance();
    if ($c->group_exists($_CONF_ASTORE['pi_name'])) {
        $c->add('cb_replhome', $_ASTORE_DEFAULT['cb_replhome'],
                'select',0, 1, 3, 120, true, $_CONF_ASTORE['pi_name']);
        $c->add('block_limit', $_ASTORE_DEFAULT['block_limit'],
                'text',0, 0, 3, 130, true, $me);
    }

    if (!astore_do_upgrade_sql('0.1.0')) return false;
    return astore_do_update_version('0.1.0');
}


/**
*   Upgrade to version 0.1.7.
*   Adds configuration item for centerblock replacing home page.
*/
function astore_upgrade_0_1_7()
{
    global $_CONF_ASTORE, $ASTORE_DEFAULT, $UPGRADE;

    // Add new configuration items
    $c = config::get_instance();
    if ($c->group_exists($_CONF_ASTORE['pi_name'])) {
        $c->add('uagent_dontshow', $_ASTORE_DEFAULT['uagent_dontshow'],
                '%text', 0, 1, 0, 25, true, $_CONF_ASTORE['pi_name']);
    }

    if (!astore_do_upgrade_sql('0.1.7')) return false;
    return astore_do_update_version('0.1.7');
}

?>