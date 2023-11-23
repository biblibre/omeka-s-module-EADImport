<?php

namespace EADImport\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\Radio;
use Omeka\Form\Element\SiteSelect;

class LoadForm extends Form
{
    public function init()
    {
        $this->setAttribute('action', 'eadimport/map');

        $this->add([
            'name' => 'import_name',
            'type' => 'text',
            'options' => [
                'label' => 'Import name', //@translate
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'source',
            'type' => 'file',
            'options' => [
                'label' => 'File path', //@translate
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);
        $this->add([
            'name' => 'schema',
            'type' => Radio::class,
            'options' => [
                'label' => 'Check structure with schema', //@translate
                'info' => 'Validation with XSD formatted schema', //@translate
                'value_options' => [
                    'ead2002.xsd' => 'EAD 2002',
                    [
                        'value' => 'None',
                        'label' => 'None', //@translate
                        'selected' => true,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'site_id',
            'type' => SiteSelect::class,
            'options' => [
                'label' => 'Site', // @translate
                'info' => 'Select which site to import datas',
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);
    }
}
