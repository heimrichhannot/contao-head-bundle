<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Manager;

use Contao\CoreBundle\Routing\ResponseContext\JsonLd\JsonLdManager as ContaoJsonLdManager;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Psr\Container\ContainerInterface;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Graph;
use Spatie\SchemaOrg\Type;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class JsonLdManager implements ServiceSubscriberInterface
{
    public const SCHEMA_ORG = 'https://schema.org';

    private array $graphs = [];
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

        $buffer = '';

        foreach ($this->getGraphs() as $graph) {
            foreach ($graph->getNodes() as $node) {
                /** @var BaseType $schema */
                foreach ($node as $schema) {
                    $buffer .= $schema->toScript();
                }
            }
        }

        return $buffer;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function createSchemaOrgTypeFromArray(array $jsonLd): Type
    {
        if ($this->getContaoJsLdManager()) {
            return $this->getContaoJsLdManager()->createSchemaOrgTypeFromArray($jsonLd);
        }

        if (!isset($jsonLd['@type'])) {
            throw new \InvalidArgumentException('Must provide the @type property!');
        }

        $schemaClass = '\Spatie\SchemaOrg\\' . $jsonLd['@type'];

        if (!class_exists($schemaClass)) {
            throw new \InvalidArgumentException(sprintf('Unknown schema.org type "%s" provided!', $jsonLd['@type']));
        }

        $schema = new $schemaClass();
        unset($jsonLd['@type']);

        foreach ($jsonLd as $k => $v) {
            if (\is_array($v) && isset($v['@type'])) {
                $v = $this->createSchemaOrgTypeFromArray($v);
            }

            $schema->setProperty($k, $v);
        }

        return $schema;
    }

    public static function getSubscribedServices(): array
    {
        $services = [];

        if (class_exists(ResponseContextAccessor::class)) {
            $services[] = '?' . ResponseContextAccessor::class;
        }

        return $services;
    }

    private function recursiveKeySort(array &$array): void
    {
        foreach ($array as &$value) {
            if (\is_array($value)) {
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

        if (!$this->container->has(ResponseContextAccessor::class)
            || !$this->container->get(ResponseContextAccessor::class)->getResponseContext()->has(ContaoJsonLdManager::class)) {
            return null;
        }

        return $this->container->get(ResponseContextAccessor::class)->getResponseContext()->get(ContaoJsonLdManager::class);
    }
}
