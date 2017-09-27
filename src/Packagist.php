<?php

declare(strict_types = 1);

namespace McMatters\Packagist;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Throwable;
use const false, null, true;
use function array_filter, explode, json_decode, strpos;

/**
 * Class Packagist
 *
 * @package McMatters\Packagist
 */
class Packagist
{
    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $baseUrl = 'https://packagist.org/';

    /**
     * Packagist constructor.
     */
    public function __construct()
    {
        $this->httpClient = new Client();
    }

    /**
     * @return array|null
     */
    public function listPackages()
    {
        return $this->request($this->getListUrl());
    }

    /**
     * @param string $vendor
     *
     * @return array|null
     */
    public function listPackagesByOrganization(string $vendor)
    {
        return $this->request($this->getListUrl(), ['vendor' => $vendor]);
    }

    /**
     * @param string $type
     *
     * @return array|null
     */
    public function listPackagesByType(string $type)
    {
        return $this->request($this->getListUrl(), ['type' => $type]);
    }

    /**
     * @param string $keyword
     * @param int $page
     * @param string|null $tag
     * @param string|null $type
     *
     * @return array|null
     */
    public function search(
        string $keyword,
        int $page = 1,
        string $tag = null,
        string $type = null
    ) {
        return $this->request(
            $this->getSearchUrl(),
            array_filter([
                'q'    => $keyword,
                'tags' => $tag,
                'type' => $type,
                'page' => $page,
            ])
        );
    }

    /**
     * @param string $tag
     * @param int $page
     *
     * @return array|null
     */
    public function searchByTag(string $tag, int $page = 1)
    {
        return $this->request(
            $this->getSearchUrl(),
            ['tags' => $tag, 'page' => $page]
        );
    }

    /**
     * @param string $type
     * @param int $page
     *
     * @return array|null
     */
    public function searchByType(string $type, int $page = 1)
    {
        return $this->request(
            $this->getSearchUrl(),
            ['type' => $type, 'page' => $page]
        );
    }

    /**
     * @param string $vendor
     * @param string|null $package
     *
     * @return array|null
     * @throws InvalidArgumentException
     */
    public function packageData(string $vendor, string $package = null)
    {
        list($vendor, $package) = $this->splitPackageNames($vendor, $package);

        return $this->request(
            "{$this->baseUrl}packages/{$vendor}/{$package}.json"
        );
    }

    /**
     * @param string $vendor
     * @param string|null $package
     *
     * @return array|null
     * @throws InvalidArgumentException
     */
    public function packageDataWithComposerMeta(
        string $vendor,
        string $package = null
    ) {
        list($vendor, $package) = $this->splitPackageNames($vendor, $package);

        return $this->request("{$this->baseUrl}p/{$vendor}/{$package}.json");
    }

    /**
     * @return string
     */
    protected function getListUrl(): string
    {
        return "{$this->baseUrl}packages/list.json";
    }

    /**
     * @return string
     */
    protected function getSearchUrl(): string
    {
        return "{$this->baseUrl}search.json";
    }

    /**
     * @param string $vendor
     * @param string|null $package
     *
     * @return array
     * @throws InvalidArgumentException
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

    /**
     * @param string $url
     * @param array $query
     *
     * @return array|null
     */
    protected function request(string $url, array $query = [])
    {
        try {
            $request = $this->httpClient->get($url, ['query' => $query]);

            return json_decode($request->getBody()->getContents(), true);
        } catch (Throwable $e) {
            return null;
        }
    }
}
