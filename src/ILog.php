<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 10/07/19
 * Time: 19:24
 */

namespace rafaeldsb\rabbitmqlog;


use yii\base\ActionEvent;
use yii\base\ModelEvent;
use yii\db\AfterSaveEvent;

interface ILog
{
    function beforeUpdate(ModelEvent $event);
    function beforeSave(ModelEvent $event);
    function afterUpdate(AfterSaveEvent $event);
    function afterSave(AfterSaveEvent $event);
    function beforeAction(ActionEvent $event);
    function afterAction(ActionEvent $event);
    function afterSend($event);
}
