# yii2-rabbitmq-log
Geração de logs estruturados e envio para o rabbitmq para projetos baseado no yii2

## Instalação

```composer require rafaeldsb/yii2-rabbitmq-log```

## Configuração
Edite o arquivo de configuração e acrescente o componente e adicione ele no bootstrap da aplicação:
```php
return [
    ...
    'components' => [
        ...
        'rabbitmq' => [
            'class' => \rafaeldsb\rabbitmqlog\RabbitMQ::className(),
            'host' => 'localhost',
            'port' => 5672,
            'user' => 'SeuUsuarioRabbitMQ',
            'password' => 'SuaSenhaRabbitMQ',
            'vHost' => 'SeuVHostRabbitmq',
            'queues' => [
                [
                    'queue' => 'NomeDaSuaFila',
                    'passive' => false,
                    'durable' => true,
                    'exclusive' => false,
                    'auto_delete' => false
                ]
            ],
            'defaultRoutingKey' => 'RoutingKey',
            'logClass' => 'SuaClasseCustomizadaDeLogs' // Opcional
        ]
    ],
    'bootstrap' => [
        'rabbitmq'
    ],
    ...
]
```
