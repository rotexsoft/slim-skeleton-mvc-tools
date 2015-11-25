<?php
use \Rotexsoft\Renderer;

/**
 * Description of ModelTest
 *
 * @author Rotimi Adegbamigbe
 */
class RendererTest extends \PHPUnit_Framework_TestCase
{
    protected $_mock_model_objs = array();

    protected function setUp() {
        
        parent::setUp();
        
        $sqlite_file = __DIR__.DIRECTORY_SEPARATOR
                       .'DbFiles'.DIRECTORY_SEPARATOR
                       .'buying_and_selling.sqlite';

        $this->_mock_model_objsarray('customers_with_specialized_collection_and_record') = 
                new \ModelForTestingPublicAndProtectedMethods(
                    "sqlite:$sqlite_file", "", "", array(),
                    array(
                        'primary_col'=>'CustomerID', 
                        'table_name'=>'Customers',
                        'collection_class_name'=>'CollectionForTestingPublicAndProtectedMethods', 
                        'record_class_name'=>'RecordForTestingPublicAndProtectedMethods',
                    )
                );

        $this->_mock_model_objsarray('customers') = 
                new \ModelForTestingPublicAndProtectedMethods(
                    "sqlite:$sqlite_file", "", "", array(),
                    array('primary_col'=>'CustomerID', 'table_name'=>'Customers')
                );

        $this->_mock_model_objsarray('employees') = 
                new \ModelForTestingPublicAndProtectedMethods(
                    "sqlite:$sqlite_file", "", "", array(),
                    array('primary_col'=>'EmployeeID', 'table_name'=>'Employees')
                );

        $this->_mock_model_objsarray('order_details') = 
                new \ModelForTestingPublicAndProtectedMethods(
                    "sqlite:$sqlite_file", "", "", array(),
                    array('primary_col'=>'OrderDetailID', 'table_name'=>'OrderDetails')
                );

        $this->_mock_model_objsarray('orders') = 
                new \ModelForTestingPublicAndProtectedMethods(
                    "sqlite:$sqlite_file", "", "", array(),
                    array('primary_col'=>'OrderID', 'table_name'=>'Orders')
                );

        $this->_mock_model_objsarray('shippers') = 
                new \ModelForTestingPublicAndProtectedMethods(
                    "sqlite:$sqlite_file", "", "", array(),
                    array('primary_col'=>'ShipperID', 'table_name'=>'Shippers')
                );
    }

    public function testToEnsureThatAddHavingConditions2QueryWorksAsExpected() {
        
        $data = array(
            'having' => 
                array(
                    0 => array( 'col' => 'col_1', 'op' => '<', 'val' => 58),
                    1 => array( 'col' => 'col_2', 'op' => '<', 'val' => 68),
                    array(
                        0 => array( 'col' => 'col_11', 'op' => '>', 'val' => 581),
                        1 => array( 'col' => 'col_21', 'op' => '>', 'val' => 681),
                        'OR#3' => array(
                            0 => array( 'col' => 'col_12', 'op' => '<', 'val' => 582),
                            1 => array( 'col' => 'col_22', 'op' => '<', 'val' => 682)
                        ),
                        2 => array( 'col' => 'col_31', 'op' => '>=', 'val' => 583),
                        'OR#4' => array(
                            0 => array( 'col' => 'col_4', 'op' => '=', 'val' => 584),
                            1 => array( 'col' => 'col_5', 'op' => '=', 'val' => 684),
                        )
                    ),
                    3 => array( 'col' => 'column_name_44', 'op' => '<', 'val' => 777),
                    4 => array( 'col' => 'column_name_55', 'op' => 'is-null'),
                )
        );
        
        $mock_model_cust = $this->_mock_model_objsarray('customers');
        
        //pdo_driver_name
        $select_qry_obj->from($mock_model_cust->_table_name)->cols(array('*'));
        
        $mock_model_cust->addHavingConditions2Query($dataarray('having'), $select_qry_obj);
        
        $expected_sql = <<<EOT
SELECT
    *
FROM
    "Customers"
HAVING
    (
	col_1 > :_1_ 
	AND
	col_2 > :_2_ 
	AND
	(
		col_11 > :_3_ 
		AND
		col_21 > :_4_ 
		OR
		(
			col_12 > :_5_ 
			AND
			col_22 > :_6_ 
		)
		AND
		col_31 >= :_7_ 
		OR
		(
			col_4 = :_8_ 
			AND
			col_5 = :_9_ 
		)
	)
	AND
	column_name_44 > :_10_ 
	AND
	column_name_55 IS NULL
)
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
//print_r( $select_qry_obj->getBindValues());exit;
        
        $expected_params = array(
            '_1_' => 58, '_2_' => 68, '_3_' => 581, '_4_' => 681, '_5_' => 582,
            '_6_' => 682, '_7_' => 583, '_8_' => 584, '_9_' => 684, '_10_' => 777
        );
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
    }

