<?php namespace SammyK\FacebookQueryBuilder;

class FQB
{
    /**
     * The root edge we are working with.
     *
     * @var \SammyK\FacebookQueryBuilder\RootEdge
     */
    public $root_edge;

    /**
     * The connection to the Facebook Graph API.
     *
     * @var \SammyK\FacebookQueryBuilder\Connection
     */
    protected static $connection;

    /**
     * Data to be sent to Graph with POST request.
     *
     * @var array
     */
    public $post_data = [];

    /**
     * New up a new RootEdge instance.
     *
     * @param string $edge_name
     * @param array $fields The fields we want on the root edge
     */
    public function __construct($edge_name = null, array $fields = [])
    {
        if (isset($edge_name))
        {
            $this->root_edge = new RootEdge($edge_name, $fields);
        }
    }

    /**
     * Return the RootEdge as a string.
     *
     * @return string
     */
    public function getQueryUrl()
    {
        return (string) $this->root_edge;
    }

    /**
     * Sets the app credentials.
     *
     * @param int $app_id
     * @param string $app_secret
     */
    public static function setAppCredentials($app_id, $app_secret)
    {
        static::getConnection()->setAppCredentials($app_id, $app_secret);
    }

    /**
     * Sets the access token to be used for all API requests.
     *
     * @param string $access_token
     */
    public static function setAccessToken($access_token)
    {
        static::getConnection()->setAccessToken($access_token);
    }

    /**
     * Send GET request to Facebook Graph API.
     *
     * @param array $fields The fields we want on the root edge
     * @return \SammyK\FacebookQueryBuilder\Collection
     */
    public function get(array $fields = [])
    {
        $this->root_edge->fields($fields);

        return static::getConnection()->get($this->root_edge)->getResponse();
    }

    /**
     * Send POST request to Facebook Graph API.
     *
     * @return \SammyK\FacebookQueryBuilder\Collection
     */
    public function post()
    {
        return static::getConnection()->post($this->root_edge, $this->post_data)->getResponse();
    }

    /**
     * Send DELETE request to Facebook Graph API.
     *
     * @return \SammyK\FacebookQueryBuilder\Collection
     */
    public function delete()
    {
        return static::getConnection()->delete($this->root_edge)->getResponse();
    }

    /**
     * Alias to RootEdge
     *
     * @param int $limit
     * @return \SammyK\FacebookQueryBuilder\FQB
     */
    public function limit($limit)
    {
        $this->root_edge->limit($limit);

        return $this;
    }

    /**
     * Alias to RootEdge
     *
     * @param array|string $fields The fields we want on the root edge
     * @return \SammyK\FacebookQueryBuilder\FQB
     */
    public function fields($fields)
    {
        if ( ! is_array($fields))
        {
            $fields = func_get_args();
        }

        $this->root_edge->fields($fields);

        return $this;
    }

    /**
     * Data to be sent to Graph with POST request.
     *
     * @param array $data
     * @return \SammyK\FacebookQueryBuilder\FQB
     */
    public function with(array $data)
    {
        $this->post_data = $data;

        return $this;
    }

    /**
     * Get the connection to Facebook.
     *
     * @return \SammyK\FacebookQueryBuilder\Connection
     */
    public static function getConnection()
    {
        if (isset(static::$connection)) return static::$connection;

        return static::$connection = new Connection(new FacebookRequestMaker(), new Response());
    }

    /**
     * Set the connection to Facebook.
     *
     * @param \SammyK\FacebookQueryBuilder\Connection $connection
     * @return void
     */
    public static function setConnection(Connection $connection)
    {
        static::$connection = $connection;
    }

    /**
     * New up an Edge instance.
     *
     * @param array $fields The fields we want on the edge
     * @param string $edge_name
     * @return \SammyK\FacebookQueryBuilder\Edge
     */
    public function edge($edge_name, array $fields = [])
    {
        return new Edge($edge_name, $fields);
    }

    /**
     * New up an instance.
     *
     * @param string $edge_name The edge name
     * @param array $fields The fields we want on the root edge
     * @return \SammyK\FacebookQueryBuilder\FQB
     */
    public function object($edge_name, array $fields = [])
    {
        return new static($edge_name, $fields);
    }

    /**
     * Returns root edge as nicely formatted string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->root_edge->compileEdge();
    }
}
