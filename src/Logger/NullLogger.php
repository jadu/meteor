<?php

namespace Meteor\Logger;

class NullLogger implements LoggerInterface
{
    /**
     * {@inheritdoc}
     */
    public function enable($path)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function disable()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function log($messages)
    {
    }
}
