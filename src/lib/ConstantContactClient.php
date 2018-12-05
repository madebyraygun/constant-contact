<?php

/* Forked from https://github.com/classy-org/constantcontact-php-client */

namespace madebyraygun\constantcontact\lib;

use Psr\Http\Message\ResponseInterface;

/**
 * Class ConstantContactClient
 *
 * php client to request Constant Contact.

 */
class ConstantContactClient
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleClient;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $access_token;

    /**
     * @param $api_key
     * @param array $options Accept same options as Guzzle constructor
     * @throws \Exception
     */
    public function __construct($api_key, $access_token, array $config = [])
    {
        if (!is_string($api_key)) {
            throw new \Exception('api_key must be a string');
        }

        if (!is_string($access_token)) {
            throw new \Exception('access_token must be a string');
        }

        $this->apiKey = $api_key;
        $this->access_token = $access_token;

        $config = array_merge($config, [
            'base_uri' => 'https://api.constantcontact.com/v2/',
            'headers'  => ['Authorization' => 'Bearer ' . $this->access_token]
        ]);

        $this->guzzleClient = new \GuzzleHttp\Client($config);
    }

    /**
     * Quickly Grab Data.
     *
     * @param $uri
     * @param array $options
     *
     * @return mixed
     */
    public function getData($uri, $options = [])
    {
        return json_decode($this->request('GET', $uri, $options)->getBody()->getContents());
    }

    /**
     * Perform a request
     *
     * @param $method
     * @param string $uri
     * @param array $options
     *
     * @return mixed|ResponseInterface
     */
    public function request($method, $uri = '', array $options = [])
    {
        //Always prepend the api key.

        if (isset($options['query'])) {
            $options['query']['api_key'] = $this->apiKey;
        } else {
            $options['query'] = ['api_key' => $this->apiKey];
        }

        return $this->guzzleClient->request($method, $uri, $options);
    }

    /**
     * Add Contact
     *
     * @param array $payload
     *
     * @return mixed|ResponseInterface
     */
    public function addContact(array $payload, string $action = 'ACTION_BY_OWNER')
    {
        $query = [];
        $query['action_by'] = 'ACTION_BY_OWNER';

        $options = [
            'json'  => $payload,
            'query' => $query,
        ];

        return $response = $this->request('POST', 'contacts', $options);
    }

    /**
     * Forward any other call to guzzle client.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->guzzleClient, $method], $parameters);
    }

    /**
     * @return \GuzzleHttp\ClientInterface
     */
    public function getGuzzleClient()
    {
        return $this->guzzleClient;
    }

    /**
     *  This is for testing basically Mocking.
     *
     * @param $client
     */
    public function setGuzzleClient($client)
    {
        $this->guzzleClient = $client;
    }
}
