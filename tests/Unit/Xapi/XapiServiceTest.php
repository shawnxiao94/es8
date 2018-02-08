<?php

namespace Tests\Unit\Xapi;

use Biz\BaseTestCase;
use Biz\Xapi\Service\XapiService;

class XapiServiceTest extends BaseTestCase
{
    public function testCreateStatement()
    {
        $statement = $this->mockStatement();
        $createdStatement = $this->getXapiService()->createStatement($statement);

        $result = $this->getXapiService()->getStatement($createdStatement['id']);

        $this->assertNotEmpty($result);
        $this->assertEquals($createdStatement['id'], $result['id']);
    }

    public function testUpdateStatementsPushedByStatementIds()
    {
        $statement = $this->mockStatement();
        $createdStatement = $this->getXapiService()->createStatement($statement);

        $this->getXapiService()->updateStatementsPushedByStatementIds(array($createdStatement['id']));

        $result = $this->getXapiService()->getStatement($createdStatement['id']);

        $this->assertEquals('pushed', $result['status']);
    }

    public function testUpdateStatementsPushingByStatementIds()
    {
        $statement = $this->mockStatement();
        $createdStatement = $this->getXapiService()->createStatement($statement);

        $this->getXapiService()->updateStatementsPushingByStatementIds(array($createdStatement['id']));

        $result = $this->getXapiService()->getStatement($createdStatement['id']);

        $this->assertEquals('pushing', $result['status']);
    }

    public function testUpdateStatementsPushedAndDataByStatementData()
    {
        $statement = $this->mockStatement();
        $createdStatement = $this->getXapiService()->createStatement($statement);

        $this->getXapiService()->updateStatementsPushedAndDataByStatementData(array($createdStatement['id'] => array('test' => 'test')));

        $result = $this->getXapiService()->getStatement($createdStatement['id']);

        $this->assertEquals('pushed', $result['status']);
        $this->assertEquals('test', $result['data']['test']);
    }

    public function testSearchStatements()
    {
        $statement = $this->mockStatement();
        $this->getXapiService()->createStatement($statement);

        $results = $this->getXapiService()->searchStatements(array('status' => 'created'), array(), 0, 10);

        $this->assertEquals(1, count($results));
    }

    public function testCountStatements()
    {
        $statement = $this->mockStatement();
        $this->getXapiService()->createStatement($statement);

        $count = $this->getXapiService()->countStatements(array('status' => 'created'));

        $this->assertEquals(1, $count);
    }

    private function mockStatement()
    {
        $statement = array(
            'user_id' => 2,
            'verb' => 'watch',
            'target_id' => 1,
            'target_type' => 'video',
            'occur_time' => time(),
        );

        return $statement;
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->createService('Xapi:XapiService');
    }
}
