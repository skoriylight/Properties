<?php

use yii\bootstrap\ActiveForm;

?>

<div style="padding:15px;">
<?php $form = ActiveForm::begin([
    'action' => Yii::$app->request->url,
    'id' => 'static-value-form',
    'type' => ActiveForm::TYPE_HORIZONTAL
]); ?>

<?= $this->render('edit-static-value-form', ['form'=>$form, 'model'=>$model]) ?>

<?= \yii\bootstrap\Html::submitButton(Yii::t('core', 'Save'), ['class'=> 'btn btn-info']) ?>

<?php ActiveForm::end(); ?>
</div>


