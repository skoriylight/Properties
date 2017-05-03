<?php
use yii\helpers\Html;
use common\components\properties\behaviors\HasProperties;

        $items = [];
        $new_groups = [];
        foreach ($entity_property_groups as $i => $opg) {
            $items[] = [
                'label' => $opg->group->name . ' ' . Html::tag(
                        'i',
                        '',
                        [
                            'class' => 'fa fa-times remove-property-group',
                            'data-pg' => json_encode(
                                [
                                    'id' => $opg->group->id,
                                    'form_name' => $model->formName(),
                                ]
                            )
                        ]
                    ),
                'url' => '#pg-' . $opg->group->id,
                'active' => ($i == 0),
                'linkOptions' => [
                    'data-toggle' => 'tab',
                ],
                'encode' => false,
            ];
        }

        foreach ($property_groups_to_add as $id => $name) {
            $new_groups[] = [
                'label' => $name,
                'url' => '#',
                'linkOptions' => [
                    'class' => 'add-property-group',
                    'data-pg' => json_encode(
                        [
                            'id' => $id,
                            'form_name' => $model->formName(),
                        ]
                    ),
                ],
            ];
        }
        echo '<div class="widget-toolbar">';
        ?>

            <?=
            yii\bootstrap\Nav::widget(
                [
                    'items' => $items,
                    'options' => [
                        'class' => 'nav-tabs',
                    ],
                ]
            );
            ?>
            <?php if (count($property_groups_to_add) > 0): ?>
                <?= \yii\bootstrap\ButtonDropdown::widget(
                    [
                        'label' => Yii::t('core', 'Add'),
                        'dropdown' => [
                            'items' => $new_groups,
                        ],
                        'options' => [
                            // 'class' => 'pull-right',
                            'class' => 'btn-xs',
                        ],
                        'containerOptions' => [
                            'class' => 'prop-addnew-dropdown',
                        ]
                    ]
                ); ?>
            <?php endif ?>

        <?php
        echo '</div>';
        ?>

        <div class="tab-content">
            <?php
            $model->getAbstractModel()->setArrayMode(true);
            foreach ($entity_property_groups as $i => $opg) {
                echo '<div class="tab-pane';
                if ($i == 0) {
                    echo ' active';
                }
                echo '" id="pg-' . $opg->group->id . '"">';
                $properties = common\components\properties\models\Property::getForGroupId($opg->group->id);

                foreach ($properties as $prop) {
                    if ($property_values = $model->getPropertyValuesByPropertyId($prop->id)) {
                        echo $prop->handler($form, $model->getAbstractModel(), $property_values, 'backend_edit_view');

                    }


                }
                echo "</div>";
            }
            ?>
        </div>
