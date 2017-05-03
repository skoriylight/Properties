<?php

/**
 * @var $attribute_name string
 * @var $form \yii\widgets\ActiveForm
 * @var $label string
 * @var $model \common\components\properties\AbstractModel
 * @var $multiple boolean
 * @var $property_id integer
 * @var $property_key string
 * @var $this \common\components\properties\handlers\Handler
 * @var $values array
 */

use kartik\widgets\DatePicker;

echo $form->field($model, $property_key)->widget(
    DatePicker::classname(),
    [
        'pluginOptions' => [
            'autoclose' => true
        ]
    ]
);


