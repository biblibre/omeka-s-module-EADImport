<?php

namespace EADImport\Form;

use Laminas\Form\Form;

class MappingModelEditForm extends Form
{
    public function init()
    {
        $mappingModel = $this->getOption('mappingModel');
        $this->setAttribute('action', $mappingModel->url('edit'));

        $this->add([
            'name' => 'model_name',
            'type' => 'text',
            'options' => [
                'label' => 'Mapping model name', //@translate
            ],
            'attributes' => [
                'required' => true,
                'value' => $mappingModel->name(),
            ],
        ]);
    }
}
