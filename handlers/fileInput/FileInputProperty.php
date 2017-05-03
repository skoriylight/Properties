<?php

namespace common\components\properties\handlers\fileInput;

use Yii;
use common\components\properties\models\AbstractPropertyEavModel;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class FileInputProperty extends \common\components\properties\handlers\AbstractHandler
{
    protected $widgetClass = '\common\components\properties\handlers\fileInput\FileInputPropertyWidget';
    protected $uploadDir = '@webroot/upload/files/';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->uploadDir = Yii::$app->getModule('core')->visitorsFileUploadPath;

        $this->uploadDir = FileHelper::normalizePath( Yii::getAlias($this->uploadDir));
        $this->additionalRenderData['uploadDir'] = $this->uploadDir;
    }

    /**
     * @param \common\components\properties\models\Property $property
     * @return bool
     */
    public function changePropertyType(\common\components\properties\models\Property &$property)
    {
        if (1 !== intval($property->is_eav)) {
            $property->is_eav = 1;
            $property->is_column_type_stored = 0;
            $property->has_static_values = 0;

            return true;
        }

        return parent::changePropertyType($property);
    }

    /**
     * @param \common\components\properties\models\Property $property
     * @param string $formProperties
     * @param array $values
     * @return array
     */
    public function processValues(\common\components\properties\models\Property $property, $formProperties = '', $values = [])
    {
        $values = array_filter($values, function($v){
            return !empty($v);
        });

        $_fi_hack = [];
        $files = array_filter(UploadedFile::getInstancesByName($formProperties.'['.$property->key.']'),
            function ($v) use ($_fi_hack)
            {
                /** @var UploadedFile $v */
                if (in_array($v->name, $_fi_hack)) {
                    return false;
                }
                $_fi_hack[] = $v->name;
                return true;
            });
        /** @var UploadedFile $file */
        foreach ($files as $file) {
            $fileName = $file->baseName . '.' . $file->extension;
            if (false === \Yii::$app->getModule('core')->overwriteUploadedFiles && is_file($this->uploadDir . DIRECTORY_SEPARATOR . $fileName)) {
                $fileName = $file->baseName . substr(md5($fileName . microtime()), 0, 6) . '.' . $file->extension;
            }

            if ($file->saveAs($this->uploadDir . DIRECTORY_SEPARATOR . $fileName)) {
                $values[] = $fileName;
            }
        }

        return array_unique($values);
    }

    /**
     * @param array $params
     * @return false|int
     * @throws \Exception
     */
    public function actionDelete($params = [])
    {
        /** @var \common\components\properties\models\Property $property */
        $property = $params['property'];
        /** @var \yii\db\ActiveRecord $modelObject */
        $modelObject = new $params['model_name']();
        $modelId = $params['model_id'];

        /** @var \app\models\Object $object */
        $object = $params['entity_id'];

        AbstractPropertyEavModel::setTableName($object->eav_table_name);

        /** @var AbstractPropertyEavModel $model */
        $model = AbstractPropertyEavModel::find()
            ->where([
                'property_group_id' => $property->property_group_id,
                'key' => $property->key,
                'entity_model_id' => $modelId,
                'value' => \Yii::$app->request->post('value')
            ])->one();

        $result = false;
        if (null !== $model) {
            if (true === \Yii::$app->getModule('core')->removeUploadedFiles) {
                @unlink($this->uploadDir.DIRECTORY_SEPARATOR.$model->value);
            }
            $result = $model->delete();
        }
        AbstractPropertyEavModel::setTableName(null);

        return $result;
    }

    /**
     * @param array $params
     * @return string
     */
    public function actionUpload($params = [])
    {
        $result = [];

        /** @var \common\components\properties\models\Property $property */
        $property = $params['property'];
        /** @var \yii\db\ActiveRecord $modelObject */
        $modelObject = new $params['model_name']();
        $modelId = $params['model_id'];

        /** @var \app\models\Object $object */
        $object = $params['entity_id'];

        $formProperties = 'Properties_'. $modelObject->formName() .'_'. $modelId;

        $modelEav = new AbstractPropertyEavModel();
        $modelEav::setTableName($object->eav_table_name);
        $modelEav->property_group_id = $property->property_group_id;
        $modelEav->key = $property->key;
        $modelEav->entity_model_id = $modelId;

        $files = UploadedFile::getInstancesByName($formProperties.'['.$property->key.']');
        foreach ($files as $file) {
            $fileName = $file->baseName . '.' . $file->extension;
            $item = [
                'originalFileName' => $fileName,
                'fileName' => $fileName,
            ];
            if (false === \Yii::$app->getModule('core')->overwriteUploadedFiles && is_file($this->uploadDir . DIRECTORY_SEPARATOR . $fileName)) {
                $fileName = $file->baseName . substr(md5($fileName . microtime()), 0, 6) . '.' . $file->extension;
            }

            if ($file->saveAs($this->uploadDir . DIRECTORY_SEPARATOR . $fileName)) {
                $modelEav->isNewRecord = true;
                $modelEav->value = $fileName;
                $modelEav->save();
                $item['fileName'] = $fileName;
            }
            $result[$item['originalFileName']] = $item;
        }
        AbstractPropertyEavModel::setTableName(null);

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return $result;
    }

    public function actionDownloadFile($params = [])
    {
        $filePath = $this->uploadDir . DIRECTORY_SEPARATOR . Yii::$app->request->get('fileName', null);
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException();
        }
        return Yii::$app->response->sendFile($filePath);
    }

    public function actionShowFile($params = [])
    {
        $filePath = $this->uploadDir . DIRECTORY_SEPARATOR . Yii::$app->request->get('fileName', null);
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException();
        }
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->setDefault('Content-Type', FileHelper::getMimeTypeByExtension($filePath));
        return file_get_contents($filePath);
    }
}
