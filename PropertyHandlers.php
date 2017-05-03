<?php

namespace common\components\properties;

use common\components\properties\handlers\dummy\DummyHandler;

class PropertyHandlers
{
    static private $handlers = [];

    /**
     * @param \common\components\properties\models\PropertyHandler $property_handler
     * @return handlers\AbstractHandler|DummyHandler
     */
    static public function createHandler(\common\components\properties\models\PropertyHandler $property_handler)
    {
        if (!isset(static::$handlers[$property_handler->id])) {
            $handlerClass = $property_handler->handler_class_name;

            if (is_subclass_of($handlerClass, '\common\components\properties\handlers\AbstractHandler')) {
                /** @var $handler \common\components\properties\handlers\AbstractHandler */
                $handler = new $handlerClass($property_handler);
                static::$handlers[$property_handler->id] = $handler;

                return $handler;
            }
        } elseif (isset(static::$handlers[$property_handler->id])) {
            return static::$handlers[$property_handler->id];
        }

        return static::$handlers[$property_handler->id] = new DummyHandler($property_handler);
    }

    /**
     * @param null $id
     * @return null
     */
    static public function getHandlerById($id = null)
    {
        if (null === $id) {
            return null;
        }

        if (isset(static::$handlers[$id])) {
            return static::$handlers[$id];
        }

        return null;
    }
}
?>