<?php

namespace common\components\properties\models;

use Yii;

/**
 * This is the model class for table "core_property_marge".
 *
 * @property integer $id
 * @property integer $property_id
 * @property integer $group_id
 */
class PropertyMarge extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'core_property_marge';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['property_id', 'group_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'property_id' => 'Property ID',
            'group_id' => 'Group ID',
        ];
    }
}
