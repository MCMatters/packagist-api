<?php

declare(strict_types = 1);

namespace McMatters\Packagist;

use InvalidArgumentException;
use McMatters\Ticl\Client;

use function array_filter, explode, strpos;

use const false, null;

/**
 * Class Packagist
 *
 * @package McMatters\Packagist
 */
class Packagist
{
    /**
     * @var \McMatters\Ticl\Client
     */
    protected $httpClient;

    /**
     * Packagist constructor.
     */
    public function __construct()
    {
        $this->httpClient = new Client([
            'base_uri' => 'https://packagist.org',
        ]);
    }

    /**
     * @param array $query
     *
     * @return array
     * @see https://packagist.org/apidoc#list-packages-all
     */
    public function listPackages(array $query = []): array
    {
        return $this->httpClient
            ->withQuery($query)
            ->get('packages/list.json')
            ->json();
    }

    /**
     * @param string $vendor
     *
     * @return array
     * @see https://packagist.org/apidoc#list-packages-by-organization
     */
    public function listPackagesByOrganization(string $vendor): array
    {
        return $this->listPackages(['vendor' => $vendor]);
    }

    /**
     * @param string $type
     *
     * @return array
     * @see https://packagist.org/apidoc#list-packages-by-type
     */
    public function listPackagesByType(string $type): array
    {
        return $this->listPackages(['type' => $type]);
    }

    /**
     * @param string $keyword
     * @param int $page
     * @param string|null $tag
     * @param string|null $type
     *
     * @return array
     * @see https://packagist.org/apidoc#search-packages-by-name
     */
    public function search(
        string $keyword,
        int $page = 1,
        string $tag = null,
        string $type = null
    ): array {
        return $this->httpClient
            ->withQuery(array_filter([
                'q' => $keyword,
                'tags' => $tag,
                'type' => $type,
                'page' => $page,
            ]))
            ->get('search.json')
            ->json();
    }

    /**
     * @param string $tag
     * @param int $page
     *
     * @return array
     * @see https://packagist.org/apidoc#search-packages-by-tag
     */
    public function searchByTag(string $tag, int $page = 1): array
    {
        return $this->search('', $page, $tag);
    }

    /**
     * @param string $type
     * @param int $page
     *
     * @return array
     * @see https://packagist.org/apidoc#search-packages-by-type
     */
    public function searchByType(string $type, int $page = 1): array
    {
        return $this->search('', $page, null, $type);
    }

    /**
     * @param string $vendor
     * @param string|null $package
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @see https://packagist.org/apidoc#get-package-data
     */
    public function packageData(string $vendor, string $package = null): array
    {
        list($vendor, $package) = $this->splitPackageNames($vendor, $package);

        return $this->httpClient
            ->get("packages/{$vendor}/{$package}.json")
            ->json();
    }

    /**
     * @param string $vendor
     * @param string|null $package
     *
     * @return array|null
     *
     * @throws \InvalidArgumentException
     * @see https://packagist.org/apidoc#get-package-data
     */
    public function packageDataWithComposerMeta(
        string $vendor,
        string $package = null
    ) {
        list($vendor, $package) = $this->splitPackageNames($vendor, $package);

        return $this->httpClient
            ->get("p/{$vendor}/{$package}.json")
            ->json();
    }

    /**
     * @return array
     * @see https://packagist.org/apidoc#get-statistics
     */
    public function statistics(): array
    {
        return $this->httpClient->get('statistics.json')->json();
    }

    /**
     * @param string $vendor
     * @param string|null $package
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function splitPackageNames(
        string $vendor,
        string $package = null
    ): array {
        if (null === $package) {
            if (strpos($vendor, '/') === false) {
                throw new InvalidArgumentException('The package name is not specified');
            }

            return explode('/', $vendor);
        }

        return [$vendor, $package];
    }
}
