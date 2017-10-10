<?php

declare(strict_types=1);

namespace StefanoTreeTest\Integration;

use StefanoTree\Exception\InvalidArgumentException;
use StefanoTree\NestedSet as TreeAdapter;
use StefanoTreeTest\IntegrationTestCase;

abstract class AbstractTest extends IntegrationTestCase
{
    /**
     * @var TreeAdapter
     */
    protected $treeAdapter;

    protected function setUp()
    {
        $this->treeAdapter = $this->getTreeAdapter();

        parent::setUp();
    }

    protected function tearDown()
    {
        $this->treeAdapter = null;
        parent::tearDown();
    }

    /**
     * @return TreeAdapter
     */
    abstract protected function getTreeAdapter();

    protected function getDataSet()
    {
        switch ($this->getName()) {
            case 'testCreateRootNode':
            case 'testCreateRootNodeWithCustomData':
            case 'testGetRootNodeRootDoesNotExist':
                return $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/initEmptyDataSet.xml');
            default:
                return $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/initDataSet.xml');
        }
    }

    public function testCreateRootNode()
    {
        $newId = $this->treeAdapter
            ->createRootNode();
        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testCreateRootNode.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertEquals(1, $newId);
    }

    public function testCreateRootNodeWithCustomData()
    {
        $newId = $this->treeAdapter
            ->createRootNode(array('name' => 'This is root node'));
        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testCreateRootNodeWithCustomData.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertEquals(1, $newId);
    }

    public function testCreateRootRootAlreadyExist()
    {
        $this->expectException('\StefanoTree\Exception\RootNodeAlreadyExistException');
        $this->expectExceptionMessage('Root node already exist');

        $this->treeAdapter
             ->createRootNode();
        $this->treeAdapter
            ->createRootNode();
    }

    public function testGetNode()
    {
        $expectedNodeData = array(
            'tree_traversal_id' => '12',
            'name' => null,
            'lft' => '18',
            'rgt' => '29',
            'parent_id' => '6',
            'level' => '3',
        );

        $nodeData = $this->treeAdapter
                         ->getNode(12);

        $this->assertEquals($expectedNodeData, $nodeData);
        $this->assertNull($this->treeAdapter->getNode(123456789));
    }

    public function testAddNodeTargetNodeDoesNotExist()
    {
        //test
        $return = $this->treeAdapter
                       ->addNode(123456789, array(), TreeAdapter::PLACEMENT_BOTTOM);
        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/initDataSetWithIds.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertNull($return);
    }

    public function testCreateNodePlacementStrategyDoesNotExists()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown placement "unknown-placement"');

