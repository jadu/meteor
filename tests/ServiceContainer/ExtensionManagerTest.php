<?php

namespace Meteor\ServiceContainer;

use Meteor\ServiceContainer\Exception\ExtensionInitializationException;
use Meteor\ServiceContainer\Test\TestExtension;
use Mockery;
use PHPUnit\Framework\TestCase;

class ExtensionManagerTest extends TestCase
{
    private $extensionManager;

    protected function setUp(): void
    {
        $this->extensionManager = new ExtensionManager([]);
    }

    public function testActivateExtensionWithClass()
    {
        $this->extensionManager->activateExtension('Meteor\ServiceContainer\Test\TestExtension', null);

        $this->assertSame(
            ['Meteor\ServiceContainer\Test\TestExtension'],
            $this->extensionManager->getExtensionClasses()
        );
    }

    public function testActivateExtensionWithAbsolutePathFile()
    {
        $this->extensionManager->activateExtension(__DIR__ . '/Fixtures/absolute_extension.php', null);

        $this->assertSame(
            ['Meteor\ServiceContainer\Test\TestAbsoluteFileExtension'],
            $this->extensionManager->getExtensionClasses()
        );
    }

    public function testActivateExtensionWithRelativePathFile()
    {
        $this->extensionManager->activateExtension('relative_extension.php', __DIR__ . '/Fixtures');

        $this->assertSame(
            ['Meteor\ServiceContainer\Test\TestRelativeFileExtension'],
            $this->extensionManager->getExtensionClasses()
        );
    }

    public function testActivateExtensionThrowsExceptionWhenClassNotFound()
    {
        static::expectException(ExtensionInitializationException::class);

        $this->extensionManager->activateExtension('ThisClassDoesNotExist', null);
    }

    public function testActivateExtensionThrowsExceptionWhenNotImplementingExceptionInterface()
    {
        static::expectException(ExtensionInitializationException::class);

        $this->extensionManager->activateExtension('DateTime', null);
    }

    public function testGetExtension()
    {
        $extension = new TestExtension();
        $extensionManager = new ExtensionManager([$extension]);

        $this->assertSame($extension, $extensionManager->getExtension('test'));
    }

    public function testGetExtensionReturnsNullWhenNotFound()
    {
        $this->assertNull($this->extensionManager->getExtension('invalid'));
    }

    public function testGetExtensions()
    {
        $extension = new TestExtension();
        $extensionManager = new ExtensionManager([$extension]);

        $this->assertSame(['test' => $extension], $extensionManager->getExtensions());
    }

    public function testGetExtensionClasses()
    {
        $extension = new TestExtension();
        $extensionManager = new ExtensionManager([$extension]);

        $this->assertSame(
            ['Meteor\ServiceContainer\Test\TestExtension'],
            $extensionManager->getExtensionClasses()
        );
    }

    public function testInitializeExtensions()
    {
        $extension = Mockery::mock('Meteor\ServiceContainer\ExtensionInterface', [
            'getConfigKey' => 'test',
        ]);
        $extensionManager = new ExtensionManager([$extension]);

        $extension->shouldReceive('initialize')
            ->with($extensionManager)
            ->once();

        $extensionManager->initializeExtensions();
    }
}
