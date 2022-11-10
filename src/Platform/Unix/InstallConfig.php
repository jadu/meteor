<?php

namespace Meteor\Platform\Unix;

class InstallConfig
{
    /**
     * @var array
     */
    private $values;

    /**
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    private function get($name)
    {
        return $this->values[$name] ?? null;
    }

    /**
     * @return bool
     */
    public function isSuexec()
    {
        return trim($this->get('SUEXEC')) === 'yes';
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->get('JADU_SYSTEM_USER');
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->get('JADU_SYSTEM_GROUP');
    }

    /**
     * @return string
     */
    public function getWebUser()
    {
        if ($this->isSuexec()) {
            return $this->get('JADU_SUEXEC_USER');
        }

        return $this->get('APACHE_USER');
    }

    /**
     * @return string
     */
    public function getWebGroup()
    {
        if ($this->isSuexec()) {
            return $this->get('JADU_SUEXEC_GROUP');
        }

        return $this->get('APACHE_GROUP');
    }
}
