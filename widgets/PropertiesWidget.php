<?php

namespace common\components\properties\widgets;

use common\components\properties\models\EntityPropertyGroup;
use common\components\properties\models\PropertyGroup;
use yii;
use yii\base\Widget;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class PropertiesWidget
 * @property ActiveRecord $model
 * @property ActiveForm $form
 * @property array $EntityPropertyGroups
 * @property array $propertyGroupsToAdd
 * @property string $viewFile
 * @package common\components\properties
 */
class PropertiesWidget extends Widget
{
    //private $entity;
    private $entityPropertyGroups = [];
    private $propertyGroupsToAdd = [];
    public $form;
    public $model;
    public $viewFile = 'properties-widget';

    /**
     * @inheritdoc
     */
    public function run()
    {
        //$this->entity = $this->model->getEntity();
        //$cacheKey = 'PropertiesWidget: ' . get_class($this->model) . ':' . $this->model->id;
        //$data = Yii::$app->cache->get($cacheKey);
        //if ($data === false) {
            $this->entityPropertyGroups = EntityPropertyGroup::getForModel($this->model);
        
            $addedPropertyGroupsIds = [];
            foreach ($this->entityPropertyGroups as $opg) {
                $addedPropertyGroupsIds[] = $opg->property_group_id;
            }
            $restPg = (new Query())
                ->select('id, name')
                ->from(PropertyGroup::tableName())
                ->where([
                        'entity_id' => $this->model->getEntityField('id'),
                ])
                ->andWhere([
                        'not in', 'id', $addedPropertyGroupsIds,
                ])
                ->orderBy('position')
                ->all();
            //echo $restPg->createCommand()->rawSql;

            $this->propertyGroupsToAdd = ArrayHelper::map($restPg, 'id', 'name');


            /*Yii::$app->cache->set(
                $cacheKey,
                [
                    'EntityPropertyGroups' => $this->EntityPropertyGroups,
                    'propertyGroupsToAdd' => $this->propertyGroupsToAdd,
                ],
                86400
                ,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(get_class($this->model)),
                            ActiveRecordHelper::getCommonTag(PropertyGroup::className()),
                            ActiveRecordHelper::getCommonTag(Property::className()),
                        ],
                    ]
                )
            );*/
        //} else {
        //    $this->EntityPropertyGroups = $data['EntityPropertyGroups'];
        //    $this->propertyGroupsToAdd = $data['propertyGroupsToAdd'];
        //}
        
        return $this->render(
            $this->viewFile,
            [
                'model' => $this->model,
                //'entity' => $this->entity,
                'entity_property_groups' => $this->entityPropertyGroups,
                'property_groups_to_add' => $this->propertyGroupsToAdd,
                'form' => $this->form,
                'widget_id' => $this->getId(),
            ]
        );
    }
}
