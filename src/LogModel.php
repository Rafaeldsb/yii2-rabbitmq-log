<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 08/07/19
 * Time: 20:51
 */

namespace rafaeldsb\rabbitmqlog;


use yii\base\BaseObject;

class LogModel extends BaseObject
{
    public $model;
    public $primaryKey;
    public $oldValues = [];
    public $newValues = [];
    public $operation;
}
