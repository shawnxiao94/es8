<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\XAPIService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class XAPIServiceTest extends BaseTestCase
{
    protected $auth;

    public function setUp()
    {
        $this->auth = $this->createAuth();
    }

    public function testWatchVideo_Success()
    {
        $service = $this->createXAPIService();

        $actor = array(
            'id' => 1,
            'name' => '测试用户',
        );
        $object = array(
            'id' => 1,
            'name' => '测试任务',
            'course' => array(
                'id' => 1,
                'title' => '测试课程',
                'description' => '这是一个测试课程',
            ),
            'video' => array(
                'id' => '1111',
                'name' => '测试视频.mp4',
            ),
        );
        $result = array(
            'duration' => 100,
        );

        $statement = $service->watchVideo($actor, $object, $result);

        $this->assertArrayHasKey('actor', $statement);
        $this->assertArrayHasKey('object', $statement);
        $this->assertArrayHasKey('result', $statement);
    }

    /**
     * @expectedException \QiQiuYun\SDK\Exception\ResponseException
     * @expectedExceptionCode 9
     */
    public function testWatchVideo_Error()
    {
        $service = $this->createXAPIService();

        $actor = array(
            'id' => 1,
            'name' => '测试用户',
        );
        $object = array(
            'id' => -1,
            'name' => 'error',
            'course' => array(
                'id' => 1,
                'title' => '测试课程',
                'description' => '这是一个测试课程',
            ),
            'video' => array(
                'id' => '1111',
                'name' => '测试视频.mp4',
            ),
        );
        $result = array(
            'duration' => 100,
        );

        $statement = $service->watchVideo($actor, $object, $result);
    }

    protected function createXAPIService()
    {
        $logger = new Logger('UnitTest');
        $logger->pushHandler(new StreamHandler(dirname(dirname(__DIR__)).'/var/log/unittest.log', Logger::DEBUG));

        return new XAPIService($this->auth, array(
            'base_uri' => 'http://localhost:8001/xapi/',
            'school' => array(
                'id' => $this->accessKey,
                'name' => '测试网校',
            ),
        ), $logger);
    }
}
