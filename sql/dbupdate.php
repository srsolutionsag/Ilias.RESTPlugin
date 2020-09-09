<?php
/**
 * ILIAS REST Plugin for the ILIAS LMS
 *
 * Authors: D.Schaefer and T.Hufschmidt <(schaefer|hufschmidt)@hrz.uni-marburg.de>
 * Since 2014
 */

?>
<#1>
<?php
/**
 * @var \ILIAS\DI\Container $container
 */
$container = $GLOBALS["DIC"];
$log = $container->logger();
$database = $container->database();

$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
    ),
    'setting_name' => array(
        'type' => 'text',
        'length' => 128,
        'fixed' => false,
        'notnull' => true,
    ),
    'setting_value' => array(
        'type' => 'text',
        'length' => 512,
        'fixed' => false,
        'notnull' => false,
    ),
);
$database->createTable('ui_uihk_rest_config', $fields, true);

$database->addPrimaryKey('ui_uihk_rest_config', array('id'));
$database->manipulate('ALTER TABLE ui_uihk_rest_config CHANGE id id INT NOT NULL AUTO_INCREMENT');
$database->manipulate('ALTER TABLE ui_uihk_rest_config ADD UNIQUE (setting_name)');

$log->root()->debug('Plugin REST -> DB-Update #1: Created ui_uihk_rest_config.');
?>
<#2>
<?php
/**
 * @var \ILIAS\DI\Container $container
 */
$container = $GLOBALS["DIC"];
$log = $container->logger();
$database = $container->database();

function gen_uuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for 'time_low'
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),

        // 16 bits for 'time_mid'
        mt_rand(0, 0xffff),

        // 16 bits for 'time_hi_and_version',
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,

        // 16 bits, 8 bits for 'clk_seq_hi_res',
        // 8 bits for 'clk_seq_low',
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,

        // 48 bits for 'node'
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

$soap_username = 'rest_sys_user';
$soap_password = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5)), 0, 10);

$database->insert('ui_uihk_rest_config', array(
    'setting_name' => array('text', 'salt'),
    'setting_value' => array('text', gen_uuid()),
));
$database->insert('ui_uihk_rest_config', array(
    'setting_name' => array('text', 'soap_username'),
    'setting_value' => array('text', $soap_username),
));
$database->insert('ui_uihk_rest_config', array(
    'setting_name' => array('text', 'soap_password'),
    'setting_value' => array('text', $soap_password),
));
$database->insert('ui_uihk_rest_config', array(
    'setting_name' => array('text', 'access_token_ttl'),
    'setting_value' => array('text', '30'),
));
$database->insert('ui_uihk_rest_config', array(
    'setting_name' => array('text', 'refresh_token_ttl'),
    'setting_value' => array('text', '525600'),
));
$database->insert('ui_uihk_rest_config', array(
    'setting_name' => array('text', 'authorization_token_ttl'),
    'setting_value' => array('text', '5'),
));
$database->insert('ui_uihk_rest_config', array(
    'setting_name' => array('text', 'short_token_ttl'),
    'setting_value' => array('text', '1'),
));
$database->insert('ui_uihk_rest_config', array(
    'setting_name' => array('text', 'log_file'),
    'setting_value' => array('text', null),
));
$database->insert('ui_uihk_rest_config', array(
    'setting_name' => array('text', 'log_level'),
    'setting_value' => array('text', null),
));

$log->root()->alert('Plugin REST -> DB-Update #2: Filled ui_uihk_rest_config.');
?>
<#3>
<?php
/**
 * @var \ILIAS\DI\Container $container
 */
$container = $GLOBALS["DIC"];
$log = $container->logger();
$database = $container->database();

