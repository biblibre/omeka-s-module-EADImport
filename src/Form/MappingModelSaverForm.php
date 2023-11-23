<?php

namespace EADImport\Form;

use Laminas\Form\Form;

class MappingModelSaverForm extends Form
{
    public function init()
    {
        $importName = $this->getOption('import_name');
        $importMapping = $this->getOption('import_mapping');
        $this->setAttribute('action', 'mapping-model/save');

        $this->add([
            'name' => 'model_name',
            'type' => 'text',
            'options' => [
                'label' => 'Mapping model name', //@translate
            ],
            'attributes' => [
                'required' => true,
                'value' => $importName,
            ],
        ]);

        $this->add([
            'name' => 'mapping',
            'type' => 'hidden',
            'attributes' => [
                'required' => true,
                'value' => $importMapping,
            ],
        ]);
    }
}
