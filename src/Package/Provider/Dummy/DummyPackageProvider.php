<?php

namespace Meteor\Package\Provider\Dummy;

use Meteor\Package\Provider\Exception\PackageNotFoundException;
use Meteor\Package\Provider\PackageProviderInterface;

class DummyPackageProvider implements PackageProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function download($packageName, $version, $tempDir)
    {
        throw new PackageNotFoundException();
    }
}
