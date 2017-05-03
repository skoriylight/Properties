<?php

use common\components\properties\models\Property;
use common\components\properties\models\SpamChecker;
use common\components\properties\models\PropertyGroup;
use dosamigos\multiselect\MultiSelect;
use kartik\dynagrid\DynaGrid;
use yii\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = Yii::t('core', 'Property edit');
$this->params['breadcrumbs'][] = ['url' => ['/property/index'], 'label' => Yii::t('core', 'Property groups')];
$this->params['breadcrumbs'][] = ['url' => ['/property/group', 'id' => $model->property_group_id], 'label' => $model->group->name];
$this->params['breadcrumbs'][] = $this->title;

//if ($model->isNewRecord) {
//    $model->type = null;
//}

?>

<?= common\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $form = ActiveForm::begin(['id' => 'property-form', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>
<?=
Html::a(
    Icon::show('arrow-circle-left') . Yii::t('core', 'Back'),
    Yii::$app->request->get('returnUrl', ['/property/group', 'id' => $model->property_group_id]),
    ['class' => 'btn btn-danger']
)
?>

<?php if ($model->isNewRecord): ?>
    <?=
    Html::submitButton(
        Icon::show('save') . Yii::t('core', 'Save & Go next'),
        [
            'class' => 'btn btn-success',
            'name' => 'action',
            'value' => 'next',
        ]
    )
    ?>
<?php endif; ?>

<?=
Html::submitButton(
    Icon::show('save') . Yii::t('core', 'Save & Go back'),
    [
        'class' => 'btn btn-warning',
        'name' => 'action',
        'value' => 'back',
    ]
)
?>

<?=
Html::submitButton(
    Icon::show('save') . Yii::t('core', 'Save'),
    [
        'class' => 'btn btn-primary',
        'name' => 'action',
        'value' => 'save',
    ]
)
?>
<?php $this->endBlock('submit'); ?>
<?php
$this->beginBlock('add-button');
?>
    <a href="<?= Url::to([
        '/property/edit-static-value', 'property_id' => $model->id//, 'returnUrl' => \app\backend\components\Helper::getReturnUrl()
    ]) ?>" class="btn btn-success">
        <?= Icon::show('plus') ?>
        <?= Yii::t('core', 'Add value') ?>
    </a>
<?php
$this->endBlock();
?>

<div class="row">

    <article class="col-lg-4 col-md-12">
        <?php \backend\widgets\BackendWidget::begin([
            'title'=> Yii::t('core', 'Property'),
            'icon'=>'cogs',
            'footer'=>$this->blocks['submit']
        ]); ?>

        <?= $form->field($model, 'name_ru') ?>
        <?= $form->field($model, 'name_uk') ?>

        <?=
        $form->field(
            $model,
            'key',
            [
                //'makeKey' => [
                //    "#property-name",
                //],
                'inputOptions' => [
                    'maxlength' => '20',
                ],
            ]
        )
        ?>

        <?=
        $form->field(
            $model,
            'label',
            [
            
                'inputOptions' => [
                    'maxlength' => '20',
                ],
            ]
        )
        ?>

        <?=
        $form->field(
            $model,
            'label_admin',
            [
            
                'inputOptions' => [
                    'maxlength' => '20',
                ],
            ]
        )
        ?>

        

        <?= $form->field($model, 'property_handler_id')->dropDownList(common\components\properties\models\PropertyHandler::getSelectArray()) ?>


        



        <div class="form-group field-property-property_handler_id required has-success">
            <label class="control-label col-md-2" for="">Группы</label>
            <div class="col-md-10">
                <?= MultiSelect::widget([
                    "options" => ['multiple'=>"multiple"],
                    'data' => PropertyGroup::listAll(),
                    'name' => 'Property[group_ids]',
                    'value' => $model->group_ids
                ]) ?>
            </div>
            <div class="col-md-offset-2 col-md-10">     </div>
            <div class="col-md-offset-2 col-md-10">
                <div class="help-block"></div>
            </div>
        </div>


        <?//= $form->field($model, 'type')->dropDownList(Property::getTypes()) ?>

        <?= ''//$form->field($model, 'has_slugs_in_values')->checkbox() ?>

        <?//= $form->field($model, 'is_range')->checkbox() ?>
        <?= $form->field($model, 'multiple')->checkbox() ?>

        <?= $form->field($model, 'required_field')->checkbox() ?>

        <?= ''//$form->field($model, 'required')->checkbox() ?>

        <?= ''//$form->field($model, 'interpret_as')->dropDownList(SpamChecker::getFieldTypesForForm())  ?>

        <?= ''//$form->field($model, 'captcha')->checkbox() ?>

        <?= $form->field($model, 'position') ?>


        <?php /*if ($model->property_handler_id == 9): ?>

            <?= $form->field($model, 'mask') ?>

            <?= $form->field($model, 'alias')->dropDownList(Property::getAliases()) ?>

        <?php endif;*/ ?>

        <?php \backend\widgets\BackendWidget::end(); ?>
    </article>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
    "use strict";
    $('input[data-group]').change(function() {
        var object = jQuery(this);
        if (object.prop('checked')) {
            $('input[data-group="' + object.data('group') + '"]').not('[name="' + object.attr('name') + '"]').prop('checked', false);
        }
    });
JS;
$this->registerJs($js);
?>

<?php if ($model->type === Property::TYPE_STATIC): ?>
    <?=
    DynaGrid::widget([
        'options' => [
            'id' => 'property-grid',
            'class' => 'col-lg-8 col-md-12',
        ],
        'columns' => [
            [
                'class' => 'yii\grid\DataColumn',
                'attribute' => 'id',
            ],
            'name_ru',
            'value',
            'slug',
            [
                'class' => \backend\components\ActionColumn::className(),
                'buttons' => [
                    [
                        'url' => 'edit-static-value',
                        'icon' => 'pencil',
                        'class' => 'btn-primary',
                        'label' => 'Edit',
                    ],
                    [
                        'url' => 'delete-static-value',
                        'icon' => 'trash-o',
                        'class' => 'btn-danger',
                        'label' => 'Delete',
                    ],
                ], // /buttons
                'url_append' => '&property_id=' . $model->id . '&property_group_id=' . $model->property_group_id,
            ],
        ],

        'theme' => 'panel-default',

        'gridOptions' => [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => true,
            'export' => false,
            'panel' => [
                'heading' => '<h3 class="panel-title">' . Yii::t('core', 'Static values') . '</h3>',
                'after' => $this->blocks['add-button'],
            ],

        ]
    ]);
    ?>
    <?php
endif;
?>

</div>

