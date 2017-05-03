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

use common\components\properties\models\Property;
use yii\widgets\MaskedInput;

$result = $form->field($model, $property_key);
$property = Property::findById($property_id);
if (empty($property->mask) === false) {
    $result->widget(MaskedInput::className(), ['mask' => $property->mask]);
} elseif (is_null($property->alias) === false && $property->alias !== 0) {
    $aliases = Property::getAliases();
    $result->widget(MaskedInput::className(), ['clientOptions' => ['alias' => $aliases[$property->alias]]]);
}
echo $result;
