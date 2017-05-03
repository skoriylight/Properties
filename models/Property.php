<?php
namespace common\components\properties\models;

//use app\modules\shop\models\FilterSets;
use common\components\properties\models\PropertyGroup;
use common\components\Entity;
use common\components\properties\behaviors\HasProperties;
use common\components\properties\behaviors\Events;
use common\components\properties\PropertyHandlers;
use yii\behaviors\SluggableBehavior;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\Json;


/**
 * @property integer $id
 * @property integer $property_group_id
 * @property string $name
 * @property string $key
 * @property string $type
 * @property integer $is_range
 * @property integer $multiple
 * @property integer $position
 * @property PropertyGroup $group
 */
class Property extends ActiveRecord
{
    const TYPE_STATIC = 0;
    const TYPE_EAV = 1;
    const TYPE_COLUMN = 2;

    public static $identity_map = [];
    public static $group_id_to_property_ids = [];
    private $handlerAdditionalParams = [];
    public $required;
    public $interpret_as;
    public $captcha;

    public static function getTypes() {
        return [
            self::TYPE_STATIC => Yii::t('core', 'Static'),
            self::TYPE_EAV => Yii::t('core', 'EAV'),
            self::TYPE_COLUMN => Yii::t('core', 'Column'),
        ];
    }
    public function getTypeCaption() {
        return $this->type !== null ? self::getTypes()[$this->type] : null ;
    }

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
            [
                'class' => SluggableBehavior::className(),
                'attribute' => 'name_convert',
                'slugAttribute' => 'key',
                'immutable' => true,
                'ensureUnique' => true,
            ],
            [
                'class' => HasProperties::className(),
            ],

