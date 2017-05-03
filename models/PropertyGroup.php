<?php

namespace common\components\properties\models;

use common\components\Entity;
use common\components\properties\behaviors\HasProperties;
use yii\helpers\ArrayHelper;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "property_group".
 *
 * @property integer $id
 * @property integer $entity_id
 * @property string $name
 * @property integer $position
 */
class PropertyGroup extends ActiveRecord
{
    private static $identity_map = [];
    private static $groups_by_entity_id = [];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'position',
                ],
                'value' => 0,
            ],
        ];
    }

    public static function listAll($keyField = 'id', $valueField = 'name', $asArray = true)
    {
        $query = static::find();
        if ($asArray) {
                $query->select([$keyField, $valueField])->asArray();
        }

        return ArrayHelper::map($query->all(), $keyField, $valueField);
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%core_property_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entity_id', 'name'], 'required'],
            [['entity_id', 'position'], 'integer'],
            [['name'], 'string']
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
            'name' => Yii::t('core', 'Name'),
            'position' => Yii::t('core', 'Sort Order'),
        ];
    }

//    /**
//     * Relation to \app\models\Object
//     * @return \yii\db\ActiveQuery
//     */
//    public function getObject()
//    {
//        return $this->hasOne(Object::className(), ['id' => 'entity_id']);
//    }

    /**
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['entity_id' => $this->entity_id]);
        return $dataProvider;
    }

    /**
     * Возвращает модель по ID с использованием IdentityMap
     *
     * @param int $id
     * @return null|PropertyGroup
     */
    public static function findById($id)
    {
        if (!isset(static::$identity_map[$id])) {
            $cacheKey = "PropertyGroup:$id";
            if (false === $group = Yii::$app->cache->get($cacheKey)) {
                if (null !== $group = static::findOne($id)) {
                    Yii::$app->cache->set(
                        $cacheKey,
                        $group,
                        0
                    );
                }
            }
            static::$identity_map[$id] = $group;
        }
        return static::$identity_map[$id];
    }

    /**
     * Relation to properties
     * @return \yii\db\ActiveQuery
     */
    public function getProperties()
    {
        return $this->hasMany(Property::className(), ['property_group_id' => 'id'])->orderBy('position');
    }

    /**
     * @param $entity_id
     * @param bool $withProperties
     * @return PropertyGroup[]
     */
    public static function getForObjectId($entity_id, $withProperties = false)
    {
        if (null === $entity_id) {
            return [];
        }

        if (!isset(static::$groups_by_entity_id[$entity_id])) {
            $cacheKey = 'PropertyGroup:objectId:'.$entity_id;
            static::$groups_by_entity_id[$entity_id] = Yii::$app->cache->get($cacheKey);
            if (!is_array(static::$groups_by_entity_id[$entity_id])) {
                $query = static::find()
                    ->where(['entity_id'=>$entity_id])
                    ->orderBy('position');
                if ($withProperties === true) {
                    $query = $query->with('properties');
                }
                static::$groups_by_entity_id[$entity_id] = $query->all();
                if (null !== $entity = Yii::$app->entity->findOne('id', $entity_id)) {
                    foreach (static::$groups_by_entity_id[$entity_id] as $propertyGroup){
                        if ($withProperties === true) {
                            foreach ($propertyGroup->properties as $prop) {
                                if (isset(Property::$group_id_to_property_ids[$propertyGroup->id]) === false) {
                                    Property::$group_id_to_property_ids[$propertyGroup->id]=[];
                                }
                                Property::$group_id_to_property_ids[$propertyGroup->id][] = $prop->id;
                                Property::$identity_map[$prop->id] = $prop;
                            }
                        }
                    }

                    Yii::$app->cache->set(
                        $cacheKey,
                        static::$groups_by_entity_id[$entity_id],
                        0
                    );
                }
            }
        }
        return static::$groups_by_entity_id[$entity_id];
    }

    /**
     * @param int $entity_id
     * @param int $entity_model_id
     * @return null|\yii\db\ActiveRecord[]
     */
    public static function getForModel($entity_id, $entity_model_id)
    {
        $cacheKey = "PropertyGroupBy:$entity_id:$entity_model_id";
        if (false === $groups = Yii::$app->cache->get($cacheKey)) {
            $group_ids = EntityPropertyGroup::find()
                ->select('property_group_id')
                ->where([
                    'entity_id' => $entity_id,
                    'entity_model_id' => $entity_model_id,
                ])->column();
            if (null === $group_ids) {
                return null;
            }
            if (null === $groups = static::find()->where(['in', 'id', $group_ids])->all()) {
                return null;
            }
            if (null !== $entity = Yii::$app->entity->findOne('id', $entity_id)) {
                Yii::$app->cache->set(
                    $cacheKey,
                    $groups,
                    0
                );
            }
        }
        return $groups;
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        $properties = Property::findAll(['property_group_id' => $this->id]);
        foreach ($properties as $prop) {
            $prop->delete();
        }
        return true;
    }

    public function afterDelete()
    {
        EntityPropertyGroup::deleteAll(['property_group_id' => $this->id]);
        parent::afterDelete();
    }

    /**
     * @param ActiveRecord|HasProperties $model
     * @param string $idAttribute
     * @return bool
     */
    public function appendToObjectModel(ActiveRecord $model, $idAttribute = 'id')
    {
        $entity = Yii::$app->entity->findOne('class', $model::className());
        if (null === $entity || !$model->hasAttribute($idAttribute)) {
            return false;
        }

        $link = new EntityPropertyGroup();
            $link->entity_id = $entity['id'];
            $link->entity_model_id = $model->$idAttribute;
            $link->property_group_id = $this->id;

        $result = $link->save();
        $model->updatePropertyGroupsInformation();

        return $result;
    }
}
?>