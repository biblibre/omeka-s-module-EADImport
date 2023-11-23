<?php
namespace EADImport\Service\Form;

use EADImport\Form\MappingModelSaverForm;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class MappingModelSaverFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $form = new MappingModelSaverForm(null, $options);
        return $form;
    }
}
