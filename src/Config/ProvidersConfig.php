<?php

namespace App\Config;

use App\Model\Provider;
use Doctrine\Common\Collections\ArrayCollection;

class ProvidersConfig
{
    const KEY_PROVIDERS = 'providers';
    const KEY_REGEX = 'regex';
    const KEY_MATCH = 'match';
    const KEY_FIELDS = 'fields';
    const KEY_ENTITY = 'entity';

    /**
     * @var ArrayCollection|Provider[]
     */
    protected $providers = [];

    public function __construct(array $config = [])
    {
        $this->providers = new ArrayCollection();
        foreach ($config as $name => $data) {
            $provider = new Provider(
                $name,
                $data[self::KEY_ENTITY],
                $data[self::KEY_REGEX],
                $data[self::KEY_MATCH],
                $data[self::KEY_FIELDS]
            );
            $this->providers->set($provider->getName(), $provider);
        }
    }

    public function getProviderNames()
    {
        return $this->providers->getKeys();
    }

    /**
     * @param $name
     * @return Provider
     */
    public function getProvider($name)
    {
        if (!$this->providers->containsKey($name)) {
            throw new \InvalidArgumentException(sprintf('Invalid provder name "%s"', $name));
        }

        return $this->providers->get($name);
    }
}
