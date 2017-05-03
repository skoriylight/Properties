<?
namespace common\components\properties\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\base\ErrorException;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

Class Events extends Behavior{
    
    public function attach($owner){
        parent::attach($owner);
        $owner->on(ActiveRecord::EVENT_BEFORE_INSERT,[$this,'onBeforeSave']);
        $owner->on(ActiveRecord::EVENT_BEFORE_UPDATE,[$this,'onBeforeSave']);
    }

    public function onBeforeSave($event){
        
        $model = $this->owner;
        if($model->property_handler_id == 2)
        {
            $this->owner->type = 0;
            $this->owner->is_range = 0;
            //$this->owner->multiple = 1;
        }
        else
        {
            $this->owner->type = 1;
            $this->owner->is_range = 1;
            //$this->owner->multiple = 0;
        }
       
        
    }

}