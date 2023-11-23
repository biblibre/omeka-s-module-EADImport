<?php
namespace EADImport\Service\Form;

use EADImport\Form\MappingModelEditForm;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class MappingModelEditFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $form = new MappingModelEditForm(null, $options);
        return $form;
    }
}
