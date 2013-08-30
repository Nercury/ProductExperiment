<?php

namespace Evispa\ObjectMigration\Migration;

/**
 * Description of MigrationMethods
 *
 * @author nerijus
 */
class MigrationMethods
{
    /**
     * @var \Evispa\ObjectMigration\Action\MigrationActionInterface[]
     */
    public $from = array();

    /**
     * @var \Evispa\ObjectMigration\Action\MigrationActionInterface[]
     */
    public $to = array();
}