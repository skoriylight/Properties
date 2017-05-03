<?php

namespace common\components\properties\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "entity_static_value".
 *
 * @property integer $id
 * @property integer $entity_id
 * @property integer $entity_model_id
 * @property integer $property_static_value_id
 */
class EntityStaticValue extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%core_entity_static_value}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entity_id', 'entity_model_id', 'property_static_value_id'], 'required'],
            [['entity_id', 'entity_model_id', 'property_static_value_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('core', 'ID'),
            'entity_id' => Yii::t('core', 'Object ID'),
            'entity_model_id' => Yii::t('core', 'Object Model ID'),
            'property_static_value_id' => Yii::t('core', 'Property Static Value ID'),
        ];
    }

    public function getPropertyStaticValue()
    {
        return $this->hasOne(PropertyStaticValue::className(), ['id' => 'property_static_value_id']);
    }

}
