<?php


namespace Meteor\Process;


class MemoryLimitSetterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var int
     */
    private $memoryLimitValue = 666;

    /**
     * @var MemoryLimitSetter
     */
    private $memoryLimitSetter;

    public function setUp()
    {
        $this->memoryLimitSetter = new MemoryLimitSetter();
    }

    /**
     * @dataProvider phpScriptDataProvider
     * @param string $script
     * @param boolean $isPHP
     */
    public function testIsPHPScriptReturnsCorrectResult($script, $isPHP)
    {
        self::assertEquals(
            $isPHP, $this->memoryLimitSetter->isPHPScript($script),
            sprintf('Expected "%s" to %s a PHP script', $script, $isPHP ? 'be' : 'not be')
        );
    }

    /**
     * @return array
     */
    public function phpScriptDataProvider()
    {
        return [
            ['php script.php', true],
            [' php script.php', true],
            ['python thing.py', false],
            ['notphp script.php', false],
            ['/usr/bin/php foo.php', false],
        ];
    }

    /**
     * @param string $script
     * @param boolean $hasLimit
     * @dataProvider memoryLimitDataProvider
     */
    public function testHasMemoryLimitShouldReturnCorrectResult($script, $hasLimit)
    {
        self::assertEquals(
            $hasLimit, $this->memoryLimitSetter->hasMemoryLimit($script),
            sprintf('Expected "%s" to %s a memory_limit flag', $script, $hasLimit ? 'have' : 'not have')
        );
    }

    /**
     * @rerturn array
     */
    public function memoryLimitDataProvider()
    {
        $units = ['K', 'M', 'G'];
        $variants = ['
            -d memory_limit',
            '-dmemory_limit',
            '--define memory_limit',
            '--definememory_limit'
        ];
        $arguments = [];

        foreach ($units as $unit) {
            foreach ($variants as $variant) {
                // uppercase unit & suffix
                $arguments[] = [$this->getMemoryLimitCommands('php', $unit, $variant, ' --define error_reporting=-1'), true];
                // lowercase unit
                $arguments[] = [$this->getMemoryLimitCommands('php', strtolower($unit), $variant), true];
                // -1 value
                $arguments[] = [$this->getMemoryLimitCommands('php', '', $variant, '', -1), true];
                // preceding arguments
                $arguments[] = [$this->getMemoryLimitCommands('php -d error_reporting=-1', $unit, $variant), true];
            }
        }

        $arguments[] = ['php check_memory_limit.php memory_limit:100', false];
        $arguments[] = ['php -d error_reporting=-1 check_memory_limit.php', false];

        return $arguments;
    }

    /**
     * @param string $prefix
     * @param string $unit
     * @param string $variant
     * @param string $suffix
     * @param null|number $value
     * @return string
     */
    private function getMemoryLimitCommands($prefix, $unit, $variant, $suffix = '', $value = null)
    {
        return sprintf('%s %s=%d%s%s)', $prefix, $variant, $value === null ? rand(1, 5000) : $value, $unit, $suffix);
    }

    public function testSetMemoryLimitUsesIniValue()
    {
        $restore = ini_get('memory_limit');
        $input = 'php cli.php cache:warmup --kernel=frontend';
        $expected = 'php --define memory_limit=666G cli.php cache:warmup --kernel=frontend';

        ini_set('memory_limit', '666G');

        self::assertEquals(
            $expected,
            $this->memoryLimitSetter->setMemoryLimit($input));

        ini_set('memory_limit', $restore);
    }

    public function testSetMemoryLimitIsSafeForNonPHPScripts()
    {
        $input = '/bin/bash foo.bar';

        self::assertEquals($input, $this->memoryLimitSetter->setMemoryLimit($input));
    }
}
