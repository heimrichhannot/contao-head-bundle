<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Twig\Runtime;

use HeimrichHannot\HeadBundle\Manager\JsonLdManager;
use Spatie\SchemaOrg\Graph;
use Twig\Extension\RuntimeExtensionInterface;

class SchemaOrgRuntime implements RuntimeExtensionInterface
{
    private JsonLdManager $jsonLdManager;

    public function __construct(JsonLdManager $jsonLdManager)
    {
        $this->jsonLdManager = $jsonLdManager;
    }

    public function add(array $jsonLd): void
    {
        $type = $this->jsonLdManager->createSchemaOrgTypeFromArray($jsonLd);

        $this->jsonLdManager
            ->getGraphForSchema(JsonLdManager::SCHEMA_ORG)
            ->set($type, $jsonLd['identifier'] ?? Graph::IDENTIFIER_DEFAULT)
        ;
    }
}
