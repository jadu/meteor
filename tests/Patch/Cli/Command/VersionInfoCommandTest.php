<?php

namespace Meteor\Patch\Cli\Command;

use Meteor\Cli\Command\CommandTestCase;
use Meteor\IO\NullIO;
use Meteor\Patch\Task\DisplayVersionInfo;
use Mockery;
use org\bovigo\vfs\vfsStream;

class VersionInfoCommandTest extends CommandTestCase
{
    private $platform;
    private $taskBus;

    public function createCommand()
    {
        vfsStream::setup('root', null, [
            'patch' => [],
            'install' => [],
        ]);

        $this->platform = Mockery::mock('Meteor\Platform\PlatformInterface');
        $this->taskBus = Mockery::mock('Meteor\Patch\Task\TaskBusInterface');

        return new VersionInfoCommand(null, ['name' => 'test'], new NullIO(), $this->platform, $this->taskBus);
    }

    public function testRunsDisplayVersionInfoTask()
    {
        $that = $this;
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $this->taskBus->shouldReceive('run')
            ->with(
                Mockery::on(function (DisplayVersionInfo $task) use ($that, $workingDir, $installDir) {
                    $that->assertSame($workingDir.'/to_patch', $task->workingDir);
                    $that->assertSame($installDir, $task->installDir);

                    return true;
                }),
                ['name' => 'test']
            )
            ->once();

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);
    }
}
