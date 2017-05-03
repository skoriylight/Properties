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

use yii\bootstrap\Html;

?>
<dl>
    <?= Html::tag('dt', $model->getAttributeLabel($property_key)) ?>
    <?= Html::tag('dd', Yii::t('core', $model->$property_key == 1 ? 'Yes' : 'No')) ?>
</dl>