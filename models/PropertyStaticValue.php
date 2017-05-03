<?php

namespace common\components\properties\models;

use common\components\properties\behaviors\HasProperties;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "property_static_values".
 * @property integer $id
 * @property integer $property_id
 * @property string $name
 * @property string $value
 * @property string $slug
 * @property integer $position
 * @property Property $property
 * @property integer $dont_filter
 */
class PropertyStaticValue extends ActiveRecord
{
    public static $identity_map_by_property_id = [];
    private static $identity_map = [];

    use \common\traits\SortModels;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => 'position',
                ],
                'value' => 0,
            ],
            [
                'class' => HasProperties::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%core_property_static_value}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['property_id', 'name_ru', 'value'], 'required'],
            //[['property_id', 'position', 'dont_filter'], 'integer'],
            [['property_id', 'position'], 'integer'],
            //[['title_prepend'], 'boolean'],
            //[['name', 'value', 'slug', 'title_append'], 'string']
            [[ 'name_ru', 'name_uk', 'value', 'slug'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('core', 'ID'),
            'property_id' => Yii::t('core', 'Property ID'),
            'name' => Yii::t('core', 'Name'),
            'name_ru' => Yii::t('core', 'Name').'(RU)',
            'name_uk' => Yii::t('core', 'Name').'(UK)',
            'value' => Yii::t('core', 'Value'),
            'slug' => Yii::t('core', 'Slug'),
            'position' => Yii::t('core', 'Position'),
        ];
    }

    public function getName(){
        $lang = \Yii::$app->urlManager->langByUrl;
        $title = $this->{'name_'.$lang};
        return (($title == '' || $title == Null)?$this->name_ru:$title);
    }

    /**
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = static::find()->where(['property_id' => $this->property_id]);
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
        $query->andFilterWhere(['like', 'value', $this->value]);
        $query->andFilterWhere(['like', 'slug', $this->slug]);
        return $dataProvider;
    }

    public function getProperty()
    {
        return $this->hasOne(Property::className(), ['id' => 'property_id']);
    }

    /**
     * Возвращает Массив! по ID с использованием IdentityMap
     * @param int $id
     * @return null|PropertyStaticValue
     */
    public static function findById($id)
    {
        if (!isset(static::$identity_map[$id])) {
            if (null !== $property = static::find()->where(['id' => $id])->asArray()->one()) {
                //--
            }
            static::$identity_map[$id] = $property;
        }
        return static::$identity_map[$id];
    }

    /**
     * Возвращает массив возможных значений свойств по property_id
     * Внимание! Это массивы, а не объекты!
     * Это сделано для экономии памяти.
     * Используется identity_map
     *
     * @return array
     */
    public static function getValuesForPropertyId($property_id)
    {
        if (!isset(static::$identity_map_by_property_id[$property_id])) {
            static::$identity_map_by_property_id[$property_id] = static::arrayOfValuesForPropertyId($property_id);
            foreach (static::$identity_map_by_property_id[$property_id] as $psv) {
                static::$identity_map[$psv['id']] = $psv;
            }
        }
        return static::$identity_map_by_property_id[$property_id];
    }

    public static function getSelectForPropertyId($property_id)
    {
        $values = PropertyStaticValue::getValuesForPropertyId($property_id);
        $result = [];
        foreach ($values as $row) {
            $result[$row['id']] = $row['name_ru'];
        }
        return $result;
    }

    /**
     * @param $property_id
     * @param $category_id
     * @param $properties
     * @return PropertyStaticValue[]
     */
    public static function getValuesForFilter($property_id, $category_id, $properties, $multiple = false, $parentsOnly = true)
    {
        $priceMin = Yii::$app->request->get('price_min');
        $priceMax = Yii::$app->request->get('price_max');
        $cacheKey = "getValuesForFilter:" . json_encode([$property_id, $category_id, $properties, $priceMin, $priceMax]);
        if (false === $allSelections = Yii::$app->cache->get($cacheKey)) {
            $joinCondition = (true === $parentsOnly) ?
                'p.id = {{%product_category}}.entity_model_id AND p.active = 1 AND p.parent_id = 0'
                : 'p.id = {{%product_category}}.entity_model_id AND p.active = 1';
            $objectModel = Object::getForClass(Product::className());
            $objectId = $objectModel !== null ? $objectModel->id : 0;
            $allSelections = static::find()
                ->asArray(true)
                ->select([self::tableName() . '.id', self::tableName() . '.name_ru', self::tableName() . '.name_uk', 'value',
                    self::tableName() . '.slug', 'count(*) as allcount'])
                ->innerJoin(
                    EntityStaticValue::tableName(),
                    EntityStaticValue::tableName() . '.property_static_value_id=' . self::tableName() . '.id'
                )
                ->innerJoin(
                    '{{%product_category}}',
                    '{{%product_category}}.entity_model_id = ' . EntityStaticValue::tableName() . '.entity_model_id'
                )
                ->innerJoin(
                    Product::tableName() . ' p',
                    $joinCondition
                )
                ->where(
                    [
                        self::tableName() . '.property_id' => $property_id,
                        self::tableName() . '.dont_filter' => 0,
                        '{{%product_category}}.category_id' => $category_id,
                    ]
                )
                ->groupBy(
                    self::tableName() . '.id'
                )
                ->orderBy(
                    [
                        self::tableName() . '.position' => SORT_ASC,
                        self::tableName() . '.name_ru' => SORT_ASC,
                    ]
                )->all();

            /** @var ActiveQuery $query */
            $query = EntityStaticValue::find()
                ->distinct(true)
                ->select(EntityStaticValue::tableName() . '.entity_model_id')
                ->where(['entity_id' => $objectId]);
            if (false === empty($properties)) {
                foreach ($properties as $propertyId => $PropertyStaticValue) {
                    $subQuery = self::initSubQuery($category_id, $joinCondition);
                    $subQuery->andWhere(['property_static_value_id' => $PropertyStaticValue,]);

                    $subQueryOptimisation = Yii::$app->db->cache(function($db) use ($subQuery) {
                        $ids = implode(', ', $subQuery->createCommand($db)->queryColumn());
                        return empty($ids) === true ? '(-1)' : "($ids)";
                    }, 86400, new TagDependency([
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(EntityStaticValue::className()),
                        ]
                    ]));
                    $query->andWhere(new Expression('`entity_model_id` IN ' . $subQueryOptimisation));
                }
            }
            if (false === empty($priceMin) && false === empty($priceMax)) {
                $subQuery = self::initSubQuery($category_id, $joinCondition);
                $subQuery
                    ->andWhere('p.price >= (:min_price * currency.convert_nominal / currency.convert_rate)',
                        [':min_price' => $priceMin])
                    ->andWhere('p.price <= (:max_price * currency.convert_nominal / currency.convert_rate)',
                        [':max_price' => $priceMax])
                    ->leftJoin(Currency::tableName() . ' ON currency.id = p.currency_id');
                $subQueryOptimisation = Yii::$app->db->cache(function($db) use ($subQuery) {
                    $ids = implode(', ', $subQuery->createCommand($db)->queryColumn());
                    return empty($ids) === true ? '(-1)' : "($ids)";
                }, 86400, new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getCommonTag(EntityStaticValue::className()),
                    ]
                ]));
                $query->andWhere(new Expression('`entity_model_id` IN ' . $subQueryOptimisation));
            }

            $selectedQuery = static::find()
                ->select([static::tableName() . '.id', 'count(*) as selcount'])
                ->asArray(true)
                ->innerJoin(
                    EntityStaticValue::tableName(),
                    EntityStaticValue::tableName() . '.property_static_value_id = ' . static::tableName() . '.id'
                )
                ->innerJoin(
                    '{{%product_category}}',
                    '{{%product_category}}.entity_model_id = ' . EntityStaticValue::tableName() . '.entity_model_id'
                )
                ->innerJoin(
                    Product::tableName() . ' p',
                    $joinCondition //Product::tableName() . '.id = ' . EntityStaticValue::tableName() . '.entity_model_id'
                )
                ->where([
                    'property_id' => $property_id,
                    //Product::tableName() . '.main_category_id' => $category_id,
                    //Product::tableName() . '.active' => 1,
                    '{{%product_category}}.category_id' => $category_id,
                ])
                ->andWhere(
                    new Expression(
                        EntityStaticValue::tableName() . '.entity_model_id IN (' . $query->createCommand()->getRawSql() . ')'
                    )
                );
            if (false == $multiple) {
                if (isset($properties[$property_id])) {
                    $selectedQuery->andWhere([self::tableName() . '.id' => $properties[$property_id]]);
                }
            } else {
                unset($properties[$property_id]);
            }
            $selectedQuery->groupBy(static::tableName() . '.id');
            $selected = $selectedQuery->all();
            $newSelected = [];
            foreach ($selected as $sel) {
                $newSelected[$sel['id']] = $sel['selcount'];
            }
            foreach ($allSelections as $index => $selection) {
                //$allSelections[$index]['active'] = in_array($selection['id'], $selected);
                $allSelections[$index]['selcount'] = isset($newSelected[$selection['id']]) ? $newSelected[$selection['id']] : 0;
                $allSelections[$index]['active'] = ($allSelections[$index]['selcount'] == true);
            }
            if (null !== $allSelections) {
                Yii::$app->cache->set(
                    $cacheKey,
                    $allSelections,
                    0,
                    new TagDependency(
                        [
                            'tags' => [
                                ActiveRecordHelper::getCommonTag(PropertyStaticValue::className()),
                                ActiveRecordHelper::getCommonTag(Property::className()),
                            ]

                        ]
                    )
                );
            }
        }
        return $allSelections;
    }

    private static function initSubQuery($category_id, $joinCondition)
    {
        $subQuery = EntityStaticValue::find();
        return $subQuery
            ->select(EntityStaticValue::tableName() . '.entity_model_id')
            ->innerJoin(
                '{{%product_category}}',
                '{{%product_category}}.entity_model_id = ' . EntityStaticValue::tableName() . '.entity_model_id'
            )->innerJoin(
                Product::tableName() . ' p',
                $joinCondition
            )->where(
                [
                    'category_id' => $category_id,
                ]
            );
    }

    /**
     * Аналогично getValuesForPropertyId
     * Но identity_map не используется
     * @param int $property_id
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public static function arrayOfValuesForPropertyId($property_id)
    {
        $values = static::find()
            ->where(['property_id' => $property_id])
            ->orderBy([
                'position' => SORT_ASC,
                'name_ru' => SORT_ASC,
            ])
            ->asArray()
            ->all();
        return $values;
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (null !== $parent = Property::findById($this->property_id)) {
            //$parent->invalidateModelCache();
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function beforeDelete()
    {
        if (null !== $parent = Property::findById($this->property_id)) {
            //$parent->invalidateModelCache();
        }
        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        EntityStaticValue::deleteAll(['property_static_value_id' => $this->id]);
        parent::afterDelete();
    }
}
