<?php

use RESTController\core\clients_v1\ClientsLegacyModel;
use \RESTController\database as Database;

/**
 * Class ClientConfiguration
 *
 * this class provides a collection of static methods that allow
 * the configuration of REST clients without having to use the API
 */
class ClientConfiguration {

    /**
     * Creates a new REST ApiKey/Client using the provided parameters
     *
     * @param $params string[]
     */
    static function createClient($params) {
        // Fetch request-parameters (into table-row format)
        $row = array(
            'id'                          => $params['id'],
            'api_key'                     => isset($params['api_key']) ? $params['api_key'] : true,
            'api_secret'                  => $params['api_secret'],
            'cert_serial'                 => $params['cert_serial'],
            'cert_issuer'                 => $params['cert_issuer'],
            'cert_subject'                => $params['cert_subject'],
            'redirect_uri'                => $params['redirect_uri'],
            'consent_message'             => $params['consent_message'],
            'client_credentials_userid'   => isset($params['client_credentials_userid']) ? $params['client_credentials_userid'] : 6,
            'grant_client_credentials'    => $params['grant_client_credentials'] || false,
            'grant_authorization_code'    => $params['grant_authorization_code'] || false,
            'grant_implicit'              => $params['grant_implicit'] || false,
            'grant_resource_owner'        => $params['grant_resource_owner'] || false,
            'refresh_authorization_code'  => $params['refresh_authorization_code'] || false,
            'refresh_resource_owner'      => $params['refresh_resource_owner'] || false,
            'grant_bridge'                => $params['grant_bridge'] || false,
            'ips'                         => $params['ips'],
            'users'                       => $params['users'],
            'scopes'                      => $params['scopes'],
            'description'                 => $params['description'],
        );

        // Construct new table from given row/request-parameter
        $client = Database\RESTclient::fromRow($row);
        $id     = $row['id'];

        // Check if clientId was given and this client already exists?
        if ($id == null || !Database\RESTclient::existsByPrimary($id)) {
            // Insert (and possibly generate new clientId [its the primaryKey])
            $client->insert($id == null);
            $api_id = $client->getKey('id');
            if ($params["permissions"]) {
                ClientsLegacyModel::setPermissions($api_id, $params["permissions"]);
            }
        }
    }

    /**
     * Deletes the REST client given by :id (api-id)
     *
     * @param $id number
     * @return number
     */
    static function deleteClient($id) {
        ClientsLegacyModel::deleteClient($id);
        return $id;
    }

    /**
     * Returns a list of all REST clients and their settings
     *
     * @return mixed
     */
    static function getClients() {
        return ClientsLegacyModel::getClients();
    }

    /**
     *  Updates a config settings with a new value
     *
     * @param $key string
     * @param $value string
     */
    static function setClientConfig($key, $value) {
        // Fetch current table entry and update with new value
        $settings = Database\RESTconfig::fromSettingName($key);
        $settings->setKey('setting_value', $value);
        $settings->update();
    }

    /**
     * Adds a new permission with given parameters to the selected client
     *
     * @param $clientId string
     * @param $pattern string
     * @param $verb string
     * @param $id number
     * @return number PermissionId of newly created database entry for this permission
     * @throws \RESTController\libs\Exceptions\Database
     */
    static function addClientPermission($clientId, $pattern, $verb, $id = null) {
        // Fetch request-parameters (into table-row format)
        $row = array(
            'api_id' => intval($clientId),
            'id' => $id,
            'pattern' => $pattern,
            'verb' => $verb,
        );

        // Construct new table from given row/request-parameter
        $permission = Database\RESTpermission::fromRow($row);
        $id = $row['id'];

        // Check for duplicate entry
        if ($permission->exists('api_id = {{api_id}} AND pattern = {{patern}} AND verb = {{verb}}'))
            return null;

        // Check if permissionId was given and this permission already exists?
        if ($id == null || !Database\RESTpermission::existsByPrimary($id)) {
            // Insert (and possibly generate new permissionId [its the primaryKey])
            $permission->insert($id == null);
            return $permission->getKey('id');
        }

        return null;
    }

}