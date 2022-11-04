<?php

namespace Meteor\Package\Provider\BasicHttp;

use Exception;
use GuzzleHttp\Client;
use Meteor\IO\IOInterface;
use Meteor\Package\Provider\Exception\PackageNotFoundException;
use Meteor\Package\Provider\PackageProviderInterface;

class BasicHttpPackageProvider implements PackageProviderInterface
{
    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var array
     */
    private $packageBaseUrls;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * BasicHttpPackageProvider constructor.
     *
     * @param IOInterface $io
     * @param array $packageBaseUrls
     * @param Client $httpClient
     */
    public function __construct(IOInterface $io, array $packageBaseUrls, Client $httpClient)
    {
        $this->io = $io;
        $this->packageBaseUrls = $packageBaseUrls;
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $packageName
     * @param string $version
     * @param string $tempDir
     *
     * @return string
     *
     * @throws PackageNotFoundException
     */
    public function download($packageName, $version, $tempDir)
    {
        if (!isset($this->packageBaseUrls[$packageName])) {
            throw new PackageNotFoundException(sprintf('The HTTP base url for the "%s" package has not been configured.', $packageName));
        }
        $file = $version . '.zip';
        $baseUrl = $this->packageBaseUrls[$packageName];

        $packageUrl = $baseUrl . $file;
        try {
            $response = $this->httpClient->request('GET', $packageUrl, ['sink' => $tempDir . '/' . $file]);
            if ($response->getStatusCode() == 200 && file_exists($tempDir . '/' . $file)) {
                return $tempDir . '/' . $file;
            }
        } catch (Exception $e) {
        }
        throw new PackageNotFoundException(sprintf('Unable to download "%s" package form the package provider.', $packageName));
    }
}
