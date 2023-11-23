<?php
namespace EADImport\Service\Form;

use EADImport\Form\LoadForm;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LoadFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $form = new LoadForm(null, $options);
        return $form;
    }
}