    public function testToEnsureThatAddWhereConditions2QueryWorksAsExpected() {
        
        $data = array(
            'where' => 
                array(
                    0 => array( 'col' => 'col_1', 'op' => '<', 'val' => 58),
                    1 => array( 'col' => 'col_2', 'op' => '<', 'val' => 68),
                    array(
                        0 => array( 'col' => 'col_11', 'op' => '>', 'val' => 581),
                        1 => array( 'col' => 'col_21', 'op' => '>', 'val' => 681),
                        'OR#3' => array(
                            0 => array( 'col' => 'col_12', 'op' => '<', 'val' => 582),
                            1 => array( 'col' => 'col_22', 'op' => '<', 'val' => 682)
                        ),
                        2 => array( 'col' => 'col_31', 'op' => '>=', 'val' => 583),
                        'OR#4' => array(
                            0 => array( 'col' => 'col_4', 'op' => '=', 'val' => 584),
                            1 => array( 'col' => 'col_5', 'op' => '=', 'val' => 684),
                        )
                    ),
                    3 => array( 'col' => 'column_name_44', 'op' => '<', 'val' => 777),
                    4 => array( 'col' => 'column_name_55', 'op' => 'is-null'),
                )
        );
        
        $mock_model_cust = $this->_mock_model_objsarray('customers');
        
        //pdo_driver_name
        $select_qry_obj->from($mock_model_cust->_table_name)->cols(array('*'));
        
        $mock_model_cust->addWhereConditions2Query($dataarray('where'), $select_qry_obj);
        
        $expected_sql = <<<EOT
SELECT
    *
FROM
    "Customers"
WHERE
    (
	col_1 > :_11_ 
	AND
	col_2 > :_12_ 
	AND
	(
		col_11 > :_13_ 
		AND
		col_21 > :_14_ 
		OR
		(
			col_12 > :_15_ 
			AND
			col_22 > :_16_ 
		)
		AND
		col_31 >= :_17_ 
		OR
		(
			col_4 = :_18_ 
			AND
			col_5 = :_19_ 
		)
	)
	AND
	column_name_44 > :_20_ 
	AND
	column_name_55 IS NULL
)
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
        
        $expected_params = array(
            '_11_' => 58, '_12_' => 68, '_13_' => 581, '_14_' => 681, '_15_' => 582,
            '_16_' => 682, '_17_' => 583, '_18_' => 584, '_19_' => 684, '_20_' => 777
        );

        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
    }
    
    public function testToEnsureThatBuildFetchQueryFromParamsWorksAsExpected() {

        $params = array(
            'distinct' => true,
            'cols' => array('CustomerID'),
        );
        
        $mock_model_cust = $this->_mock_model_objsarray('customers');

        //$mock_model_cust->buildFetchQueryFromParams($params, $allowed_keys);
        
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);
        
        $expected_sql = <<<EOT
SELECT DISTINCT
    CustomerID
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
        
////////////////////////////////////////////////////////////////////////////////        
        $params = array(
            'distinct' => true,
            'cols' => array('CustomerID', 'CompanyName'),
        );
           
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);
        
        $expected_sql = <<<EOT
SELECT DISTINCT
    CustomerID,
    CompanyName
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
                
        $expected_params = array();
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
        $params = array(
            'distinct' => true,
            'cols' => array('CustomerID', 'CompanyName', 'ContactName'),
        );
           
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);

        $expected_sql = <<<EOT
SELECT DISTINCT
    CustomerID,
    CompanyName,
    ContactName
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
                
        $expected_params = array();
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
        $params = array(
            'distinct' => true,
            'cols' => array('CustomerID', 'CompanyName', 'ContactName', 'ContactTitle'),
        );
           
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);

        $expected_sql = <<<EOT