$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
    ),
    'api_key' => array(
        'type' => 'text',
        'length' => 128,
        'fixed' => false,
        'notnull' => true,
    ),
    'api_secret' => array(
        'type' => 'text',
        'length' => 256,
        'fixed' => false,
        'notnull' => false,
    ),
    'cert_serial' => array(
        'type' => 'text',
        'length' => 256,
        'fixed' => false,
        'notnull' => false,
    ),
    'cert_issuer' => array(
        'type' => 'text',
        'length' => 256,
        'fixed' => false,
        'notnull' => false,
    ),
    'cert_subject' => array(
        'type' => 'text',
        'length' => 256,
        'fixed' => false,
        'notnull' => false,
    ),
    'redirect_uri' => array(
        'type' => 'text',
        'length' => 512,
        'fixed' => false,
        'notnull' => false,
    ),
    'consent_message' => array(
        'type' => 'text',
        'length' => 4000,
        'fixed' => false,
        'notnull' => false,
    ),
    'client_credentials_userid' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => -1,
    ),
    'grant_client_credentials' => array(
        'type' => 'integer',
        'length' => 1,
    ),
    'grant_authorization_code' => array(
        'type' => 'integer',
        'length' => 1,
    ),
    'grant_implicit' => array(
        'type' => 'integer',
        'length' => 1,
    ),
    'grant_resource_owner' => array(
        'type' => 'integer',
        'length' => 1,
    ),
    'refresh_authorization_code' => array(
        'type' => 'integer',
        'length' => 1,
    ),
    'refresh_resource_owner' => array(
        'type' => 'integer',
        'length' => 1,
    ),
    'grant_bridge' => array(
        'type' => 'text',
        'length' => 1,
        'fixed' => false,
        'notnull' => false,
    ),
    'ips' => array(
        'type' => 'text',
        'length' => 256,
        'fixed' => false,
        'notnull' => false,
    ),
    'users' => array(
        'type' => 'text',
        'length' => 1024,
        'fixed' => false,
        'notnull' => false,
    ),
    'scopes' => array(
        'type' => 'text',
        'length' => 1024,
        'fixed' => false,
        'notnull' => false,
    ),
    'description' => array(
        'type' => 'text',
        'length' => 4000,
        'fixed' => false,
        'notnull' => false,
    ),
);
$database->createTable('ui_uihk_rest_client', $fields, true);

$database->addPrimaryKey('ui_uihk_rest_client', array('id'));
$database->manipulate('ALTER TABLE ui_uihk_rest_client CHANGE id id INT NOT NULL AUTO_INCREMENT');
$database->manipulate('ALTER TABLE ui_uihk_rest_client ADD UNIQUE (api_key)');

$log->root()->debug('Plugin REST -> DB-Update #3: Created ui_uihk_rest_client.');
?>
<#4>
<?php
/**
 * @var \ILIAS\DI\Container $container
 */
$container = $GLOBALS["DIC"];
$log = $container->logger();
$database = $container->database();

$api_key = 'apollon';
//$api_secret      = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5)), 0, 10);
$api_secret = '';
$description = 'REST Admin-Panel';

$database->insert('ui_uihk_rest_client', array(
    'api_key' => array('text', $api_key),
    'api_secret' => array('text', $api_secret),
    'grant_resource_owner' => array('integer', 1),
    'description' => array('text', $description),
    'grant_bridge' => array('text', 'b'),
));
// TODO: Store into admin-panel config

/**
 * @var \ILIAS\DI\Container $container
 */
$container = $GLOBALS["DIC"];
$log = $container->logger();
$log->root()->debug('Plugin REST -> DB-Update #4: Filled ui_uihk_rest_client.');
?>
<#5>
<?php
/**
 * @var \ILIAS\DI\Container $container
 */
$container = $GLOBALS["DIC"];
$log = $container->logger();
$database = $container->database();

$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
    ),
    'api_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
    ),
    'pattern' => array(
        'type' => 'text',
        'length' => 256,
        'fixed' => false,
        'notnull' => true,
    ),
    'verb' => array(
        'type' => 'text',
        'length' => 16,
        'fixed' => false,
        'notnull' => true,
    ),
);
$database->createTable('ui_uihk_rest_perm', $fields, true);

