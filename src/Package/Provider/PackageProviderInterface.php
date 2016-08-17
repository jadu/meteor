<?php

namespace Meteor\Package\Provider;

interface PackageProviderInterface
{
    /**
     * @param string $packageName
     * @param string $version
     * @param string $tempDir
     *
     * @return string
     */
    public function download($packageName, $version, $tempDir);
}
