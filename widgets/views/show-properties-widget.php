<?php
/**
 * @var \app\models\View $this
 * @var string $widget_id
 * @var \app\models\Submission $model
 * @var \kartik\widgets\ActiveForm $form
 */
?>

<div class="properties-widget widget-<?= $widget_id ?>">

<?php
    if (!empty($entity_property_groups)) {
        foreach ($entity_property_groups as $i => $opg) {
            if (null === $opgGroup = $opg->group) continue;
            //if ($opg->group->is_internal) continue;
            $options = [
                'id' => 'pg-' . $opgGroup->id,
                'class' => 'object-property-group',
            ];
            if ($i == 0) {
                \yii\bootstrap\Html::addCssClass($options, 'active');
            }
            echo \yii\bootstrap\Html::beginTag('div', $options);

            /** @var \common\components\properties\models\Property[] $properties */
            $properties = common\components\properties\models\Property::getForGroupId($opgGroup->id);

            foreach ($properties as $prop) {
                if ($property_values = $model->getPropertyValuesByPropertyId($prop->id)) {
                    echo $prop->handler($form, $model->getAbstractModel(), $property_values, 'frontend_render_view');
                }
            }
            echo "</div>";
        }
    } else {
        echo '<!-- Empty properties -->';
    }
?>

</div> <!-- /properties-widget -->