$database->addPrimaryKey('ui_uihk_rest_perm', array('id'));
$database->manipulate('ALTER TABLE ui_uihk_rest_perm CHANGE id id INT NOT NULL AUTO_INCREMENT');

$log->root()->debug('Plugin REST -> DB-Update #5: Created ui_uihk_rest_perm.');
?>
<#6>
<?php
/**
 * @var \ILIAS\DI\Container $container
 */
$container = $GLOBALS["DIC"];
$log = $container->logger();
$database = $container->database();

$id = 1;
$database->insert('ui_uihk_rest_perm', array(
    'api_id' => array('integer', $id),
    'pattern' => array('text', '/v2/admin/clients'),
    'verb' => array('text', 'GET'),
));
$database->insert('ui_uihk_rest_perm', array(
    'api_id' => array('integer', $id),
    'pattern' => array('text', '/v2/admin/client/:id'),
    'verb' => array('text', 'GET'),
));
$database->insert('ui_uihk_rest_perm', array(
    'api_id' => array('integer', $id),
    'pattern' => array('text', '/v2/admin/client/'),
    'verb' => array('text', 'POST'),
));
$database->insert('ui_uihk_rest_perm', array(
    'api_id' => array('integer', $id),
    'pattern' => array('text', '/v2/admin/client/:id'),
    'verb' => array('text', 'PUT'),
));
$database->insert('ui_uihk_rest_perm', array(
    'api_id' => array('integer', $id),
    'pattern' => array('text', '/v2/admin/client/:id'),
    'verb' => array('text', 'DELETE'),
));
$database->insert('ui_uihk_rest_perm', array(
    'api_id' => array('integer', $id),
    'pattern' => array('text', '/v2/admin/config/:key'),
    'verb' => array('text', 'GET'),
));
$database->insert('ui_uihk_rest_perm', array(
    'api_id' => array('integer', $id),
    'pattern' => array('text', '/v2/admin/config/:key'),
    'verb' => array('text', 'PUT'),
));
$database->insert('ui_uihk_rest_perm', array(
    'api_id' => array('integer', $id),
    'pattern' => array('text', '/v2/admin/permissions/:clientId'),
    'verb' => array('text', 'GET'),
));
$database->insert('ui_uihk_rest_perm', array(
    'api_id' => array('integer', $id),
    'pattern' => array('text', '/v2/admin/permission/:clientId'),
    'verb' => array('text', 'POST'),
));
$database->insert('ui_uihk_rest_perm', array(
    'api_id' => array('integer', $id),
    'pattern' => array('text', '/v2/admin/permission/:permissionId'),
    'verb' => array('text', 'DELETE'),
));
$database->insert('ui_uihk_rest_perm', array(
    'api_id' => array('integer', $id),
    'pattern' => array('text', '/v1/clients'),
    'verb' => array('text', 'GET'),
));
$database->insert('ui_uihk_rest_perm', array(
    'api_id' => array('integer', $id),
    'pattern' => array('text', '/v1/clients/:id'),
    'verb' => array('text', 'PUT'),
));
$database->insert('ui_uihk_rest_perm', array(
    'api_id' => array('integer', $id),
    'pattern' => array('text', '/v1/clients/'),
    'verb' => array('text', 'POST'),
));
$database->insert('ui_uihk_rest_perm', array(
    'api_id' => array('integer', $id),
    'pattern' => array('text', '/v1/clients/:id'),
    'verb' => array('text', 'DELETE'),
));

$log->root()->debug('Plugin REST -> DB-Update #6: Filled ui_uihk_rest_perm.');
?>
<#7>
<?php
/**
 * @var \ILIAS\DI\Container $container
 */
$container = $GLOBALS["DIC"];
$log = $container->logger();
$database = $container->database();

