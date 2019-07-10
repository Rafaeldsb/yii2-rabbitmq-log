<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 07/07/19
 * Time: 20:24
 */

namespace rafaeldsb\rabbitmqlog;


use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use yii\base\Application;
use yii\base\Component;
use yii\base\Controller;
use yii\base\Event;
use yii\base\InvalidArgumentException;

use yii\db\ActiveRecord;

class RabbitMQ extends Component
{
    const ILoggingClass = 'common\components\rabbitmq\ILogging';

    public $host;
    public $port;
    public $user;
    public $password;
    public $vHost = 'EnterpriseLog';
    public $queues = [
        [
            'queue' => 'ApplicationLog',
            'passive' => false,
            'durable' => true,
            'exclusive' => false,
            'auto_delete' => false
        ]
    ];

    public $defaultRoutingKey = 'ApplicationLog';

    public $logClass = 'rafaeldsb\\rabbitmqlog\\Log';

    /** @var AMQPStreamConnection */
    protected $connection;

    /** @var AMQPChannel */
    protected $channel;

    /**
     * @var Log
     */
    protected $log;

    public function init()
    {
        $this->log = new $this->logClass();
        $this->manageEvents();
    }

    protected function manageEvents() {
        Event::on(self::ILoggingClass, ActiveRecord::EVENT_BEFORE_INSERT, [$this->log, 'afterUpdate']);
        Event::on(self::ILoggingClass, ActiveRecord::EVENT_BEFORE_UPDATE, [$this->log, 'afterSave']);
        Event::on(self::ILoggingClass, ActiveRecord::EVENT_AFTER_UPDATE, [$this->log, 'afterUpdate']);
        Event::on(self::ILoggingClass, ActiveRecord::EVENT_AFTER_INSERT, [$this->log, 'afterSave']);
        Event::on(Controller::className(), Controller::EVENT_BEFORE_ACTION, [$this->log, 'beforeAction']);
        Event::on(Controller::className(), Controller::EVENT_AFTER_ACTION, [$this->log, 'afterAction']);
        \Yii::$app->on(Application::EVENT_AFTER_REQUEST, [$this->log, 'afterRequest']);
        \Yii::$app->on(Application::EVENT_AFTER_REQUEST, [$this, 'sendLog']);
    }

    protected function connect() {
        $this->connection = new AMQPStreamConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->vHost
        );
        $this->channel = $this->connection->channel();
        $this->prepareQueues();
    }

    protected function disconnect() {
        $this->channel->close();
        $this->connection->close();
    }

    protected function prepareQueues() {
        foreach ($this->queues as $queue) {
            $this->channel->queue_declare(
                $queue['queue'],
                $queue['passive'],
                $queue['durable'],
                $queue['exclusive'],
                $queue['auto_delete']
            );
        }
    }

    /**
     * @param object | array $object
     * @return AMQPMessage
     */
    protected function processMessage($object) {
        if($msg = json_encode($object))
            return new AMQPMessage($msg);

        throw new InvalidArgumentException("Parâmetro deve ser um objeto ou array serializável");
    }

    protected function getRoutingKey($routingKey = null) {
        if($routingKey)
            return $routingKey;
        return $this->defaultRoutingKey;
    }

    public function send($msg, $routingKey = null) {
        $this->connect();

        $msg = $this->processMessage($msg);
        $routingKey = $this->getRoutingKey($routingKey);
        $this->channel->basic_publish($msg, '', $routingKey);

        $this->disconnect();
    }

    public function sendLog() {
        $this->send($this->log);
    }

}
