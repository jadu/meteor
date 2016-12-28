<?php

namespace Meteor\Patch\Lock;

use RuntimeException;

class Locker
{
    const FILENAME = 'meteor.lock';

    /**
     * @param string $path
     */
    public function lock($path)
    {
        $lockPath = $path . '/' . self::FILENAME;
        if (!file_exists($lockPath)) {
            $lockFile = fopen($lockPath, 'w+');
            if (flock($lockFile, LOCK_EX)) {
                fclose($lockFile);

                return;
            }

            fclose($lockFile);
        }

        throw new RuntimeException('Unable to create lock file. This may be due to failure during a previous attempt to apply this package.');
    }

    /**
     * @param string $path
     */
    public function unlock($path)
    {
        $lockPath = $path . '/' . self::FILENAME;
        if (!file_exists($lockPath)) {
            return false;
        }

        $lockFile = fopen($lockPath, 'w+');
        flock($lockFile, LOCK_UN);

        fclose($lockFile);
        unlink($lockPath);

        return true;
    }
}
