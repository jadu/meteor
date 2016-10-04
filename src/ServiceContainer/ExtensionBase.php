<?php

namespace Meteor\ServiceContainer;

abstract class ExtensionBase
{
    /**
     * {@inheritdoc}
     */
    public function configParse(array $config)
    {
        $extensionConfig = array();
        $extensionConfigKey = $this->getConfigKey();

        if (isset($config[$extensionConfigKey])) {
            $extensionConfig = $config[$extensionConfigKey];
        }

        return $extensionConfig;
    }
}
