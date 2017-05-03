<?php

namespace common\components\properties\models;

use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "property_handler".
 *
 * @property integer $id
 * @property string $name
 * @property string $frontend_render_view
 * @property string $frontend_edit_view
 * @property string $backend_render_view
 * @property string $backend_edit_view
 * @property string $handler_class_name
 */
class PropertyHandler extends ActiveRecord
{
    private static $identity_map = [];
    private static $select_array_cache = null;
    private static $map_name_to_id = [];

    public function behaviors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%core_property_handler}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['name', 'frontend_render_view', 'frontend_edit_view', 'backend_render_view',
                    'backend_edit_view', 'handler_class_name'],
                'required'
            ],
            [
                ['name', 'frontend_render_view', 'frontend_edit_view', 'backend_render_view',
                    'backend_edit_view', 'handler_class_name'],
                'string'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('core', 'ID'),
            'name' => Yii::t('core', 'Name'),
            'frontend_render_view' => Yii::t('core', 'Frontend Render View'),
            'frontend_edit_view' => Yii::t('core', 'Frontend Edit View'),
            'backend_render_view' => Yii::t('core', 'Backend Render View'),
            'backend_edit_view' => Yii::t('core', 'Backend Edit View'),
            'handler_class_name' => Yii::t('core', 'Handler Class Name'),
        ];
    }

    /**
     * Возвращает модель по ID с использованием IdentityMap
     */
    public static function findById($id)
    {
        if (!isset(static::$identity_map[$id])) {
            static::$identity_map[$id] = Yii::$app->cache->get('PropertyHandler: ' . $id);
            if (static::$identity_map[$id] === false) {
                static::$identity_map[$id] = PropertyHandler::findOne($id);
                if (static::$identity_map[$id] !== null) {
                    Yii::$app->cache->set(
                        'PropertyHandler: ' . $id,
                        static::$identity_map[$id],
                        86400
                    );
                }
            }
        }
        return static::$identity_map[$id];
    }

    /**
     * Возвращает список всех объектов
     * Ключ - ID
     * Значение - name
     * Используется для фильтрации в таблицах и выборе объекта в форме
     */
    public static function getSelectArray()
    {
        if (static::$select_array_cache === null) {
            static::$select_array_cache = Yii::$app->cache->get('PropertyHandlersList');
            if (static::$select_array_cache === false) {
                $rows = (new Query())
                    ->select('id, name')
                    ->from(PropertyHandler::tableName())
                    ->all();
                static::$select_array_cache = ArrayHelper::map($rows, 'id', 'name');
                Yii::$app->cache->set(
                    'PropertyHandlersList',
                    static::$select_array_cache,
                    86400
                );
            }
        }
        return static::$select_array_cache;
    }

    /**
     * @param $name
     * @return null|int
     */
    public static function findByName($name)
    {
        if (empty(static::$map_name_to_id)) {
            $cache_key = 'PropertyHandler:MapToId';
            static::$map_name_to_id = Yii::$app->cache->get($cache_key);
            if (false === static::$map_name_to_id) {
                $query = static::find()->asArray()->all();
                foreach ($query as $row) {
                    static::$map_name_to_id[$row['name']] = intval($row['id']);
                }
                Yii::$app->cache->set(
                    $cache_key,
                    static::$map_name_to_id,
                    0
                );
            }
        }

        if (isset(static::$map_name_to_id[$name])) {
            return static::$map_name_to_id[$name];
        }

        return null;
    }
}
