<?php
/**
 * Mage Scan
 *
 * PHP version 5
 *
 * @category  MageScan
 * @package   MageScan
 * @author    Steve Robbins <steve@steverobbins.com>
 * @copyright 2015 Steve Robbins
 * @license   http://creativecommons.org/licenses/by/4.0/ CC BY 4.0
 * @link      https://github.com/steverobbins/magescan
 */

namespace MageScan;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Response;

/**
 * Make a cURL request to a url
 *
 * @category  MageScan
 * @package   MageScan
 * @author    Steve Robbins <steve@steverobbins.com>
 * @copyright 2015 Steve Robbins
 * @license   http://creativecommons.org/licenses/by/4.0/ CC BY 4.0
 * @link      https://github.com/steverobbins/magescan
 */
class Request
{
    const REQUEST_TIMEOUT = 15.0;

    /**
     * If true, SSL does not have to be verified
     *
     * @var boolean
     */
    protected $insecure = false;

    protected $client;

    /**
     * Initialize request object
     *
     * @param string  $baseUri
     * @param boolean $verify
     */
    public function __construct($baseUri, $verify = true)
    {
        $this->client = new Client([
            'base_uri' => $baseUri,
            //'timeout'  => self::REQUEST_TIMEOUT,
            'verify'   => $verify,
            'http_errors' => false,
        ]);
    }

    /**
     * Get a path
     *
     * @param string $path
     * @param array  $params
     *
     * @return GuzzleHttp\Psr7\Response
     */
    public function get($path, array $params = [])
    {
        return $this->client->get('/' . $path, $params);
    }

    /**
     * Get many paths asyncronously
     *
     * @param string[] $paths
     * @param array    $params
     *
     * @return GuzzleHttp\Psr7\Response[]
     */
    public function getMany($paths, array $params = [])
    {
        $promises = [];
        foreach ($paths as $path) {
            $promises[$path] = $this->client->getAsync('/' . $path, $params);
        }
        return Promise\unwrap($promises);
    }

    /**
     * Post to a path
     *
     * @param string $path
     * @param array  $params
     *
     * @return GuzzleHttp\Psr7\Response
     */
    public function post($path, array $params = [])
    {
        return $client->post('/' . $path, $params);
    }

    /**
     * Manipulate header data into a parsable format
     *
     * @param string $rawData
     *
     * @return array
     */
    public function parseHeader($rawData)
    {
        $data = [];
        foreach (explode("\n", trim($rawData)) as $line) {
            $bits = explode(': ', $line);
            if (count($bits) > 1) {
                $key = $bits[0];
                unset($bits[0]);
                $data[$key] = trim(implode(': ', $bits));
            }
        }
        return $data;
    }

    /**
     * Parse out the count from the response
     *
     * @param Response $response
     * @param string    $pattern
     * @param boolean   $returnAll
     *
     * @return string|array|boolean
     */
    public function findMatchInResponse(Response $response, $pattern, $returnAll = false)
    {
        if ($response->getStatusCode() == 200) {
            if (preg_match($pattern, $response->getBody(), $match)
                && (isset($match[1]) || $returnAll)
            ) {
                return $returnAll ? $match : $match[1];
            }
        }
        return false;
    }
}