$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
    ),
    'hash' => array(
        'type' => 'text',
        'length' => 256,
        'fixed' => false,
        'notnull' => true,
    ),
    'token' => array(
        'type' => 'text',
        'length' => 512,
        'fixed' => false,
        'notnull' => true,
    ),
    'last_refresh' => array(
        'type' => 'timestamp',
    ),
    'created' => array(
        'type' => 'timestamp',
    ),
    'refreshes' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
    ),
);
$database->createTable('ui_uihk_rest_refresh', $fields, true);

$database->addPrimaryKey('ui_uihk_rest_refresh', array('id'));
$database->manipulate('ALTER TABLE ui_uihk_rest_refresh CHANGE id id INT NOT NULL AUTO_INCREMENT');

$log->root()->debug('Plugin REST -> DB-Update #7: Created ui_uihk_rest_refresh.');
?>
<#8>
<?php
/**
 * @var \ILIAS\DI\Container $container
 */
$container = $GLOBALS["DIC"];
$log = $container->logger();
$database = $container->database();

$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
    ),
    'hash' => array(
        'type' => 'text',
        'length' => 256,
        'fixed' => false,
        'notnull' => true,
    ),
    'token' => array(
        'type' => 'text',
        'length' => 512,
        'fixed' => false,
        'notnull' => true,
    ),
    'expires' => array(
        'type' => 'timestamp',
    ),
);
$database->createTable('ui_uihk_rest_authcode', $fields, true);

$database->addPrimaryKey('ui_uihk_rest_authcode', array('id'));
$database->manipulate('ALTER TABLE ui_uihk_rest_authcode CHANGE id id INT NOT NULL AUTO_INCREMENT');

$log->root()->debug('Plugin REST -> DB-Update #8: Created ui_uihk_rest_authcode.');
?>
<#9>
<?php
/**
 * @var \ILIAS\DI\Container $container
 */
$container = $GLOBALS["DIC"];
$log = $container->logger();
$database = $container->database();

$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
    ),
    'hash' => array(
        'type' => 'text',
        'length' => 256,
        'fixed' => false,
        'notnull' => true,
    ),
    'token' => array(
        'type' => 'text',
        'length' => 512,
        'fixed' => false,
        'notnull' => true,
    ),
    'expires' => array(
        'type' => 'timestamp',
    ),
);
$database->createTable('ui_uihk_rest_access', $fields, true);

$database->addPrimaryKey('ui_uihk_rest_access', array('id'));
$database->manipulate('ALTER TABLE ui_uihk_rest_access CHANGE id id INT NOT NULL AUTO_INCREMENT');

$log->root()->debug('Plugin REST -> DB-Update #9: Created ui_uihk_rest_access.');
?>
<#10>
<?php
/**
 * @var \ILIAS\DI\Container $container
 */
$container = $GLOBALS["DIC"];
$log = $container->logger();
$database = $container->database();

$fields = array(
    'user_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
    ),
    'token' => array(
        'type' => 'text',
        'length' => 128,
        'fixed' => false,
        'notnull' => true,
    ),
    'expires' => array(
        'type' => 'timestamp',
        'notnull' => true,
    ),
);
$database->createTable('ui_uihk_rest_token', $fields, true);

$database->addPrimaryKey('ui_uihk_rest_token', array('user_id'));

$log->root()->debug('Plugin REST -> DB-Update #10: Created ui_uihk_rest_token.');
?>
<#11>
<?php
/**
 * @var \ILIAS\DI\Container $container
 */
$container = $GLOBALS["DIC"];
$log = $container->logger();
$database = $container->database();

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/services/FileHashing/entity/HashCacheEntry.php';

use RESTController\extensions\ILIASApp\V2\HashCacheEntry;

HashCacheEntry::installDB();

$log->root()->debug('Plugin REST -> DB-Update #11: Created ui_uihk_rest_hashcache.');
?>
