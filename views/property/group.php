<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Form $searchModel
 */

//use app\backend\components\ActionColumn;
//use app\backend\widgets\BackendWidget;
use kartik\dynagrid\DynaGrid;
use yii\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;

$this->title = Yii::t('core', 'Update');
$this->params['breadcrumbs'][] = ['url' => ['/property/index'], 'label' => Yii::t('core', 'Properties')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= common\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $this->beginBlock('submit'); ?>
<?=
Html::a(
    Icon::show('arrow-circle-left') . Yii::t('core', 'Back'),
    Yii::$app->request->get('returnUrl', ['/property/index']),
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

<?php $form = ActiveForm::begin(['id' => 'property-group-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>
    <section id="widget-grid">
        <div class="row">
            <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <?php \backend\widgets\BackendWidget::begin([
                    'title' => Yii::t('core', 'Property groups'),
                    'icon' => 'list',
                    'footer' => $this->blocks['submit'],
                ]); ?>
                    <?= $form->field($model, 'name') ?>
                    <?= $form->field($model, 'entity_id')->dropDownList(Yii::$app->entity->dropDown) ?>
                    <?php //$model->object_id = 3; ?>
                    <?= ''//$form->field($model, 'entity_id')->hiddenInput()->label(false) ?>

                    <?= ''//$form->field($model, 'is_internal')->checkbox() ?>
                    <?= ''//$form->field($model, 'hidden_group_title')->checkbox() ?>
                    <?= $form->field($model, 'position') ?>

                    <?= ''//$this->blocks['submit'] ?>
                <?php \backend\widgets\BackendWidget::end(); ?>
            </article>
        </div>
    </section>
<?php ActiveForm::end(); ?>
<?php if (!$model->isNewRecord): ?>
    <?=
        DynaGrid::widget(
            [
                'options' => [
                    'id' => 'group-grid',
                ],
                'columns' => [
                    [
                        'class' => \kartik\grid\CheckboxColumn::className(),
                        'options' => [
                            'width' => '10px',
                        ],
                    ],
                    [
                        'class' => \kartik\grid\DataColumn::className(),
                        'attribute' => 'id',
                    ],
                    [
                        'attribute' => 'property_handler_id',
                        'filter' => common\components\properties\models\PropertyHandler::getSelectArray(),
                        'value' => function ($model, $key, $index, $widget) {
                            $array = common\components\properties\models\PropertyHandler::getSelectArray();
                            return $array[$model->property_handler_id];
                        },
                    ],
                    'name_ru',
                    'key',
                    [
                        'attribute' => 'type',
                        'filter' => \common\components\properties\models\Property::getTypes(),
                        'value' => function ($model, $key, $index, $widget) {
                            return $model === null ? null : $model->typeCaption;
                        },
                    ],
//                    [
//                        'class' => \kartik\grid\BooleanColumn::className(),
//                        'attribute' => 'has_slugs_in_values',
//                    ],
                    [
                        'class' => \kartik\grid\BooleanColumn::className(),
                        'attribute' => 'is_range',
                    ],
                    [
                        'class' => \kartik\grid\BooleanColumn::className(),
                        'attribute' => 'multiple',
                    ],
                    [
                        'class' => \backend\components\ActionColumn::className(),
                        'buttons' => [
                            [
                                'url' => 'edit-property',
                                'icon' => 'pencil',
                                'class' => 'btn-primary',
                                'label' => 'Edit',
                            ],
                            [
                                'url' => 'delete-property',
                                'icon' => 'trash-o',
                                'class' => 'btn-danger',
                                'label' => 'Delete',
                                'options' => [
                                    'data-action' => 'delete',
                                ],
                            ],
                        ],
                        'url_append' => '&property_group_id=' . $model->id,
                    ],
                ],
                'theme' => 'panel-default',
                'gridOptions'=>[
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'hover' => true,
                    'export' => false,
                    'panel' => [
                        'heading' => Html::tag('h3', Yii::t('core', 'Properties'), ['class' => 'panel-title']),
                        'after' => Html::a(
                                Icon::show('plus') . Yii::t('core', 'Add'),
                                ['property/edit-property', 'property_group_id' => $model->id, 'returnUrl' => \backend\components\Helper::getReturnUrl()],
                                ['class' => 'btn btn-success']
                            ) . \backend\widgets\RemoveAllButton::widget([
                                'url' => \yii\helpers\Url::to(['property/remove-all-properties', 'group_id' => $model->id]),
                                'gridSelector' => '.grid-view',
                                'htmlOptions' => [
                                    'class' => 'btn btn-danger pull-right'
                                ],
                            ]),
                    ],
                ],
            ]
        );
    ?>
<?php endif; ?>
