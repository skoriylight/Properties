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
use yii\helpers\Url;

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
            if ($model->getOwnerModel()->className() === \app\modules\shop\models\Order::className())
            {
                echo Html::tag('dd', Html::a($val['value'], 'http://'. Yii::$app->getModule('core')->getBaseUrl() . Url::to([
                    '/shop/backend-order/download-file',
                    'key' => $val['key'],
                    'orderId' => $values->entity_model_id
                ])));
            } else {
                echo Html::tag('dd', Html::a($val['value'], 'http://'. Yii::$app->getModule('core')->getBaseUrl() . Url::to([
                    '/backend/form/download',
                    'key' => $val['key'],
                    'submissionId' => $values->entity_model_id
                ])));
            }
        }

    }

    ?>
</dl>