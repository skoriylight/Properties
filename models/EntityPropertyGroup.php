<?php

namespace common\components\properties\models;

use common\components\Entity;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "entity_property_group".
 *
 * @property integer $id
 * @property integer $entity_id
 * @property integer $entity_model_id
 * @property integer $property_group_id
 */
class EntityPropertyGroup extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%core_entity_property_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entity_id', 'entity_model_id', 'property_group_id'], 'required'],
            [['entity_id', 'entity_model_id', 'property_group_id'], 'integer'],
            [['entity_id', 'entity_model_id', 'property_group_id'], 'unique', 'targetAttribute' => ['entity_id', 'entity_model_id', 'property_group_id'], 'message' => 'Property group is already binded'],
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
            'property_group_id' => Yii::t('core', 'Property Group ID'),
        ];
    }

    public static function getForModel($model)
    {
        /** @var Object $object */
        return EntityPropertyGroup::find()
            ->joinWith('group')
            ->where(
                [
                    static::tableName() . '.entity_id' => Yii::$app->entity->getFieldByClass('id', get_class($model)),
                    static::tableName() . '.entity_model_id' => $model->id,
                ]
            )->orderBy(PropertyGroup::tableName() . '.position')
            ->all();
    }

    /**
     * @return PropertyGroup|null
     */
    public function getGroup()
    {
        return $this->hasOne(PropertyGroup::className(), ['id' => 'property_group_id']);
    }
}
?>