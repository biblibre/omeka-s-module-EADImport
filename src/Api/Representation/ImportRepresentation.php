<?php

namespace EADImport\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class ImportRepresentation extends AbstractEntityRepresentation
{
    public function getControllerName()
    {
        return 'import';
    }

    public function getJsonLd()
    {
        return [
            'o:job' => $this->job()->getReference(),
            'o:sites' => $this->site()->getReference(),
            'name' => $this->name(),
            'mapping' => $this->mapping(),
        ];
    }

    public function getJsonLdType()
    {
        return 'o:EADimportImport';
    }

    public function job()
    {
        return $this->getAdapter('jobs')
            ->getRepresentation($this->resource->getJob());
    }

    public function site()
    {
        return $this->getAdapter('sites')
            ->getRepresentation($this->resource->getSite());
    }

    public function resourcesCount($jobId)
    {
        $entityAdapter = $this->getAdapter('eadimport_entities');

        $counts = $entityAdapter->countByResourceType($jobId);

        return $counts;
    }

    public function name()
    {
        return $this->resource->getName();
    }

    public function mapping()
    {
        return $this->resource->getMapping();
    }

    public function adminUrl($action = null, $canonical = false)
    {
        $url = $this->getViewHelper('Url');
        return $url(
            'admin/eadimport/mapping',
            [
                'controller' => $this->getControllerName(),
                'action' => $action,
                'id' => $this->id(),
            ],
            ['force_canonical' => $canonical]
        );
    }
}
