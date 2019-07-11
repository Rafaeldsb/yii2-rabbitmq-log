<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 08/07/19
 * Time: 20:14
 */

namespace rafaeldsb\rabbitmqlog;


use yii\base\ActionEvent;
use yii\base\BaseObject;
use yii\base\ModelEvent;
use yii\db\AfterSaveEvent;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

class Log extends BaseObject implements ILog
{
    public $application;
    public $type;
    public $isSuccess;
    public $statusCode;
    public $statusText;
    public $errorMessage;
    public $user;
    public $action;
    public $request;
    public $response;
    public $header;
    public $models;

    public function init()
    {
        parent::init();

        $this->application = \Yii::$app->id;
    }

    function beforeUpdate(ModelEvent $event)
    {
        // TODO: Implement beforeUpdate() method.
    }

    function beforeSave(ModelEvent $event)
    {
        // TODO: Implement beforeSave() method.
    }

    public function afterUpdate(AfterSaveEvent $event) {
        $this->addModel($event, false);
    }

    public function afterSave(AfterSaveEvent $event) {
        $this->addModel($event, true);
    }

    protected function addModel(AfterSaveEvent $event, $isInsert) {
        $model = $event->sender;
        $secureProperties = [];

        $logModel = new LogModel([
            'operation' => $isInsert ? 'insert' : 'update',
            'model' => $model::className(),
            'primaryKey' => $model->primaryKey
        ]);

        if($model instanceof ISecureLogging)
            $secureProperties = $model->secureProperties();

        foreach ($event->changedAttributes as $attribute => $value) {

            if(in_array($attribute, $secureProperties))
                continue;

            $logModel->oldValues[$attribute] = $value;
            $logModel->newValues[$attribute] = $model->$attribute;
        }

        $this->models[] = $logModel;
    }

    public function beforeAction(ActionEvent $event) {
        $request = \Yii::$app->request;
        $user = \Yii::$app->user->identity;

        if($user)
            $this->user = $user->getId();
        $this->request = $request;
        $this->header = $request->headers;

        $this->action = $event->action->uniqueId;
    }

    /**
     * @param ActionEvent $event
     */
    public function afterAction(ActionEvent $event) {
        $response = \Yii::$app->response;

        $this->isSuccess = $response->isSuccessful;
        $this->statusCode = $response->statusCode;
        $this->statusText = $response->statusText;

        if(!$this->isSuccess) {
            $this->errorMessage = $event->result;
        }

    }

    public function afterRequest($event) {

    }

}