        $this->treeAdapter
            ->addNode(1, array(), 'unknown-placement');
    }

    public function testAddNodePlacementBottom()
    {
        //test
        $return = $this->treeAdapter
                       ->addNode(1, array(), TreeAdapter::PLACEMENT_BOTTOM);
        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/initDataSetWithIds.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertNull($return);

        //test 1
        $lastGeneratedValue = $this->treeAdapter
                                   ->addNode(12, array(), TreeAdapter::PLACEMENT_BOTTOM);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testAddNodePlacementBottom-1.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertEquals(26, $lastGeneratedValue);

        //test 2 with data
        $data = array(
            'name' => 'ahoj',
        );

        $lastGeneratedValue = $this->treeAdapter
                                   ->addNode(19, $data, TreeAdapter::PLACEMENT_BOTTOM);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testAddNodePlacementBottom-2.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertEquals(27, $lastGeneratedValue);
    }

    public function testAddNodePlacementTop()
    {
        //test
        $return = $this->treeAdapter
                       ->addNode(1, array(), TreeAdapter::PLACEMENT_TOP);
        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/initDataSetWithIds.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertNull($return);

        //test 1
        $lastGeneratedValue = $this->treeAdapter
                                   ->addNode(16, array(), TreeAdapter::PLACEMENT_TOP);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testAddNodePlacementTop-1.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertEquals(26, $lastGeneratedValue);

        //test 2 with data
        $data = array(
            'name' => 'ahoj',
        );
        $lastGeneratedValue = $this->treeAdapter
                                   ->addNode(3, $data, TreeAdapter::PLACEMENT_TOP);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testAddNodePlacementTop-2.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertEquals(27, $lastGeneratedValue);
    }

    public function testAddNodePlacementChildBottom()
    {
        //test 1
        $lastGeneratedValue = $this->treeAdapter
                                   ->addNode(21, array(), TreeAdapter::PLACEMENT_CHILD_BOTTOM);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testAddNodePlacementChildBottom-1.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertEquals(26, $lastGeneratedValue);

        //test 2 with data
        $data = array(
            'name' => 'ahoj',
        );
        $lastGeneratedValue = $this->treeAdapter
                                   ->addNode(4, $data, TreeAdapter::PLACEMENT_CHILD_BOTTOM);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testAddNodePlacementChildBottom-2.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertEquals(27, $lastGeneratedValue);
    }

    public function testAddNodePlacementChildTopDefaultPlacement()
    {
        //test 1
        $lastGeneratedValue = $this->treeAdapter
                                   ->addNode(4);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testAddNodePlacementChildTop-1.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertEquals(26, $lastGeneratedValue);

        //test 2 with data
        $data = array(
            'name' => 'ahoj',
        );
        $lastGeneratedValue = $this->treeAdapter
                                   ->addNode(10, $data);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testAddNodePlacementChildTop-2.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertEquals(27, $lastGeneratedValue);
    }

    public function testDeleteBranch()
    {
        //test 2
        $return = $this->treeAdapter
                       ->deleteBranch(123456789);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/initDataSetWithIds.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet, 'Not Exist Branch');
        $this->assertFalse($return);

        //test 3
        $return = $this->treeAdapter
                       ->deleteBranch(6);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testDeleteBranch.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertTrue($return);
    }

    public function testMoveUnmovableNode()
    {
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/initDataSetWithIds.xml');
        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));

        //test 1
        $return = $this->treeAdapter
                       ->moveNode(1, 12);

        $this->assertDataSetsEqual($expectedDataSet, $dataSet, 'Target node is inside source node');
        $this->assertFalse($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(10, 10);

        $this->assertDataSetsEqual($expectedDataSet, $dataSet, 'Target node and source node are same');
        $this->assertFalse($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(5, 123456);

        $this->assertDataSetsEqual($expectedDataSet, $dataSet, 'Target node does not exist');
        $this->assertFalse($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(123456, 6);

        $this->assertDataSetsEqual($expectedDataSet, $dataSet, 'Source node does not exist');
        $this->assertFalse($return);
    }

    public function testMoveNodePlacementStrategyDoesNotExists()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown placement "unknown-placement"');

        $this->treeAdapter
            ->moveNode(11, 1, 'unknown-placement');
    }

    public function testMoveNodePlacementBottom()
    {
        //test
        $return = $this->treeAdapter
                       ->moveNode(11, 1, TreeAdapter::PLACEMENT_BOTTOM);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/initDataSetWithIds.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet, 'Root node cannot have sibling');
        $this->assertFalse($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(3, 2, TreeAdapter::PLACEMENT_BOTTOM);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/initDataSetWithIds.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet, 'Source node is in required position');
        $this->assertTrue($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(14, 18, TreeAdapter::PLACEMENT_BOTTOM);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testMoveNodePlacementBottom-1.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertTrue($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(16, 7, TreeAdapter::PLACEMENT_BOTTOM);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testMoveNodePlacementBottom-2.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertTrue($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(14, 3, TreeAdapter::PLACEMENT_BOTTOM);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testMoveNodePlacementBottom-3.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertTrue($return);
    }

    public function testMoveNodePlacementTop()
    {
        //test
        $return = $this->treeAdapter
                       ->moveNode(17, 1, TreeAdapter::PLACEMENT_TOP);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/initDataSetWithIds.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet, 'Root node cannot have sibling');
        $this->assertFalse($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(3, 4, TreeAdapter::PLACEMENT_TOP);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/initDataSetWithIds.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet, 'Source node is in required position');
        $this->assertTrue($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(19, 12, TreeAdapter::PLACEMENT_TOP);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testMoveNodePlacementTop-1.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertTrue($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(10, 18, TreeAdapter::PLACEMENT_TOP);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testMoveNodePlacementTop-2.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertTrue($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(21, 6, TreeAdapter::PLACEMENT_TOP);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testMoveNodePlacementTop-3.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertTrue($return);
    }

    public function testMoveNodePlacementChildBottom()
    {
        //test
        $return = $this->treeAdapter
                       ->moveNode(22, 18, TreeAdapter::PLACEMENT_CHILD_BOTTOM);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/initDataSetWithIds.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet, 'Source node is in required position');
        $this->assertTrue($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(9, 12, TreeAdapter::PLACEMENT_CHILD_BOTTOM);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testMoveNodePlacementChildBottom-1.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertTrue($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(10, 3, TreeAdapter::PLACEMENT_CHILD_BOTTOM);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testMoveNodePlacementChildBottom-2.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertTrue($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(21, 12, TreeAdapter::PLACEMENT_CHILD_BOTTOM);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testMoveNodePlacementChildBottom-3.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertTrue($return);
    }

    public function testMoveNodePlacementChildTopDefaultPlacement()
    {
        //test
        $return = $this->treeAdapter
                       ->moveNode(21, 18);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/initDataSetWithIds.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet, 'Source node is in required position');
        $this->assertTrue($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(9, 21);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testMoveNodePlacementChildTop-1.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertTrue($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(16, 3);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testMoveNodePlacementChildTop-2.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertTrue($return);

        //test
        $return = $this->treeAdapter
                       ->moveNode(18, 3);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testMoveNodePlacementChildTop-3.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertTrue($return);
    }

    public function testGetPathReturnEmptyArrayIfNodeDoesNotExist()
    {
        $return = $this->treeAdapter
            ->getPath(123456789);
        $this->assertEquals(array(), $return);
    }

    public function testGetPathReturnEmptyArrayIfNodeExistButHasNoPath()
    {
        $return = $this->treeAdapter
            ->getPath(1, 0, true);
        $this->assertEquals(array(), $return);
    }

    public function testGetPath()
    {
        //test
        $return = $this->treeAdapter
                       ->getPath(6);
        $expected = array(
            array(
                'tree_traversal_id' => '1',
                'name' => null,
                'lft' => '1',
                'rgt' => '50',
                'parent_id' => null,
                'level' => '0',
            ),
            array(
                'tree_traversal_id' => '3',
                'name' => null,
                'lft' => '16',
                'rgt' => '35',
                'parent_id' => '1',
                'level' => '1',
            ),
            array(
                'tree_traversal_id' => '6',
                'name' => null,
                'lft' => '17',
                'rgt' => '32',
                'parent_id' => '3',
                'level' => '2',
            ),
        );
        $this->assertEquals($expected, $return);

        //test
        $return = $this->treeAdapter
                       ->getPath(6, 1);
        $expected = array(
            array(
                'tree_traversal_id' => '3',
                'name' => null,
                'lft' => '16',
                'rgt' => '35',
                'parent_id' => '1',
                'level' => '1',
            ),
            array(
                'tree_traversal_id' => '6',
                'name' => null,
                'lft' => '17',
                'rgt' => '32',
                'parent_id' => '3',
                'level' => '2',
            ),
        );
        $this->assertEquals($expected, $return);

        //test
        $return = $this->treeAdapter
                       ->getPath(6, 0, true);
        $expected = array(
            array(
                'tree_traversal_id' => '1',
                'name' => null,
                'lft' => '1',
                'rgt' => '50',
                'parent_id' => null,
                'level' => '0',
            ),
            array(
                'tree_traversal_id' => '3',
                'name' => null,
                'lft' => '16',
                'rgt' => '35',
                'parent_id' => '1',
                'level' => '1',
            ),
        );
        $this->assertEquals($expected, $return);
    }

    public function testGetDescendantsReturnEmptyArrayIfNodeDoesNotExist()
    {
        $return = $this->treeAdapter
            ->getDescendants(123456789);
        $this->assertEquals(array(), $return);
    }

    public function testGetDescendantsReturnEmptyArrayNodeDoesNotHaveDescendants()
    {
        $return = $this->treeAdapter
            ->getDescendants(8, 1);
        $this->assertEquals(array(), $return);
    }

    public function testGetDescendants()
    {
        //test whole branche
        $return = $this->treeAdapter
                       ->getDescendants(21);
        $expected = array(
            array(
                'tree_traversal_id' => '21',
                'name' => null,
                'lft' => '20',
                'rgt' => '25',
                'parent_id' => '18',
                'level' => '5',
            ),
            array(
                'tree_traversal_id' => '24',
                'name' => null,
                'lft' => '21',
                'rgt' => '22',
                'parent_id' => '21',
                'level' => '6',
            ),
            array(
                'tree_traversal_id' => '25',
                'name' => null,
                'lft' => '23',
                'rgt' => '24',
                'parent_id' => '21',
                'level' => '6',
            ),
        );
        $this->assertEquals($expected, $return);

        //test different start node
        $return = $this->treeAdapter
                       ->getDescendants(6, 3);
        $expected = array(
            array(
                'tree_traversal_id' => '21',
                'name' => null,
                'lft' => '20',
                'rgt' => '25',
                'parent_id' => '18',
                'level' => '5',
            ),
            array(
                'tree_traversal_id' => '24',
                'name' => null,
                'lft' => '21',
                'rgt' => '22',
                'parent_id' => '21',
                'level' => '6',
            ),
            array(
                'tree_traversal_id' => '25',
                'name' => null,
                'lft' => '23',
                'rgt' => '24',
                'parent_id' => '21',
                'level' => '6',
            ),
            array(
                'tree_traversal_id' => '22',
                'name' => null,
                'lft' => '26',
                'rgt' => '27',
                'parent_id' => '18',
                'level' => '5',
            ),
        );
        $this->assertEquals($expected, $return);

        //test custom levels
        $return = $this->treeAdapter
                       ->getDescendants(18, 0, 2);
        $expected = array(
            array(
                'tree_traversal_id' => '18',
                'name' => null,
                'lft' => '19',
                'rgt' => '28',
                'parent_id' => '12',
                'level' => '4',
            ),
            array(
                'tree_traversal_id' => '21',
                'name' => null,
                'lft' => '20',
                'rgt' => '25',
                'parent_id' => '18',
                'level' => '5',
            ),
            array(
                'tree_traversal_id' => '22',
                'name' => null,
                'lft' => '26',
                'rgt' => '27',
                'parent_id' => '18',
                'level' => '5',
            ),
        );
        $this->assertEquals($expected, $return);

        //test exclude node
        $return = $this->treeAdapter
                       ->getDescendants(12, 0, null, 21);
        $expected = array(
            array(
                'tree_traversal_id' => '12',
                'name' => null,
                'lft' => '18',
                'rgt' => '29',
                'parent_id' => '6',
                'level' => '3',
            ),
            array(
                'tree_traversal_id' => '18',
                'name' => null,
                'lft' => '19',
                'rgt' => '28',
                'parent_id' => '12',
                'level' => '4',
            ),
            array(
                'tree_traversal_id' => '22',
                'name' => null,
                'lft' => '26',
                'rgt' => '27',
                'parent_id' => '18',
                'level' => '5',
            ),
        );
        $this->assertEquals($expected, $return);
    }

    public function testGetChildrenReturnEmptyArrayIfNodeDoesNotExist()
    {
        $return = $this->treeAdapter
            ->getChildren(123456789);
        $this->assertEquals(array(), $return);
    }

    public function testGetChildrenReturnEmptyArrayIfNodeDoesNotHaveChildren()
    {
        $return = $this->treeAdapter
            ->getChildren(8);
        $this->assertEquals(array(), $return);
    }

    public function testGetChildren()
    {
        //test exclude node
        $return = $this->treeAdapter
                       ->getChildren(18);
        $expected = array(
            array(
                'tree_traversal_id' => '21',
                'name' => null,
                'lft' => '20',
                'rgt' => '25',
                'parent_id' => '18',
                'level' => '5',
            ),
            array(
                'tree_traversal_id' => '22',
                'name' => null,
                'lft' => '26',
                'rgt' => '27',
                'parent_id' => '18',
                'level' => '5',
            ),
        );
        $this->assertEquals($expected, $return);
    }

    public function testUpdateNode()
    {
        //test
        $data = array(
            'name' => 'ahoj',
        );
        $this->treeAdapter
             ->updateNode(3, $data);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testUpdateNode-1.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        //test
        $data = array(
            'name' => 'ahoj',
            'lft' => '123456',
            'rgt' => '123456',
            'tree_traversal_id' => '123456',
            'level' => '123456',
            'parent_id' => '123456',
        );
        $this->treeAdapter
             ->updateNode(3, $data);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__.'/_files/NestedSet/testUpdateNode-1.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testGetRootNodeRootDoesNotExist()
    {
        $return = $this->treeAdapter
            ->getRootNode();

        $this->assertEquals(array(), $return);
    }

    public function testGetRootNode()
    {
        $return = $this->treeAdapter
            ->getRootNode();

        $expected = array(
            'tree_traversal_id' => '1',
            'name' => '',
            'lft' => '1',
            'rgt' => '50',
            'parent_id' => null,
            'level' => '0',
        );
        $this->assertEquals($expected, $return);
    }
}
