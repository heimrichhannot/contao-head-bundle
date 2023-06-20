<?php

namespace HeimrichHannot\HeadBundle\Manager;

use Contao\CoreBundle\Routing\ResponseContext\JsonLd\ContaoPageSchema as CoreContaoPageSchema;
use Contao\CoreBundle\Routing\ResponseContext\JsonLd\JsonLdManager as ContaoJsonLdManager;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use HeimrichHannot\HeadBundle\Routing\ResponseContext\JsonLd\ContaoPageSchema;
use Psr\Container\ContainerInterface;
use Spatie\SchemaOrg\Graph;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class JsonLdManager implements ServiceSubscriberInterface
{
    public const SCHEMA_ORG = 'https://schema.org';

    private array              $graphs = [];
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getGraphForSchema(string $schema): Graph
    {
        if ($this->getContaoJsLdManager()) {
            return $this->getContaoJsLdManager()->getGraphForSchema($schema);
        }

        $schema = rtrim($schema, '/');

        if (!\array_key_exists($schema, $this->graphs)) {
            $this->graphs[$schema] = new Graph($schema);
        }

        return $this->graphs[$schema];
    }

    /**
     * @return array<Graph>
     */
    public function getGraphs(): array
    {
        return $this->graphs;
    }

    public function collectFinalScriptFromGraphs(): string
    {
        if ($this->getContaoJsLdManager()) {
            return $this->getContaoJsLdManager()->collectFinalScriptFromGraphs();
        }

        $data = [];

        foreach ($this->getGraphs() as $graph) {
            $data[] = $graph->toArray();
        }

        // Reset graphs
        $this->graphs = [];

        if (0 === \count($data)) {
            return '';
        }


        $this->recursiveKeySort($data);

        return '<script type="application/ld+json">'."\n".json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n".'</script>';
    }

    private function recursiveKeySort(array &$array): void
    {
        foreach ($array as &$value)
        {
            if (\is_array($value))
            {
                self::recursiveKeySort($value);
            }
        }

        ksort($array);
    }

    private function getContaoJsLdManager()
    {
        if (!class_exists(ResponseContextAccessor::class) || !class_exists(ContaoJsonLdManager::class)) {
            return null;
        }

        if (!$this->container->has(ResponseContextAccessor::class) ||
            !$this->container->get(ResponseContextAccessor::class)->has(ContaoJsonLdManager::class)) {
            return null;
        }

        return $this->container->get(ResponseContextAccessor::class)->get(ContaoJsonLdManager::class);
    }

    public static function getSubscribedServices(): array
    {
        $services = [];
        if (class_exists(ResponseContextAccessor::class)) {
            $services[] = '?'.ResponseContextAccessor::class;
        }
        return $services;
    }
}