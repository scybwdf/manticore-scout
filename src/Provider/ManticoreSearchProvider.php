<?php

namespace Scybwdf\ManticoreScout\Provider;

use Scybwdf\ManticoreScout\Engine\ManticoreSearchEngine;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Scout\Engine\Engine;
use Hyperf\Scout\Provider\ProviderInterface;


class ManticoreSearchProvider implements ProviderInterface
{
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function make(string $name): Engine
    {
        $config = $this->container->get(ConfigInterface::class);
        $config=$config->get("scout.engine.{$name}");
        $client=$this->container->make(\Manticoresearch\Client::class,[$config]);

        return new ManticoreSearchEngine($client,$config);
    }
}