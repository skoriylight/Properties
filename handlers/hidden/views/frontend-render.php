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

    <?php
        if (count($values->values) == 0) {
            return;
        }
    ?>

    <?php
        $property = Property::findById($property_id);
        $result = "";
        foreach ($values->values as $val) {
            if (isset($val['value'])) {
                if (!empty(trim($val['value']))) {
                    $result .= Html::tag('dd', $val['value']);
                }
            }
        }
        $result = trim($result);

        if (!empty($result)) {
            echo '<dl>' . Html::tag('dt', $property->name) . $result . "</dl>\n\n";
        }
    ?>
