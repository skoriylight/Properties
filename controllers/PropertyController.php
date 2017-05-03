<?php

namespace common\components\properties\controllers;

//use app\models\Form;
//use app\models\Submission;

use common\components\Entity;
use common\components\properties\models\EntityPropertyGroup;
use common\components\properties\models\EntityStaticValue;
use common\components\properties\models\Property;
use common\components\properties\models\PropertyGroup;
use common\components\properties\models\PropertyStaticValue;
use common\components\properties\PropertyHandlers;
use common\helpers\OopHelper;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\caching\TagDependency;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PropertyController extends Controller
{
    public function getViewPath()
    {
        return Yii::getAlias('@common/components/properties/views/property');
    }

//    protected function checkDoubledSlugs($slug)
//    {
//        if (empty($slug) === false) {
//            $PropertyStaticValue = PropertyStaticValue::find()
//                ->where(['slug' => $slug])
//                ->all();
//            if (count($PropertyStaticValue) > 1) {
//                $result = Html::tag('h4', Yii::t('core', 'You have doubled slugs. Fix it please.'));
//                $result .= Html::beginTag('ul');
//                foreach ($PropertyStaticValue as $propertyStaticValue) {
//                    $property = Property::findById($propertyStaticValue->property_id);
//                    if ($property !== null) {
//                        $propertyGroup = PropertyGroup::findById($property->property_group_id);
//                        $result .= Html::tag(
//                            'li',
//                            ($propertyGroup !== null ? Html::a(
//                                $propertyGroup->name,
//                                [
//                                    '/properties/group',
//                                    'id' => $property->property_group_id,
//                                ]
//                            ) : '')
//                            . ' > '
//                            . Html::a(
//                                $property->name,
//                                [
//                                    '/properties/edit-property',
//                                    'id' => $propertyStaticValue->property_id,
//                                    'property_group_id' => $property->property_group_id,
//                                ]
//                            )
//                            . ' > '
//                            . Html::a(
//                                $propertyStaticValue->name,
//                                [
//                                    '/properties/edit-static-value',
//                                    'id' => $propertyStaticValue->id,
//                                    'property_id' => $propertyStaticValue->property_id,
//                                    'property_group_id' => $property->property_group_id,
//                                ]
//                            )
//                        );
//                    }
//                }
//                $result .= Html::endTag('ul');
//                Yii::$app->session->setFlash('warning', $result);
//            }
//        }
//    }

    public function behaviors()
    {
        return [
//            'access' => [
//                'class' => AccessControl::className(),
//                'rules' => [
//                    [
//                        'allow' => true,
//                        'roles' => ['property manage'],
//                    ],
//                ],
//            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new PropertyGroup();
        $dataProvider = $searchModel->search($_GET);

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionFields()
    {
        $searchModel = new Property();
        $dataProvider = $searchModel->searchAll($_GET);

        return $this->render(
            'fields',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionGroup($id = null)
    {
        if ($id === null) {
            $model = new PropertyGroup();
        } else {
            $model = PropertyGroup::findById($id);
        }

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            $save_result = $model->save();
            if ($save_result) {
                Yii::$app->session->setFlash('success', Yii::t('core', 'Record has been saved'));
                $returnUrl = Yii::$app->request->get('returnUrl', ['/property/index']);
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/property/group',
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            [
                                '/property/group',
                                'id' => $model->id,
                                'returnUrl' => $returnUrl,
                            ]
                        );
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('core', 'Cannot save data'));
            }
        }

        $searchModel = new Property();
        $searchModel->property_group_id = $model->id;
        $dataProvider = $searchModel->search($_GET);


        return $this->render(
            'group',
            [
                'model' => $model,
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

//    /**
//     * @param $value_type
//     * @return string
//     * @throws \Exception
//     */
//    private function getColumnType($value_type)
//    {
//        switch ($value_type) {
//            case 'STRING':
//                return 'TINYTEXT';
//            case 'NUMBER':
//                return 'FLOAT';
//            default:
//                throw new \Exception('Unknown value type');
//        }
//    }

    public function actionEditProperty($property_group_id = 1, $id = null)
    {
        if ($id === null) {
            $model = new Property();
            $model->handler_additional_params = '[]';
        } else {
            $model = Property::findById($id);
        }
        //$object = Object::getForClass(Property::className());
        $object = Yii::$app->entity->findOne('class', Property::className());
        $model->property_group_id = $property_group_id;

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            $propertyHandler = PropertyHandlers::createHandler($model->handler);
            if (!$propertyHandler->changePropertyType($model)) {
//                if ($model->is_column_type_stored) {
//                    if ($model->isNewRecord) {
//                        $object = Object::findById($model->group->entity_id);
//                        Yii::$app->db->createCommand()
//                            ->addColumn($object->column_properties_table_name, $model->key, "TINYTEXT")
//                            ->execute();
//                        if ($object->object_class == Form::className()) {
//                            $submissionObject = Object::getForClass(Submission::className());
//                            $col_type = $this->getColumnType($model->value_type);
//                            Yii::$app->db->createCommand()
//                                ->addColumn($submissionObject->column_properties_table_name, $model->key, $col_type)
//                                ->execute();
//                        }
//                    } else {
//                        if ($model->key != $model->getOldAttribute('key')) {
//                            $object = Object::findById($model->group->entity_id);
//                            Yii::$app->db->createCommand()
//                                ->renameColumn(
//                                    $object->column_properties_table_name,
//                                    $model->getOldAttribute('key'),
//                                    $model->key
//                                )->execute();
//                            if ($object->object_class == Form::className()) {
//                                $submissionObject = Object::getForClass(Submission::className());
//                                Yii::$app->db->createCommand()
//                                    ->renameColumn(
//                                        $submissionObject->column_properties_table_name,
//                                        $model->getOldAttribute('key'),
//                                        $model->key
//                                    )->execute();
//                            }
//                        }
//                        if ($model->value_type != $model->getOldAttribute('value_type')) {
//                            $object = Object::findById($model->group->entity_id);
//                            $new_type = $this->getColumnType($model->value_type);
//                            Yii::$app->db->createCommand()
//                                ->alterColumn(
//                                    $object->column_properties_table_name,
//                                    $model->getOldAttribute('key'),
//                                    $new_type
//                                )->execute();
//                            if ($object->object_class == Form::className()) {
//                                $submissionObject = Object::getForClass(Submission::className());
//                                Yii::$app->db->createCommand()
//                                    ->renameColumn(
//                                        $submissionObject->column_properties_table_name,
//                                        $model->getOldAttribute('key'),
//                                        $new_type
//                                    )->execute();
//                            }
//                        }
//                    }
//                }
            }

            $save_result = $model->save();
            if ($save_result) {
                //$this->runAction('save-info');
                Yii::$app->session->setFlash('success', Yii::t('core', 'Record has been saved'));
                $returnUrl = Yii::$app->request->get(
                    'returnUrl',
                    [
                        '/property/group',
                        'id' => $property_group_id,
                    ]
                );
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/property/edit-property',
                                'property_group_id' => $property_group_id,
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    '/property/edit-property',
                                    'id' => $model->id,
                                    'property_group_id' => $model->property_group_id,
                                    'returnUrl' => $returnUrl,
                                ]
                            )
                        );
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('core', 'Cannot save data'));
            }
        }

        $searchModel = new PropertyStaticValue();

        $searchModel->property_id = $model->id;
        $dataProvider = $searchModel->search($_GET);

        return $this->render(
            'edit-property',
            [
                'model' => $model,
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'fieldinterpretParentId' => 0,
                'object' => $object,
            ]
        );
    }

    public function actionEditStaticValue($property_id, $id = null)
    {
        if ($id === null) {
            $model = new PropertyStaticValue();
        } else {
            $model = PropertyStaticValue::findOne($id);
        }
        //$object = Object::getForClass(PropertyStaticValue::className());
        $object = Yii::$app->entity->findOne('class', PropertyStaticValue::className());
        $model->property_id = $property_id;
        $post = \Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            $save_result = $model->save();
            if ($save_result) {
                //$this->checkDoubledSlugs($model->slug);
                //$this->runAction('save-info');
                Yii::$app->session->setFlash('success', Yii::t('core', 'Record has been saved'));
                $returnUrl = Yii::$app->request->get(
                    'returnUrl',
                    [
                        '/property/edit-property',
                        'id' => $model->property_id,
                        'property_group_id' => $model->property->property_group_id,
                    ]
                );
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/property/edit-static-value',
                                'property_id' => $model->property_id,
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    '/property/edit-static-value',
                                    'id' => $model->id,
                                    'property_id' => $model->property_id,
                                    'returnUrl' => $returnUrl,
                                ]
                            )
                        );
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('core', 'Cannot save data'));
            }
        }
        return $this->render(
            'edit-static-value',
            [
                'model' => $model,
                'object' => $object,
            ]
        );
    }


    public function actionAddStaticValue($key, $value, $returnUrl, $objectId = null, $objectModelId = null)
    {
        $model = new PropertyStaticValue();
        /** @var Property $property */
        $property = Property::findOne(['key'=>$key]);
        if (is_null($property)) {
            throw new NotFoundHttpException;
        }
        $model->property_id = $property->id;
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                //$this->checkDoubledSlugs($model->slug);
                if (!is_null($objectId) && !is_null($objectModelId)) {
                    if ($property->multiple == 0) {
                        $propertyStaticValueIds = PropertyStaticValue::find()
                            ->select('id')
                            ->where(['property_id' => $property->id])
                            ->column();
                        EntityStaticValue::deleteAll(
                            [
                                'entity_id' => $objectId,
                                'entity_model_id' => $objectModelId,
                                'property_static_value_id' => $propertyStaticValueIds,
                            ]
                        );
                    }
                    $EntityStaticValue = new EntityStaticValue;
                    $EntityStaticValue->attributes = [
                        'entity_id' => $objectId,
                        'entity_model_id' => $objectModelId,
                        'property_static_value_id' => $model->id,
                    ];
                    $EntityStaticValue->save();
                }
                return $this->redirect($returnUrl);
            }
        } elseif ($value !== "") {
            $model->name = $value;
            $model->value = $value;
            $model->slug = OopHelper::createSlug($value);
            $model->position = 0;
        }
        return $this->renderAjax('ajax-static-value', ['model' => $model]);
    }


    public function actionDeleteStaticValue($id, $property_id, $property_group_id)
    {
        /** @var PropertyStaticValue $model */
        $model = PropertyStaticValue::findOne($id);
        if (is_null($model)) {
            throw new NotFoundHttpException;
        }
        $model->delete();
        Yii::$app->session->setFlash('danger', Yii::t('core', 'Object removed'));
        return $this->redirect(
            Url::to(
                [
                    '/property/edit-property',
                    'id'=>$property_id,
                    'property_group_id'=>$property_group_id
                ]
            )
        );
    }

    public function actionDeleteProperty($id, $property_group_id)
    {
        /** @var Property $model */
        $model = Property::findOne($id);
        if (is_null($model)) {
            throw new NotFoundHttpException;
        }
        $model->delete();
        Yii::$app->session->setFlash('danger', Yii::t('core', 'Object removed'));
        return $this->redirect(
            Url::to(
                [
                    '/property/group',
                    'id'=>$property_group_id,
                ]
            )
        );
    }

    public function actionRemoveAllProperties($group_id)
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = Property::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $item->delete();
            }
        }
        return $this->redirect(['group', 'id' => $group_id]);
    }

    public function actionDeleteGroup($id)
    {
        /** @var PropertyGroup $model */
        $model = PropertyGroup::findOne($id);
        if (is_null($model)) {
            throw new NotFoundHttpException;
        }
        $model->delete();
        Yii::$app->session->setFlash('danger', Yii::t('core', 'Object removed'));
        return $this->redirect(
            Url::to(
                [
                    '/property/index',
                ]
            )
        );
    }

    public function actionRemoveAllGroups()
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = PropertyGroup::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $properties = Property::find()
                    ->where(['property_group_id' => $item->id])
                    ->all();
                foreach ($properties as $prop) {
                    $prop->delete();
                }
                $item->delete();
            }
        }

        return $this->redirect(['index']);
    }

    public function actionHandlers()
    {
        return $this->render('handlers');
    }

    public function actionAjaxGroup()
    {
        if (Yii::$app->request->isAjax) {
            //Yii::$app->response->format = Response::FORMAT_JSON;

            $post = Yii::$app->request->post();
            if (
                //(isset($post['action'])) && ($post['action'] == 'add') &&
                ($operation = isset($post['operation']) ? $post['operation'] : '') &&
                ($value = isset($post['value']) ? $post['value'] : '') &&
                ($id = isset($post['id']) ? $post['id'] : '') &&
                ($modelName = isset($post['model']) ? $post['model'] : '')
            ) {
                //$class = Entity::findOne($modelName, 'name')['class'];
                $class = Yii::$app->entity->getFieldByName('class', $modelName);
                if (null !== $model = $class::findOne($id)) {
                    $runtimePost = [
                        $operation => [$modelName => (integer) $value]
                    ];
                    //var_dump($runtimePost);
                    $model->saveProperties($runtimePost);

                    $entityPropertyGroups = EntityPropertyGroup::getForModel($model);

                    $addedPropertyGroupsIds = [];
                    foreach ($entityPropertyGroups as $opg) {
                        $addedPropertyGroupsIds[] = $opg->property_group_id;
                    }
                    $restPg = (new Query())
                        ->select('id, name')
                        ->from(PropertyGroup::tableName())
                        ->where([
                            'entity_id' => $model->getEntityField('id'),
                        ])
                        ->andWhere([
                            'not in', 'id', $addedPropertyGroupsIds,
                        ])
                        ->orderBy('position')
                        ->all();

                    $propertyGroupsToAdd = ArrayHelper::map($restPg, 'id', 'name');

                    //var_dump($model);exit;

                    return $this->renderPartial('@common/components/properties/widgets/views/_widget-content', [
                        'model' => $model,
                        'entity_property_groups' => $entityPropertyGroups,
                        'property_groups_to_add' => $propertyGroupsToAdd,
                        'form' => ActiveForm::begin(),
                    ]);
                }
            };

        }

        throw new \Exception();
    }
}
