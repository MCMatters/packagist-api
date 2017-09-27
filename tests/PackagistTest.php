<?php

declare(strict_types = 1);

namespace McMatters\Packagist\Tests;

use InvalidArgumentException;
use McMatters\Packagist\Packagist;
use PHPUnit\Framework\TestCase;

/**
 * Class PackagistTest
 *
 * @package McMatters\Packagist\Tests
 */
class PackagistTest extends TestCase
{
    /**
     * @var Packagist
     */
    protected $packagist;

    /**
     * @var string
     */
    protected $vendor = 'mcmatters';

    /**
     * @var string
     */
    protected $packageName = 'laravel-helpers';

    /**
     * @var string
     */
    protected $package;

    /**
     * PackagistTest constructor.
     *
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->packagist = new Packagist();
        $this->package = "{$this->vendor}/{$this->packageName}";
    }

    /**
     * Test "listPackages" method.
     */
    public function testListPackages()
    {
        $list = $this->packagist->listPackages();

        $this->assertArrayHasKey('packageNames', $list);
        $this->assertNotEmpty($list['packageNames']);
        $this->assertTrue(in_array($this->package, $list['packageNames'], true));
    }

    /**
     * Test "listPackagesByOrganization" method.
     */
    public function testListByOrganization()
    {
        $packages = $this->packagist->listPackagesByOrganization($this->vendor);
        $this->assertArrayHasKey('packageNames', $packages);
        $this->assertNotEmpty($packages['packageNames']);
        $this->assertTrue(in_array($this->package, $packages['packageNames'], true));
    }

    /**
     * Test "listPackagesByType" method.
     */
    public function testListByType()
    {
        $packages = $this->packagist->listPackagesByType('library');
        $this->assertArrayHasKey('packageNames', $packages);
        $this->assertNotEmpty($packages['packageNames']);
        $this->assertTrue(in_array($this->package, $packages['packageNames'], true));
    }

    /**
     * Test "search" method.
     */
    public function testSearch()
    {
        $packages = $this->packagist->search('laravel-helpers');
        $this->assertArrayHasKey('results', $packages);
        $this->assertArrayHasKey('total', $packages);

        $packages = $this->packagist->search($this->vendor);
        $this->assertArrayHasKey('results', $packages);
        $this->assertArrayHasKey('total', $packages);
        $this->assertNotEmpty(
            array_filter($packages['results'], function ($package) {
                return $package['name'] === $this->package;
            })
        );
    }

    /**
     * Test "searchByTag" method.
     */
    public function testSearchByTag()
    {
        $packages = $this->packagist->searchByTag('mongodb');
        $this->assertArrayHasKey('results', $packages);
        $this->assertArrayHasKey('total', $packages);
    }

    /**
     * Test "searchByType" method.
     */
    public function testSearchByType()
    {
        $packages = $this->packagist->searchByType('symfony-bundle');
        $this->assertArrayHasKey('results', $packages);
        $this->assertArrayHasKey('total', $packages);
    }

    /**
     * Test "packageData" method.
     */
    public function testPackageData()
    {
        $package = $this->packagist->packageData($this->vendor, $this->packageName);
        $this->assertArrayHasKey('package', $package);
        $this->assertArrayHasKey('name', $package['package']);
        $this->assertSame($this->package, $package['package']['name']);
        $this->assertArrayHasKey('versions', $package['package']);
        $this->assertArrayHasKey('dev-master', $package['package']['versions']);

        $package = $this->packagist->packageData($this->package);
        $this->assertArrayHasKey('package', $package);
        $this->assertArrayHasKey('name', $package['package']);
        $this->assertSame($this->package, $package['package']['name']);
        $this->assertArrayHasKey('versions', $package['package']);
        $this->assertArrayHasKey('dev-master', $package['package']['versions']);
    }

    /**
     * Test "packageData" method with expecting exception.
     */
    public function testPackageDataWithException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->packagist->packageData($this->vendor);
    }

    /**
     * Test "packageDataWithComposerMeta" method.
     */
    public function testPackageDataWithComposerMeta()
    {
        $package = $this->packagist->packageDataWithComposerMeta($this->vendor, $this->packageName);
        $this->assertArrayHasKey('packages', $package);
        $this->assertArrayHasKey($this->package, $package['packages']);
        $this->assertArrayHasKey('dev-master', $package['packages'][$this->package]);

        $package = $this->packagist->packageDataWithComposerMeta($this->package);
        $this->assertArrayHasKey('packages', $package);
        $this->assertArrayHasKey($this->package, $package['packages']);
        $this->assertArrayHasKey('dev-master', $package['packages'][$this->package]);
    }

    /**
     * Test "packageDataWithComposerMeta" method with expecting exception.
     */
    public function testPackageDataWithComposerMetaWithException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->packagist->packageDataWithComposerMeta($this->vendor);
    }
}
