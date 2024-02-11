<?php declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @method ContainerInterface getContainer()
 */
trait ApiTestTrait
{

    private function createApiClient(array $options = [], array $server = []): ApiClient
    {
        $server['HTTP_HOST'] = $options['host'] ?? 'api.localhost';

        if (!empty($options['token'])) {
            $server['X-API-KEY'] = $options['apiKey'];
        }

        $client = $this->getContainer()->get('test.client');

        $reboot = $options['reboot'] ?? false;
        if (!$reboot) {
            $client->disableReboot();
        }

        $client->setServerParameters($server);

        return new ApiClient($client);
    }
}
