<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 08/07/19
 * Time: 20:05
 */

namespace rafaeldsb\rabbitmqlog;


interface ISecureLogging extends ILogging
{
    /**
     * Retorna as propriedades que são seguras, elas não serão processadas no log
     * @return array
     */
    function secureProperties();
}
