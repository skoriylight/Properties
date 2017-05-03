<?php

use yii\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = Yii::t('core', 'Property static value edit');
$this->params['breadcrumbs'][] = ['url' => ['/property/index'], 'label' => Yii::t('core', 'Property groups')];
$this->params['breadcrumbs'][] = ['url' => ['/property/group', 'id'=>$model->property->property_group_id], 'label' => $model->property->group->name];
$this->params['breadcrumbs'][] = ['url' => ['/property/edit-property', 'id'=>$model->property_id, 'property_group_id' => $model->property->property_group_id], 'label' => $model->property->name_ru];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= common\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $form = ActiveForm::begin(['id' => 'static-value-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
<?=
Html::a(
    Icon::show('arrow-circle-left') . Yii::t('core', 'Back'),
    Yii::$app->request->get('returnUrl', ['/property/edit-property', 'id' => $model->property_id, 'property_group_id' => $model->property->property_group_id]),
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
</div>
<?php $this->endBlock('submit'); ?>

<section id="widget-grid">
    <div class="row">
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php \backend\widgets\BackendWidget::begin([
                'title'=> Yii::t('core', 'Property static value'),
                'icon'=>'cogs',
                'footer'=>$this->blocks['submit']
            ]); ?>
            <?= $this->render('edit-static-value-form', ['model'=>$model, 'form'=>$form]) ?>
            <?php \backend\widgets\BackendWidget::end(); ?>
        </article>
    </div>
</section>

<?php ActiveForm::end();  ?>

