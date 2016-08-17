<?php

namespace Meteor\Logger;

interface LoggerInterface
{
    /**
     * Enable the logger.
     *
     * @param string $path
     */
    public function enable($path);

    /**
     * Disable the logger.
     */
    public function disable();

    /**
     * Log a message.
     *
     * @param array|string $messages
     */
    public function log($messages);
}
