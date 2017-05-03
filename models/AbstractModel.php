<?php

namespace common\components\properties\models;

use common\components\properties\models\EntityStaticValue;
use common\components\properties\models\Property;
use common\components\properties\PropertyValue;
use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class AbstractModel extends Model
{
    /**
     * @var PropertyValue[] $values_by_property_key
     * @var string $form_name
     * @var array $rules
     * @var Property[] $properties_models
     */
    private $values_by_property_key = [];
    private $form_name;
    private $properties_models = [];
    private $rules = [];
    private $ownerModel = null;

    /**
     * Changes to behavior of getter and PropertyValue->toVal() to array mode.
     * Useful in backend when you want to edit multiple-valued properties.
     * @var bool
     */
    private $arrayMode = false;

    public function __construct($config = [], $ownerModel = null)
    {
        parent::__construct($config);
        $this->ownerModel = $ownerModel;
    }

    /**
     * @param null $value
     * @return bool
     */
    public function setArrayMode($value = null) {
        $lastValue = $this->arrayMode;

        if (is_bool($value)) {
            $this->arrayMode = $value;
        }

        return $lastValue;
    }

    /**
     * @param $name
     */
    public function setFormName($name)
    {
        $this->form_name = $name;
    }

    /**
     * @return mixed
     */
    public function formName()
    {
        return $this->form_name;
    }

    /**
     * @param $properties_models \common\components\properties\models\Property[]
     */
    public function setPropertiesModels($properties_models)
    {
        $this->properties_models = $properties_models;
        $this->values_by_property_key = array_fill_keys(array_keys($properties_models), []);
    }

    /**
     * @return Property[]
     */
    public function getPropertiesModels()
    {
        return $this->properties_models;
    }

    public function rules()
    {
        return ArrayHelper::merge(
            [
                [array_keys($this->properties_models), 'safe'],
            ],
            $this->rules
        );
    }

    public function addRules($rules)
    {
        $this->rules = ArrayHelper::merge($this->rules, $rules);
    }

    public function clearRules()
    {
        $this->rules = [];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['readonly'] = [];
        return $scenarios;
    }


    public function attributeLabels()
    {
        $labels = [];
        foreach ($this->properties_models as $property) {
            $labels[$property->key] = $property->name;
        }
        return $labels;
    }

    public function __get($name)
    {
        if (isset($this->values_by_property_key[$name])) {
            return $this->values_by_property_key[$name]->toValue($this->arrayMode);
        }
        return parent::__get($name);
    }

    public function attributes()
    {
        return array_keys($this->values_by_property_key);
    }

    public function setAttributes($values, $safeOnly = true)
    {
        $this->values_by_property_key = $values;
    }

    public function setAttributesValues($values)
    {
        foreach ($this->values_by_property_key as $key => $value) {
            if (isset($values[$this->form_name][$key])) {
                if (is_array($values[$this->form_name][$key])) {
                    $this->values_by_property_key[$key]->values = [];
                    foreach ($this->values_by_property_key[$key]->values as $val) {
                        $this->values_by_property_key[$key]->values[] = ['value' => $val];
                    }
                } else {
                    $this->values_by_property_key[$key]->values = [['value' => $values[$this->form_name][$key]]];
                }
            }
        }
    }

    public function updateValues($new_values, $entity_id, $entity_model_id)
    {
        $column_type_updates = ['entity_model_id' => $entity_model_id];
        $osv_psv_ids = [];

        $new_eav_values = [];
        $eav_ids_to_delete = [];

        foreach ($new_values as $key => $values) {
            $property = Property::findById($values->property_id);
            //if ($property->captcha == 1) {
            //    continue;
            //}

            if (!isset($this->values_by_property_key[$key])) {
                // нужно добавить
                if ($property->type == Property::TYPE_COLUMN) {
                    $column_type_updates[$key] = (string) $values;
                } elseif ($property->type == Property::TYPE_STATIC) {
                    foreach ($values->values as $val) {
                        $osv_psv_ids[] = $val['value'];
                    }
                } elseif ($property->type == Property::TYPE_EAV) {
                    $new_eav_values[$key] = $values;
                }
            } else {
                if ($property->type == Property::TYPE_COLUMN) {
                    $column_type_updates[$key] = (string) $values;
                } elseif ($property->type == Property::TYPE_STATIC) {
                    foreach ($values->values as $val) {
                        $osv_psv_ids[] = $val['value'];
                    }
                } elseif ($property->type == Property::TYPE_EAV) {
                    // добавим новые
                    foreach ($values->values as $index => $val) {
                        $new_eav_values[] = [
                            $entity_model_id,
                            $values->property_group_id,
                            $key,
                            $val['value'],
                            $val['position'],
                        ];
                    }

                    // теперь добавим на удаление
                    foreach ($this->values_by_property_key[$key]->values as $old_val) {
                        if (isset($old_val['eav_id'])) {
                            $eav_ids_to_delete[] =  $old_val['eav_id'];
                        }
                    }
                }
            }
        }
        $osv_psv_ids_to_delete = [];
        foreach ($this->values_by_property_key as $key => $values) {
            $property = Property::findById($values->property_id);
            if (in_array($key, array_keys($new_values)) === false) {
                // if in incoming array there was no specification for this property - skip it
                continue;
            }
            if ($property->type == Property::TYPE_STATIC) {
                foreach ($values->values as $val) {
                    if (in_array($val['psv_id'], $osv_psv_ids) === false) {
                        // в новых значениях нет
                        $osv_psv_ids_to_delete[] = $val['psv_id'];
                    } else {
                        // удалим, чтобы заново не добавлять
                        unset(
                            $osv_psv_ids[
                                array_search(
                                    $val['psv_id'],
                                    $osv_psv_ids
                                )
                            ]
                        );
                    }
                }
            }
        }
        if (count($osv_psv_ids_to_delete) > 0) {
            EntityStaticValue::deleteAll(
                [
                    'and',
                    '`entity_id` = :objectId',
                    [
                        'and',
                        '`entity_model_id` = :objectModelId',
                        [
                            'in',
                            '`property_static_value_id`',
                            $osv_psv_ids_to_delete
                        ]
                    ]
                ],
                [
                    ':objectId' => $entity_id,
                    ':objectModelId' => $entity_model_id,
                ]
            );
        }
        if (count($osv_psv_ids) > 0) {
            $rows = [];
            foreach ($osv_psv_ids as $psv_id) {
                // 0 - Not Selected Field. Такие значения в базу не сохраняем
                if ($psv_id == 0) {
                    continue;
                }
                $rows[] = [
                    $entity_id, $entity_model_id, $psv_id,
                ];
            }
            if (!empty($rows)) {
                Yii::$app->db->createCommand()
                    ->batchInsert(
                        EntityStaticValue::tableName(),
                        ['entity_id', 'entity_model_id', 'property_static_value_id'],
                        $rows
                    )->execute();
            }
        }
        Yii::$app->cache->delete("PSV:".$entity_id.":".$entity_model_id);
//        if (count($column_type_updates) > 1) {
//            $table_name = Object::findById($entity_id)->column_properties_table_name;
//            $exists = Yii::$app->db->createCommand('select entity_model_id from '.$table_name . ' where entity_model_id=:entity_model_id')
//                ->bindValue(':entity_model_id', $entity_model_id)
//                ->queryScalar();
//            if ($exists) {
//                Yii::$app->db->createCommand()
//                    ->update(
//                        $table_name,
//                        $column_type_updates,
//                        'entity_model_id = :entity_model_id',
//                        [
//                            ':entity_model_id' => $entity_model_id
//                        ]
//                    )->execute();
//            } else {
//                Yii::$app->db->createCommand()
//                    ->insert(
//                        $table_name,
//                        $column_type_updates
//                    )->execute();
//            }
//        }
        if (count($eav_ids_to_delete) > 0) {
            $table_name = Yii::$app->entity->getFieldById('table_eav', $entity_id);
            Yii::$app->db->createCommand()
                ->delete($table_name, ['in', 'id', $eav_ids_to_delete])
                ->execute();
        }
        if (count($new_eav_values) > 0) {
            $table_name = Yii::$app->entity->getFieldById('table_eav', $entity_id);
            Yii::$app->db->createCommand()
                ->batchInsert($table_name, ['entity_model_id', 'property_group_id', 'key', 'value', 'position'], $new_eav_values)
                ->execute();
        }

        Yii::$app->cache->delete("TIR:".$entity_id . ':' .$entity_model_id);
        $this->values_by_property_key = $new_values;
    }

    /**
     * @param $attribute
     * @return PropertyValue|null
     */
    public function getPropertyValueByAttribute($attribute)
    {
        if (isset($this->values_by_property_key[$attribute])) {
            return $this->values_by_property_key[$attribute];
        }

        return null;
    }

    /**
     * @return ActiveRecord|null
     */
    public function getOwnerModel()
    {
        return $this->ownerModel;
    }
}
?>