            [
                'class' => Events::className(),
            ],
            [
                'class' => \common\behaviors\ManyToManyBehavior::className(),
                'relations' => [
                    'group_ids' => 'groups',
                ],
            ],
        ];
    }

    public function getGroups()
    {
        return $this->hasMany(PropertyGroup::className(), ['id' => 'group_id'])
             ->viaTable('{{%core_property_marge}}', ['property_id' => 'id']);
    }

    public function getName_convert()
    {
        $vowels = ['-',' ','/',':','','.'];
        return str_replace($vowels, "_", $this->name_ru).time();
    }

    


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%core_property}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_ids'], 'each', 'rule' => ['integer']],
            [['property_group_id', 'name_ru', 'property_handler_id', 'handler_additional_params'], 'required'],
            [
                [
                    'property_group_id',
                    'property_handler_id',
                    'is_range',
                    'multiple',
                    'position',
                    'type',
                ],
                'integer'
            ],
            //[['interpret_as'], 'string'],
            //[['name', 'handler_additional_params', 'depended_property_values', 'value_type', 'mask'], 'string'],
            [[ 'name_ru', 'name_uk', 'handler_additional_params','label','label_admin','required_field'], 'string'],
            [['key'], 'string', 'max' => 80],
            [['key'], 'match', 'pattern' => '#^[\w-]+$#'],
            //[['depends_on_property_id', 'depends_on_category_group_id'], 'default', 'value' => 0],
            //[['required', 'captcha'], 'integer', 'min' => 0, 'max' => 1],
            //[['dont_filter'], 'safe'],
            [['key'], 'unique', 'targetAttribute' => ['key', 'property_group_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('core', 'ID'),
            'property_group_id' => Yii::t('core', 'Property Group ID'),
            'name' => Yii::t('core', 'Name'),
            'name_ru' => Yii::t('core', 'Name').'(RU)',
            'name_uk' => Yii::t('core', 'Name').'(UK)',
            'key' => Yii::t('core', 'Key'),
            'type' => Yii::t('core', 'Value Type'),
            'property_handler_id' => Yii::t('core', 'Property Handler ID'),
            'is_range' => Yii::t('core', 'Range'),
            'multiple' => Yii::t('core', 'Multiple'),
            'position' => Yii::t('core', 'Sort Order'),
            'label' => 'Доп.опц.',
            'label_admin' => 'Метка',
            'required_field' => 'Обязательно для заполнения'
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
        /* @var $query \yii\db\ActiveQuery */
        $query = static::find()->leftJoin('core_property_marge', 'core_property_marge.property_id = core_property.id')
        ->where(['core_property_marge.group_id' => $this->property_group_id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'name_ru', $this->name_ru]);
        $query->andFilterWhere(['like', 'key', $this->key]);
        $query->andFilterWhere(['property_handler_id' => $this->property_handler_id]);
        $query->andFilterWhere(['type' => $this->type]);
        $query->andFilterWhere(['is_range' => $this->is_range]);
        $query->andFilterWhere(['multiple' => $this->multiple]);
        return $dataProvider;
    }

    public function searchAll($params)
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
        $query->andFilterWhere(['like', 'name_ru', $this->name_ru]);
        $query->andFilterWhere(['like', 'key', $this->key]);
        $query->andFilterWhere(['property_handler_id' => $this->property_handler_id]);
        $query->andFilterWhere(['type' => $this->type]);
        $query->andFilterWhere(['is_range' => $this->is_range]);
        $query->andFilterWhere(['multiple' => $this->multiple]);
        return $dataProvider;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(PropertyGroup::className(), ['id' => 'property_group_id']);
    }

    /**
     * @return PropertyHandler
     */
    public function getHandler()
    {
//        return $this->hasOne(PropertyHandler::className(), ['id' => 'property_handler_id']);
        return PropertyHandler::findById($this->property_handler_id);
    }

    /**
     * Возвращает модель по ID с использованием IdentityMap
     * @param int $id
     * @return null|Property
     */
    public static function findById($id)
    {
        if (!isset(static::$identity_map[$id])) {
            $cacheKey = "Property:$id";
            if (false === $prop = Yii::$app->cache->get($cacheKey)) {
                if (null === $prop = static::findOne($id)) {
                    return null;
                }
                Yii::$app->cache->set(
                    $cacheKey,
                    $prop,
                    0
                );
            }
            static::$identity_map[$id] = $prop;
        }
        return static::$identity_map[$id];
    }

    /**
     * @param $group_id
     * @return null|Property[]
     */
    public static function getForGroupId($group_id)
    {
        if (!isset(static::$group_id_to_property_ids[$group_id])) {
            $cacheKey = "PropsForGroup:$group_id";
            if (false === $props = Yii::$app->cache->get($cacheKey)) {
                if (null !== $props = static::find()->select('core_property.*')
                                       //->where(['property_group_id' => $group_id])
                                        ->leftJoin('core_property_marge', 
                                        '`core_property_marge`.`property_id` = `core_property`.`id`')
                                        ->where(['core_property_marge.group_id' => $group_id])
                                        ->orderBy('position')
                                        ->all()
                ) {
                    Yii::$app->cache->set(
                        $cacheKey,
                        $props,
                        0
                    );
                }
            }
            static::$group_id_to_property_ids[$group_id] = [];
            foreach ($props as $property) {
                static::$identity_map[$property->id] = $property;
                static::$group_id_to_property_ids[$group_id][] = $property->id;
            }
            return $props;
        }
        $properties = [];
        foreach (static::$group_id_to_property_ids[$group_id] as $property_id) {
            $properties[] = static::findById($property_id);
        }
        return $properties;
    }

    /**
     * @param $form
     * @param $model
     * @param $values
     * @param string $renderType
     * @return string
     */
    public function handler($form, $model, $values, $renderType = 'frontend_render_view')
    {
        $handler = $this->handler;
        if (null === $handler) {
            return '';
        }
        $handler = PropertyHandlers::createHandler($handler);
        if (null === $handler) {
            return '';
        }
        return $handler->render($this, $model, $values, $form, $renderType);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->handlerAdditionalParams = Json::decode($this->handler_additional_params);
        $this->required = isset($this->handlerAdditionalParams['rules']) && is_array(
                $this->handlerAdditionalParams['rules']
            ) && in_array('required', $this->handlerAdditionalParams['rules']);
        $this->interpret_as = isset($this->handlerAdditionalParams['interpret_as']) ? $this->handlerAdditionalParams['interpret_as'] : 0;
        if (isset($this->handlerAdditionalParams['rules']) && is_array($this->handlerAdditionalParams['rules'])) {
            foreach ($this->handlerAdditionalParams['rules'] as $rule) {
                if (is_array($rule)) {
                    if (in_array('captcha', $rule, true)) {
                        $this->captcha = true;
                    }
                } else {
                    switch ($rule) {
                        case 'required':
                            $this->required = true;
                            break;
                    }
                }
            }
        }
    }

    /**
     * @inheritdoc
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $handlerAdditionalParams = $this->isNewRecord ? [] : Json::decode($this->handler_additional_params);
        $handlerRules = [];
        if (1 === intval($this->required)) {
            $handlerRules[] = 'required';
        }
        if (PropertyHandler::findByName('File') === intval($this->property_handler_id)) {
            if (1 === intval($this->multiple)) {
                $handlerRules[] = ['file', 'maxFiles' => 0];
            } else {
                $handlerRules[] = ['file', 'maxFiles' => 1];
            }
        }
        if (1 === intval($this->captcha)) {
            $handlerRules[] = ['captcha', 'captchaAction' => '/default/captcha'];
        }
        $handlerAdditionalParams['interpret_as'] = $this->interpret_as;
        $handlerAdditionalParams['rules'] = $handlerRules;
        $this->handlerAdditionalParams = $handlerAdditionalParams;
        $this->handler_additional_params = Json::encode($handlerAdditionalParams);
        return true;
    }

//    /**
//     *
//     */
//    public function invalidateModelCache()
//    {
//        TagDependency::invalidate(
//            Yii::$app->cache,
//            [
//                ActiveRecordHelper::getObjectTag(
//                    PropertyGroup::className(),
//                    $this->property_group_id
//                ),
//                ActiveRecordHelper::getObjectTag(Property::className(), $this->id)
//            ]
//        );
//    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        // @todo clear table schema
        //$this->invalidateModelCache();
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        //$this->invalidateModelCache();
        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        $staticValues = PropertyStaticValue::find()->where(['property_id' => $this->id])->all();
        foreach ($staticValues as $psv) {
            $psv->delete();
        }
        /*
        $entity = Entity::findOne($this->group->entity_id);
        if ($this->is_eav) {
            Yii::$app->db->createCommand()->delete(
                $entity['table_eav'],
                ['key' => $this->key, 'property_group_id' => $this->group->id]
            )->execute();
        }
        if ($this->is_column_type_stored) {
            Yii::$app->db->createCommand()->dropColumn($entity['table_column'], $this->key)->execute();
            //                if ($object->object_class == Form::className()) {
            //                    $submissionObject = Object::getForClass(Submission::className());
            //                    Yii::$app->db->createCommand()
            //                        ->dropColumn($submissionObject->column_properties_table_name, $this->key)
            //                        ->execute();
            //                }
        }
        */
        //FilterSets::deleteAll(['property_id' => $this->id]);
        parent::afterDelete();
    }

    /**
     * @param $name
     * @return null|mixed
     */
    public function getAdditionalParam($name)
    {
        if (isset($this->handlerAdditionalParams[$name])) {
            return $this->handlerAdditionalParams[$name];
        }
        return null;
    }

    /**
     * @return array
     */
    public static function getAliases()
    {
        return [
            0 => Yii::t('core', 'Not selected'),
            1 => 'date',
            2 => 'ip',
            3 => 'url',
            4 => 'email',
        ];
    }
}
