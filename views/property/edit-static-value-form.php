<?= $form->field($model, 'name_ru')?>
<?= $form->field($model, 'name_uk')?>
<?= $form->field($model, 'value', [
//    'copyFrom' => [
//        "#propertystaticvalues-name",
//    ]
])?>

<?= $form->field($model, 'slug', [
//    'makeSlug' => [
//        "#propertystaticvalues-name",
//        "#propertystaticvalues-value",
//    ]
])?>

<?= $form->field($model, 'position')?>

<?= ''//$form->field($model, 'dont_filter')->checkbox() ?>
