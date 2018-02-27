<?php
/**
 * ILIAS REST Plugin for the ILIAS LMS
 *
 * Authors: D.Schaefer, T.Hufschmidt <(schaefer|hufschmidt)@hrz.uni-marburg.de>
 * Since 2014
 */
 
 
// Include core UIHook plugin slot class
use RESTController\extensions\ILIASApp\V2\HashCacheEntry;

require_once 'Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php';
require_once 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/services/FileHashing/entity/HashCacheEntry.php';


/**
 * REST Plugin
 *
 * @author Dirk Schäfer <schaefer@hrz.uni-marburg.de>
 * @version $Id$
 *
 */
class ilRESTPlugin extends ilUserInterfaceHookPlugin
{
    /**
     * Returns plugin name (CASE-SENSITIVE) that will be displayed
     * inside ILIAS and also be used to find all plugin classes.
     *
     * @return (String) Plugin name
     */
    function getPluginName() {
        return "REST";
    }


	/**
	 * Custom uninstall routine which drops all database tables of the plugin.
	 * This method will not emit errors if the tables were not found, to
	 * enable the uninstallation of damaged or inconsistent plugin setups.
	 *
	 * @return bool This uninstallation routine will always return true.
	 */
	protected function beforeUninstall() {
    	global $DIC;

    	$db = $DIC->database();
    	$db->dropTable('ui_uihk_rest_config', false);
    	$db->dropTable('ui_uihk_rest_client', false);
    	$db->dropTable('ui_uihk_rest_perm', false);
    	$db->dropTable('ui_uihk_rest_refresh', false);
    	$db->dropTable('ui_uihk_rest_authcode', false);
    	$db->dropTable('ui_uihk_rest_access', false);
    	$db->dropTable('ui_uihk_rest_token', false);
    	$db->dropTable(HashCacheEntry::returnDbTableName(), false);

		return true;
	}
}
