<?php

namespace common\components\properties\handlers\dummy;

use common\components\properties\handlers\AbstractHandler;

class DummyHandler extends AbstractHandler
{
    /*
     *
     */
    function __construct(\common\components\properties\models\PropertyHandler $propertyHandler)
    {
    }

    /**
     * @param $property
     * @param $model
     * @param $values
     * @param $form
     * @param $renderType
     * @return string
     */
    public function render($property, $model, $values, $form, $renderType)
    {
        return '';
    }
}
?>