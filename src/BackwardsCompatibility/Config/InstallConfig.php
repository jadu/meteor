<?php

namespace Jadu\Meteor\Config;

/**
 * This class is from the original version of Meteor. Some migrations use it so it needs to be
 * available to them for backwards compatibility. No new migrations should be using this class.
 *
 * @deprecated Use Meteor\Platform\Unix\InstallConfig instead
 */
class InstallConfig
{
    protected $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    protected function get($name)
    {
        return $this->values[$name] ?? null;
    }

    public function isSuexec()
    {
        return trim($this->get('SUEXEC')) === 'yes';
    }

    public function getUser()
    {
        return $this->get('JADU_SYSTEM_USER');
    }

    public function getGroup()
    {
        return $this->get('JADU_SYSTEM_GROUP');
    }

    public function getWebUser()
    {
        if ($this->isSuexec()) {
            return $this->get('JADU_SUEXEC_USER');
        }

        return $this->get('APACHE_USER');
    }

    public function getWebGroup()
    {
        if ($this->isSuexec()) {
            return $this->get('JADU_SUEXEC_GROUP');
        }

        return $this->get('APACHE_GROUP');
    }
}
