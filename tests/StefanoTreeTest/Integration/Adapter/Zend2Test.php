<?php
namespace StefanoTreeTest\Integration\Adapter;

use StefanoTree\NestedSet\Adapter\AdapterInterface as TreeAdapterInterface;
use StefanoTree\NestedSet\Adapter\Zend2 as NestedSetAdapter;
use StefanoTree\NestedSet\Options;
use Zend\Db\Adapter\Adapter as DbAdapter;


class Zend2Test
    extends AdapterTestAbstract
{
    /**
     * @return TreeAdapterInterface
     */
    protected function getAdapter()
    {
        $dbAdapter = new DbAdapter(array(
            'driver' => 'Pdo_' . ucfirst(TEST_STEFANO_DB_ADAPTER),
            'hostname' => TEST_STEFANO_DB_HOSTNAME,
            'database' => TEST_STEFANO_DB_DB_NAME,
            'username' => TEST_STEFANO_DB_USER,
            'password' => TEST_STEFANO_DB_PASSWORD
        ));

        $options = new Options(array(
            'tableName' => 'tree_traversal',
            'idColumnName' => 'tree_traversal_id',
        ));

        if ('pgsql' == TEST_STEFANO_DB_ADAPTER) {
            $options->setSequenceName('tree_traversal_tree_traversal_id_seq');
        }

        return new NestedSetAdapter($options, $dbAdapter);
    }
}