SELECT DISTINCT
    CustomerID,
    CompanyName,
    ContactName,
    ContactTitle
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
                
        $expected_params = array();
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
        $params = array(
            'distinct' => true,
            'cols' => array('CustomerID', 'CompanyName', 'ContactName', 'ContactTitle', 'Address'),
        );
           
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);

        $expected_sql = <<<EOT
SELECT DISTINCT
    CustomerID,
    CompanyName,
    ContactName,
    ContactTitle,
    Address
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
        
        $expected_params = array();
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
        $params = array(
            'distinct' => true,
            'cols' => array('CustomerID', 'CompanyName', 'ContactName', 'ContactTitle', 'Address', 'City'),
        );
           
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);

        $expected_sql = <<<EOT
SELECT DISTINCT
    CustomerID,
    CompanyName,
    ContactName,
    ContactTitle,
    Address,
    City
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
                
        $expected_params = array();
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
        $params = array(
            'distinct' => true,
            'cols' => array('CompanyName', 'ContactName', 'ContactTitle', 'Address', 'City', 'State'),
        );
           
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);

        $expected_sql = <<<EOT
SELECT DISTINCT
    CompanyName,
    ContactName,
    ContactTitle,
    Address,
    City,
    State
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
       
        $expected_params = array();
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
        $params = array(
            'distinct' => true,
            'cols' => array('CustomerID', 'CompanyName', 'ContactName', 'ContactTitle', 'Address', 'City', 'State'),
        );
           
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);

        $expected_sql = <<<EOT
SELECT DISTINCT
    CustomerID,
    CompanyName,
    ContactName,
    ContactTitle,
    Address,
    City,
    State
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
        
        $expected_params = array();
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
        
        $params = array(
            'distinct' => false,
            'cols' => array('CustomerID'),
        );
        
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);
        
        $expected_sql = <<<EOT
SELECT
    CustomerID
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
        
        $expected_params = array();
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////        
        $params = array(
            'distinct' => false,
            'cols' => array('CustomerID', 'CompanyName'),
        );
           
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);
        
        $expected_sql = <<<EOT
SELECT
    CustomerID,
    CompanyName
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
                
        $expected_params = array();
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
        $params = array(
            'distinct' => false,
            'cols' => array('CustomerID', 'CompanyName', 'ContactName'),
        );
           
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);

        $expected_sql = <<<EOT
SELECT
    CustomerID,
    CompanyName,
    ContactName
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
        
        $expected_params = array();
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
        $params = array(
            'distinct' => false,
            'cols' => array('CustomerID', 'CompanyName', 'ContactName', 'ContactTitle'),
        );
           
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);

        $expected_sql = <<<EOT
SELECT
    CustomerID,
    CompanyName,
    ContactName,
    ContactTitle
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
        
        $expected_params = array();
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
        $params = array(
            'distinct' => false,
            'cols' => array('CustomerID', 'CompanyName', 'ContactName', 'ContactTitle', 'Address'),
        );
           
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);

        $expected_sql = <<<EOT
SELECT
    CustomerID,
    CompanyName,
    ContactName,
    ContactTitle,
    Address
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
        
        $expected_params = array();
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
        $params = array(
            'distinct' => false,
            'cols' => array('CustomerID', 'CompanyName', 'ContactName', 'ContactTitle', 'Address', 'City'),
        );
           
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);

        $expected_sql = <<<EOT
SELECT
    CustomerID,
    CompanyName,
    ContactName,
    ContactTitle,
    Address,
    City
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
        
        $expected_params = array();
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
        $params = array(
            'distinct' => false,
            'cols' => array('CompanyName', 'ContactName', 'ContactTitle', 'Address', 'City', 'State'),
        );
           
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);

        $expected_sql = <<<EOT
SELECT
    CompanyName,
    ContactName,
    ContactTitle,
    Address,
    City,
    State
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
        
        $expected_params = array();
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
        $params = array(
            'distinct' => false,
            'cols' => array('CustomerID', 'CompanyName', 'ContactName', 'ContactTitle', 'Address', 'City', 'State'),
        );
           
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);

        $expected_sql = <<<EOT
SELECT
    CustomerID,
    CompanyName,
    ContactName,
    ContactTitle,
    Address,
    City,
    State
