<?php

namespace common\components\properties\handlers;

use common\components\properties\models\AbstractModel;
use yii\base\Widget;

class AbstractHandlerWidget extends Widget
{
    public $multiple = false;
    public $required = 0;
    public $values = [];
    public $form;
    /** @var AbstractModel $model */
    public $model;
    public $property_key;
    public $property_id;
    public $label = '';
    public $label_admin = '';
    public $viewFile = '';
    public $additional = [];

    public function run()
    {
        return $this->render(
            $this->viewFile,
            [
                'values' => $this->values,
                'form' => $this->form,
                'model' => $this->model,
                'attribute_name' => $this->attributeName(),
                'label' => $this->label,
                'label_admin' => $this->label_admin,
                'property_key' => $this->property_key,
                'property_id' => $this->property_id,
                'multiple' => $this->multiple,
                'additional' => $this->additional,
                'required' => $this->required,
            ]
        );
    }

    public function attributeName()
    {
        return 'props['.$this->property_key.'][]';
    }
}
?>