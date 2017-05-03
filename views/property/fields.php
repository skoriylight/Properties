<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 */

use kartik\dynagrid\DynaGrid;
use kartik\grid\BooleanColumn;
use yii\bootstrap\Html;
use kartik\icons\Icon;
use common\components\Entity;

$this->title = Yii::t('core', 'Property groups');
$this->params['breadcrumbs'][] = $this->title;

?>

<?= \common\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?=
    DynaGrid::widget([
        'options' => [
            'id' => 'properties-grid',
        ],
        'columns' => [
            [
                'class' => \kartik\grid\CheckboxColumn::className(),
                'options' => [
                    'width' => '10px',
                ],
            ],
            [
                'class' => 'yii\grid\DataColumn',
                'attribute' => 'id',
            ],
            
            'name_ru',
            'label_admin',
            [
                'class' => \backend\components\ActionColumn::className(),
                'options' => [
                    'width' => '95px',
                ],
                'buttons' => [
                    [
                        'url' => 'edit-property',
                        'icon' => 'pencil',
                        'class' => 'btn-primary',
                        'label' => Yii::t('core', 'Edit'),
                    ],
                    [
                        'url' => 'delete-group',
                        'icon' => 'trash-o',
                        'class' => 'btn-danger',
                        'label' => Yii::t('core', 'Delete'),
                        'options' => [
                            'data-action' => 'delete',
                        ],
                    ],
                ],
            ],
        ],
        'theme' => 'panel-default',
        'gridOptions'=>[
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => true,
            'export' => false,
            'panel' => [
                'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
                'after' => Html::a(
                        Icon::show('plus') . Yii::t('core', 'Add'),
                        ['property/group', 'returnUrl' => \backend\components\Helper::getReturnUrl()],
                        ['class' => 'btn btn-success']
                    ) . \backend\widgets\RemoveAllButton::widget([
                        'url' => 'property/remove-all-groups',
                        'gridSelector' => '.grid-view',
                        'htmlOptions' => [
                            'class' => 'btn btn-danger pull-right'
                        ],
                    ]),
            ],
            
        ]
    ]);
?>
