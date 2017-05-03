<?php

/**
 * @var $attribute_name string
 * @var $form \yii\widgets\ActiveForm
 * @var $label string
 * @var $model \common\components\properties\AbstractModel
 * @var $multiple boolean
 * @var $property_id integer
 * @var $property_key string
 * @var $this \yii\web\View
 * @var $values array
 */

$js = <<<JS
jQuery('body').on('click', '[data-action="add-new-static-value"]', function() {
    var \$this= jQuery(this);
    var value = encodeURI(\$this.parents('.select2-container').eq(0).find("input.select2-search__field").val());
    var \$modal = jQuery('#newStaticValue');
    jQuery.ajax({
        'url': \$this.attr('href') + '&value=' + value,
        'success': function(data) {
            \$modal.find('.modal-content').html(data);
            \$modal.modal('show');
        }
    });
    console.log(value);
    return false;
});
JS;
$this->registerJs($js, \yii\web\View::POS_READY, 'select2-add-static-value');

?>

<?php
    // little style fix
    if (!$multiple) {
        echo '<style>.field-' . \yii\bootstrap\Html::getInputId($model, $property_key) . ' .select2-container .select2-choice .select2-arrow b {background: none;}</style>';
    }
?>

<div class="field-<?= \yii\bootstrap\Html::getInputId($model, $property_key) ?>">
    <?php
    if ($multiple):
        ?>
        <?= \yii\helpers\Html::hiddenInput(\yii\helpers\Html::getInputName($model, $property_key), '') ?>
    <?php
    endif;
    ?>
    <?= ''//\yii\helpers\Html::activeLabel($model, $property_key, ['class' => 'control-label']); ?>
    <div class="">
        <?php
        $addUrl = \yii\helpers\Url::to(
            [
                '/property/add-static-value',
                'key' => $property_key,
                'objectId' => !is_null($model->getOwnerModel()) ? $model->getOwnerModel()->getEntityField('id') : null,
                'objectModelId' => !is_null($model->getOwnerModel()) ? $model->getOwnerModel()->id : null,
                'returnUrl' => Yii::$app->request->url
            ]
        );
        ?>
        <?php //$propFieldName = \yii\helpers\Html::getInputName($model, $property_key); ?>
           <?php
    if ($multiple):
        ?>
        <?=
        $form->field($model, $property_key, [
            //'template' => '<div class="col-sm-3 text-right">{label}</div><div class="col-sm-6">{input}{error}{hint}</div>',
            'template' => '{label}<div class="col-sm-6">{input}{error}{hint}</div>'."<div class='col-md-3 control-label' style='text-align: left'>$label_admin</div>",
            
        ])
        ->checkboxList(common\components\properties\models\PropertyStaticValue::getSelectForPropertyId($property_id)
        ) ?>

     <?php
     
    else:
    ?>
        <?
        $arr =  common\components\properties\models\PropertyStaticValue::getSelectForPropertyId($property_id);
        $list = $required > 0?
        $arr:['' => ''] + $arr;
        echo $form->field($model, $property_key, [
            //'template' => '<div class="col-sm-3 text-right">{label}</div><div class="col-sm-6">{input}{error}{hint}</div>',
            'template' => '{label}<div class="col-sm-6">{input}{error}{hint}</div>'."<div class='col-md-3 control-label' style='text-align: left'>$label_admin</div>",
            
        ])
        ->dropDownList($list) ?>
    <?php
    endif;
    ?>

        <?= ''
//            kartik\widgets\Select2::widget(
//                [
//                    'name' => \yii\helpers\Html::getInputName($model, $property_key),
//                    'data' => ['' => ''] + common\components\properties\models\PropertyStaticValue::getSelectForPropertyId($property_id),
//                    'options' => [
//                        'multiple' => $multiple ? true : false,
//                    ],
//                    'pluginOptions' => [
//                        'allowClear' => false,
//                        'escapeMarkup' => new \yii\web\JsExpression('function (markup) {return markup;}'),
//                        'language' =>  new \yii\web\JsExpression(
//                            '{
//                                noResults: function() {
//                                   return "<a href=\'' . $addUrl . '\' data-action=\'add-new-static-value\'>'
//                            . Yii::t('core', 'Add')
//                            .'</a>"
//                                }
//                             }'
//                        ),
//                    ],
//                    'value' => is_array($model->$property_key)
//                        ? $model->$property_key
//                        : explode(', ', $model->$property_key),
//                ]
//            )
        ?>
    </div>
</div>
