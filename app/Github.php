<?php
use GuzzleHttp\Client;

class Github {
    private static $_client;

    /**
     * Search users by their names
     * @param $search string
     * @return array|bool
     */
    public static function searchAccounts($search)
    {
        return self::makeRequest('/search/users', array('q' => $search));
    }

    private static function getClient()
    {
        if(!self::$_client)
            self::$_client = new GuzzleHttp\Client(array('base_uri' => Env::get('github_api_url', 'https://api.github.com')));

        return self::$_client;
    }

    /**
     * Launch a request
     * @param $path string URI
     * @param null $query parameters
     * @return array|bool json_decode of the response
     */
    private static function makeRequest($path, $query = null)
    {
        $response = self::getClient()->request(
            'GET',
            $path,
            array(
                // Utilisation d'un open cert pour s'assurer du non-blocage sous windows/... sans environnement propre
                // À retirer pour une utilisation en production
                // https://curl.haxx.se/docs/caextract.html
                'verify'    =>_RESSOURCES_PATH_.'cacert.pem',
                'query'     => $query
            )
        );

        $body = $response->getBody();
        if(!strlen($body))
            return false;

        $arr = @json_decode($body, true);
        if(!is_array($arr) || !sizeof($arr))
            return false;

        return $arr;
    }
}