<?php
/**
 * ILIAS REST Plugin for the ILIAS LMS
 *
 * Authors: D.Schaefer, T.Hufschmidt <(schaefer|hufschmidt)@hrz.uni-marburg.de>
 * Since 2014
 */
namespace RESTController\extensions\docs_v1;

// This allows us to use shortcuts instead of full quantifier
use \RESTController\libs as Libs;


class DocumentationModel extends Libs\RESTModel
{

    public $docs = array();

    function __construct() {
        // /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // admin_v1
        $this->docs['get/v1/admin/files/:id'] = array(
            'route'         => '/v1/admin/files/:id',
            'verb'          => 'GET',
            'group'         => '/v1/admin',
            'description'   => 'Admin Route. Downloads a file with a given id (ref_id). If parameter is set to
                                true then only descriptions about a file in json format are provided.',
            'parameters'     => '{"meta_data":"true"}'
        );

        $this->docs['get/v1/admin/describe/:id'] = array(
            'route'         => '/v1/admin/describe/:id',
            'verb'          => 'GET',
            'group'         => '/v1/admin',
            'description'   => 'Returns a description of an object or user specified by its obj_id, ref_id, usr_id or file_id. Supported types: obj_id, ref_id, usr_id and file_id.',
            'parameters'    => '{"id_type":"ref_id"}'
        );

        $this->docs['get/v1/admin//desktop/overview/:id'] = array(
            'route'         => '/v1/admin//desktop/overview/:id',
            'verb'          => 'GET',
            'group'         => '/v1/admin',
            'description'   => 'Retrieves all items from the personal desktop of a user specified by its id.',
            'parameters'    => '{}'
        );

        $this->docs['delete/v1/admin//desktop/overview/:id'] = array(
            'route'         => '/v1/admin//desktop/overview/:id',
            'verb'          => 'DELETE',
            'group'         => '/v1/admin',
            'description'   => 'Deletes an item specified by ref_id from the personal desktop of the user specified by $id.',
            'parameters'    => '{"ref_id":"ID"}'
        );

        $this->docs['get/v1/admin/reporting/active_sessions'] = array(
            'route'         => '/v1/admin/reporting/active_sessions',
            'verb'          => 'GET',
            'group'         => '/v1/admin',
            'description'   => 'Returns a list of active user sessions.',
            'parameters'    => '{}'
        );

        $this->docs['get/v1/admin/reporting/session_stats'] = array(
            'route'         => '/v1/admin/reporting/session_stats',
            'verb'          => 'GET',
            'group'         => '/v1/admin',
            'description'   => 'Returns statistics about current user sessions.',
            'parameters'    => '{}'
        );

        $this->docs['get/v1/admin/reporting/session_stats_daily'] = array(
            'route'         => '/v1/admin/reporting/session_stats_daily',
            'verb'          => 'GET',
            'group'         => '/v1/admin',
            'description'   => 'Returns statistics about user sessions within a 24-h time frame.',
            'parameters'    => '{}'
        );

        $this->docs['get/v1/admin/reporting/session_stats_hourly'] = array(
            'route'         => '/v1/admin/reporting/session_stats_daily',
            'verb'          => 'GET',
            'group'         => '/v1/admin',
            'description'   => 'Returns statistics about user sessions within a 1-h time frame.',
            'parameters'    => '{}'
        );

        $this->docs['get/v1/admin/reporting/object_stats'] = array(
            'route'         => '/v1/admin/reporting/session_stats_daily',
            'verb'          => 'GET',
            'group'         => '/v1/admin',
            'description'   => 'Provides a snapshot of the total access counts of repository object types.',
            'parameters'    => '{}'
        );

        $this->docs['get/v1/admin/reporting/repository_stats'] = array(
            'route'         => '/v1/admin/reporting/repository_stats',
            'verb'          => 'GET',
            'group'         => '/v1/admin',
            'description'   => 'Returns the access statistics on all ILIAS repository objects within a 24-h time frame. In addition to the access numbers the route also provides information about the objects, such as title, type, location within the repository hierarchy',
            'parameters'    => '{}'
        );

        $this->docs['get/v1/admin/testquestion/:question_id'] = array(
            'route'         => '/v1/admin/testquestion/:question_id',
            'verb'          => 'GET',
            'group'         => '/v1/admin',
            'description'   => 'Returns a (json) representation of a test question given its question_id.',
            'parameters'    => '{}'
        );

        $this->docs['get/v1/admin/workspaces'] = array(
            'route'         => '/v1/admin/workspaces',
            'verb'          => 'GET',
            'group'         => '/v1/admin',
            'description'   => 'Provides an overview of workspaces of a limited amount of users.',
            'parameters'    => '{}'
        );

        $this->docs['get/v1/admin/workspaces/:user_id'] = array(
            'route'         => '/v1/admin/workspaces/:user_id',
            'verb'          => 'GET',
            'group'         => '/v1/admin',
            'description'   => 'Returns the content of the workspace from a user specified by her/his user id.',
            'parameters'    => '{}'
        );
        // /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // bibliography_v1



    }

    /**
     * Creates an internal (single-) key representation.
     * @param $route
     * @param $verb
     * @return string
     */
    private function getInternalKey($route, $verb)
    {
        $combinedKey = '';
        if (strlen($route)>0) {
            $loRoute = strtolower($route);
            $loVerb = strtolower($verb);
            if ($loRoute[0] == '/') {
                $combinedKey = $loVerb.$loRoute;
            } else {
                $combinedKey = $loVerb.'/'.$loRoute;
            }
        }
        return $combinedKey;
    }

    /**
     * Returns the documentation of a particular (route, verb) pair.
     * @param $route
     * @param $verb
     * @return array
     */
    function getDocumentation($route, $verb)
    {
        $result = array();
        $result [] = $this->docs[$this->getInternalKey($route, $verb)];
        return $result;
    }

    /**
     * Returns the documentation of all available (route, verb) pairs
     * @return array
     */
    function getCompleteApiDocumentation()
    {
        $result = array();
        foreach ($this->docs as $key => $value) {
            $result[] = $value;
        }
        return $result;
    }
    
}
