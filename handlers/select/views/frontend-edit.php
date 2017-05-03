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

?>
<?php if ($multiple): ?>
    <div class="form-group field-<?= \yii\bootstrap\Html::getInputId($model, $property_key) ?>">
        <?= \yii\helpers\Html::activeLabel($model, $property_key, ['class' => 'col-md-2 control-label']); ?>
        <div class="col-md-10">
            <?= \yii\helpers\Html::hiddenInput(\yii\helpers\Html::getInputName($model, $property_key), '') ?>
            <?=
            kartik\widgets\Select2::widget(
                [
                    'name' => \yii\helpers\Html::getInputName($model, $property_key),
                    'data' => common\components\properties\models\PropertyStaticValue::getSelectForPropertyId($property_id),
                    'options' => [
                        'multiple' => true,
                    ],
                    'value' => explode(', ', $model->$property_key),
                ]
            )
            ?>
        </div>
    </div>
<?php else: ?>
    <?=
    $form
        ->field($model, $property_key)
        ->dropDownList(
            ['' => Yii::t('core', 'Not selected')] + common\components\properties\models\PropertyStaticValue::getSelectForPropertyId($property_id)
        );
    ?>
<?php endif; ?>
