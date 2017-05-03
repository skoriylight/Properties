<?php
use yii\helpers\Html;
use common\components\properties\behaviors\HasProperties;

?>
    <div id="properties-widget-<?= $widget_id ?>" class="backend-properties-widget">
        <?= $this->render('_widget-content', [
            'model' => $model,
            'entity_property_groups' => $entity_property_groups,
            'property_groups_to_add' => $property_groups_to_add,
            'form' => $form,
        ]) ?>
    </div>
<?php
//$formSerialize = serialize($form);
$fieldAddPropertyGroup = HasProperties::FIELD_ADD_PROPERTY_GROUP;
$fieldRemovePropertyGroup = HasProperties::FIELD_REMOVE_PROPERTY_GROUP;
$js = <<<JS
    $("#properties-widget-{$widget_id}").on('click', '.add-property-group', function(event) {
        var _this = $(this),
            data = _this.data('pg'),
            //_form = _this.closest('form'), //$('#{form->id}'), 
            //csrf = _form.find('input[name=_csrf]').val(),
            widget = $("#properties-widget-{$widget_id}");
            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            
        $.ajax({
            url: '/admin/property/ajax-group',
            type: 'post',
            //dataType: 'json',
            data: {
                _csrf: csrfToken,
                //action: 'add',
                operation: '{$fieldAddPropertyGroup}',
                value: data.id,
                model: data.form_name,
                id: {$model->id}
            },
            success: function (data) {
                //console.log(widget);
                widget.html(data);
            },
            error: function (data) {
                //alert(data);
                console.log('ajax /admin/property/ajax-group add error');
            }            
        });
        
        return false;
    });
    
    $("#properties-widget-{$widget_id}").on('click', '.remove-property-group', function(event) {
        var _this = $(this),
            data = _this.data('pg'),
            //_form = _this.closest('form'), //$('#{form->id}'), 
            //csrf = _form.find('input[name=_csrf]').val(),
            widget = $("#properties-widget-{$widget_id}");
            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            
        $.ajax({
            url: '/admin/property/ajax-group',
            type: 'post',
            //dataType: 'json',
            data: {
                _csrf: csrfToken,
                //action: 'remove',
                operation: '{$fieldRemovePropertyGroup}',
                value: data.id,
                model: data.form_name,
                id: {$model->id}
            },
            success: function (data) {
                //console.log(widget);
                widget.html(data);
            },
            error: function (data) {
                //alert(data);
                console.log('ajax /admin/property/ajax-group remove error');
            }            
        });
        
        return false;
    });
JS;

$this->registerJs($js, \yii\web\View::POS_READY, 'back-prop-widget');