FROM
    "Customers"
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
        
        $expected_params = array();
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
        $params = array(
            'distinct' => false,
            'cols' => array('CustomerID', 'CompanyName', 'ContactName', 'ContactTitle', 'Address', 'City', 'State'),

            'where' => array(
                array( 'col' => 'hidden_fiscal_year', 'op' => 'in', 'val' => 16 ),
                array( 'col' => 'deactivated', 'op' => '=', 'val' => 0),
                array( 'col' => 'parent_id', 'op' => 'is-null'),
            ),
            'group' => array('hidden_fiscal_year'),
            'having' => array(
                array( 'col' => 'hidden_fiscal_year', 'op' => '>', 'val' => 9 ),
                array( 'col' => 'deactivated', 'op' => '=', 'val' => 0),
                array( 'col' => 'parent_id', 'op' => 'is-null'),
            ),
            'order' => array('title desc'),
            'limit_size' => 400,
            'limit_offset' => 50,
        );
           
        $select_qry_obj = $mock_model_cust->buildFetchQueryFromParams($params);
        
        $expected_sql = <<<EOT
SELECT
    CustomerID,
    CompanyName,
    ContactName,
    ContactTitle,
    Address,
    City,
    State
FROM
    "Customers"
WHERE
    (
	hidden_fiscal_year IN (16) 
	AND
	deactivated = :_21_ 
	AND
	parent_id IS NULL
)

GROUP BY
    hidden_fiscal_year
HAVING
    (
	hidden_fiscal_year > :_22_ 
	AND
	deactivated = :_23_ 
	AND
	parent_id IS NULL
)

ORDER BY
    title desc
LIMIT 400 OFFSET 50
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());

        $expected_params = array( '_21_' => 0, '_22_' => 9, '_23_' => 0);
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
        $params = array(
            'distinct' => false,
            'cols' => array('CustomerID', 'CompanyName', 'ContactName', 'ContactTitle', 'Address', 'City', 'State'),

            'where' => array(
                array( 'col' => 'hidden_fiscal_year', 'op' => 'in', 'val' => 16 ),
                array( 'col' => 'deactivated', 'op' => '=', 'val' => 0),
                array( 'col' => 'parent_id', 'op' => 'is-null'),
            ),
            'group' => array('hidden_fiscal_year'),
            'having' => array(
                array( 'col' => 'hidden_fiscal_year', 'op' => '>', 'val' => 9 ),
                array( 'col' => 'deactivated', 'op' => '=', 'val' => 0),
                array( 'col' => 'parent_id', 'op' => 'is-null'),
            ),
            'order' => array('title desc'),
            'limit_size' => 400,
            'limit_offset' => 0,
        );
           
        $select_qry_obj = 
            $mock_model_cust
                ->buildFetchQueryFromParams($params, array('having', 'limit_size'));

        $expected_sql = <<<EOT
SELECT
    CustomerID,
    CompanyName,
    ContactName,
    ContactTitle,
    Address,
    City,
    State
FROM
    "Customers"
WHERE
    (
	hidden_fiscal_year IN (16) 
	AND
	deactivated = :_24_ 
	AND
	parent_id IS NULL
)

GROUP BY
    hidden_fiscal_year
ORDER BY
    title desc
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
                
        $expected_params = array('_24_' => 0);
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
        
////////////////////////////////////////////////////////////////////////////////
//Test with overriden table name        
        $params = array(
            'distinct' => false,
            'cols' => array('CustomerID', 'CompanyName', 'ContactName', 'ContactTitle', 'Address', 'City', 'State'),

            'where' => array(
                array( 'col' => 'hidden_fiscal_year', 'op' => 'in', 'val' => 16 ),
                array( 'col' => 'deactivated', 'op' => '=', 'val' => 0),
                array( 'col' => 'parent_id', 'op' => 'is-null'),
            ),
            'group' => array('hidden_fiscal_year'),
            'having' => array(
                array( 'col' => 'hidden_fiscal_year', 'op' => '>', 'val' => 9 ),
                array( 'col' => 'deactivated', 'op' => '=', 'val' => 0),
                array( 'col' => 'parent_id', 'op' => 'is-null'),
            ),
            'order' => array('title desc'),
            'limit_size' => 400,
            'limit_offset' => 0,
        );
           
        $select_qry_obj = 
            $mock_model_cust
                ->buildFetchQueryFromParams($params, array('having', 'limit_size'), "Customers2");

        $expected_sql = <<<EOT
SELECT
    CustomerID,
    CompanyName,
    ContactName,
    ContactTitle,
    Address,
    City,
    State
FROM
    "Customers2"
WHERE
    (
	hidden_fiscal_year IN (16) 
	AND
	deactivated = :_25_ 
	AND
	parent_id IS NULL
)

