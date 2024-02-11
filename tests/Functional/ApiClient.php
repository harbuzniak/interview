<?php declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class ApiClient
{
    /** @var KernelBrowser */
    private $client;

    public function __construct(KernelBrowser $client)
    {
        $this->client = $client;
    }

    public function callApi(string $method, string $url, array $params = []): Response
    {
        $content = null;

        $server = ['HTTP_ACCEPT' => $params['accept'] ?? 'application/json'];

        if (isset($params['json']) && is_array($params['json'])) {
            $server['CONTENT_TYPE'] = 'application/json';
            $content = json_encode($params['json']);
            unset($params['json']);
        }
        if (isset($params['payload']) && is_string($params['payload'])) {
            $content = $params['payload'];
        }

        $queryString = '';
        if (isset($params['query']) && is_array($params['query'])) {
            $queryString = '?'.http_build_query($params['query']);
            unset($params['query']);
        }
        if (isset($params['headers']) && is_array($params['headers'])) {
            $server = array_merge($server, $params['headers']);
            unset($params['headers']);
        }

        $this->client->request($method, $url.$queryString, $params, [], $server, $content);
        return $this->client->getResponse();
    }

    public function request(string $method, string $uri, array $parameters = array(), array $files = array(),
                            array $server = array(), string $content = null, bool $changeHistory = true): Response
    {
        $this->client->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
        return $this->client->getResponse();
    }
}
