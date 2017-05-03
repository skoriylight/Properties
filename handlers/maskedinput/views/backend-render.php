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
use yii\bootstrap\Html;

?>
<dl>
    <?php
        if (count($values->values) == 0) {
            return;
        }
        $property = Property::findById($property_id);
        echo Html::tag('dt', $property->name);
        foreach ($values->values as $val) {
            if (isset($val['value'])) {
                echo Html::tag('dd', $val['value']);
            }
        }
    ?>
</dl>