GROUP BY
    hidden_fiscal_year
ORDER BY
    title desc
EOT;
        $this->assertContains($expected_sql, $select_qry_obj->__toString());
                
        $expected_params = array('_25_' => 0);
        $this->assertEquals($expected_params, $select_qry_obj->getBindValues());
    }

    public function testCreateNewCollection() {
        
        $model_with_mock_coll_and_rec =
            $this->_mock_model_objsarray('customers_with_specialized_collection_and_record');

        $coll_mock = $model_with_mock_coll_and_rec
                            ->createNewCollection(new \GDAO\Model\RecordsList(array()));
        //exact class
        $this->assertEquals(
            'CollectionForTestingPublicAndProtectedMethods', 
            get_class($coll_mock)
        );
        
        //has the right parent class
        $this->assertInstanceOf('LeanOrm\Model\Collection', $coll_mock);
        $this->assertInstanceOf('GDAO\Model\CollectionInterface', $coll_mock);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $model_with_leanorm_coll_and_rec = $this->_mock_model_objsarray('customers');
        
        $coll_generic = $model_with_leanorm_coll_and_rec
                            ->createNewCollection(new \GDAO\Model\RecordsList(array()));
        //exact class
        $this->assertEquals('LeanOrm\Model\Collection', get_class($coll_generic));
    }

    public function testCreateNewRecord() {
        
        $model_with_mock_coll_and_rec =
            $this->_mock_model_objsarray('customers_with_specialized_collection_and_record');

        $record_mock = $model_with_mock_coll_and_rec
                                        ->createNewRecord(array(), array('is_new'=>false));
        //exact class
        $this->assertEquals(
            'RecordForTestingPublicAndProtectedMethods', 
            get_class($record_mock)
        );
        
        //has the right parent class
        $this->assertInstanceOf('\\LeanOrm\\Model\\Record', $record_mock);
        $this->assertInstanceOf('\\GDAO\\Model\\RecordInterface', $record_mock);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $model_with_leanorm_coll_and_rec = $this->_mock_model_objsarray('customers');
        
        $record_generic = $model_with_leanorm_coll_and_rec
                                        ->createNewRecord(array(), array('is_new'=>false));
        //exact class
        $this->assertEquals('LeanOrm\Model\Record', get_class($record_generic));
    }
    
    public function test__Get() {
        
        $mock_model_cust = $this->_mock_model_objsarray('customers');
        
        //access protected property
        $this->assertEquals('CustomerID', $mock_model_cust->primary_col);
        $this->assertEquals('CustomerID', $mock_model_cust->_primary_col);
        
        try {
            //access non-existent property
            $mock_model_cust->non_existent_property;
            
        } catch (Exception $ex) {

            $this->assertEquals(
                'LeanOrm\ModelPropertyNotDefinedException', get_class($ex)
            );
        }
    }
    
    public function testDeleteMatchingDbTableRows() {
        
        $ins_sql = <<<SQL
INSERT INTO "Shippers" VALUES(55,'USPS','1 (800) 275-8777');
INSERT INTO "Shippers" VALUES(56,'Federal Express','1-800-463-3339');
INSERT INTO "Shippers" VALUES(57,'UPS','1 (800) 742-5877');
INSERT INTO "Shippers" VALUES(58,'DHL','1-800-CALL-DHL');
SQL;
        $mock_model_shippers = $this->_mock_model_objsarray('shippers');
        
        //add the data to delete
        $mock_model_shippers->getPDO()->exec($ins_sql);
        
        //should return 1, 1 record deleted
        $res1 = $mock_model_shippers->deleteMatchingDbTableRows(
                    array($mock_model_shippers->getPrimaryColName() => 55 )
                );
        $this->assertEquals(1, $res1);
        
        //should return 3, 3 records deleted
        $res2 = $mock_model_shippers->deleteMatchingDbTableRows(
                    array($mock_model_shippers->getPrimaryColName() => array(56, 57, 58))
                );
        $this->assertEquals(3, $res2);
        
        //should return 0 no records deleted
        $res3 = $mock_model_shippers->deleteMatchingDbTableRows(
                    array($mock_model_shippers->getPrimaryColName() => 55 )
                );
        $this->assertEquals( true, ($res3 === 0) );
        
        //should return null no db operation happened
        $res4 = $mock_model_shippers->deleteMatchingDbTableRows(array());
        $this->assertEquals( true, ($res4 === null) );
    }
}
