<?php

namespace Tests;

use Codeages\Biz\Framework\Dao\Connection;
use Codeages\Biz\Framework\Provider\RedisServiceProvider;
use Codeages\Biz\Framework\Provider\SchedulerServiceProvider;
use Codeages\Biz\Framework\Provider\TargetlogServiceProvider;
use Codeages\Biz\Framework\Provider\TokenServiceProvider;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\DoctrineServiceProvider;

class IntegrationTestCase extends TestCase
{
    /**
     * @var \Composer\Autoload\ClassLoader
     */
    public static $classLoader = null;

    /**
     * @var Biz
     */
    protected $biz;

    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var \Redis|\RedisArray
     */
    protected $redis;

    public function setUp()
    {
        $this->biz = $this->createBiz();
        $this->db = $this->biz['db'];
        $this->redis = $this->biz['redis'];

        $this->db->beginTransaction();
        $this->redis->flushDB();
    }

    public function tearDown()
    {
        $this->db->rollBack();
        $this->redis->close();

        unset($this->db);
        unset($this->redis);
        unset($this->biz);
    }

    protected function createBiz(array $options = array())
    {
        $defaultOptions = array(
            'db.options' => array(
                'dbname' => getenv('DB_NAME') ?: 'biz-target-test',
                'user' => getenv('DB_USER') ?: 'root',
                'password' => getenv('DB_PASSWORD') ?: '',
                'host' => getenv('DB_HOST') ?: '127.0.0.1',
                'port' => getenv('DB_PORT') ?: 3306,
                'driver' => 'pdo_mysql',
                'charset' => 'utf8',
            ),
            'redis.options' => array(
                'host' => getenv('REDIS_HOST'),
            ),
            'debug' => true,
        );
        $options = array_merge($defaultOptions, $options);

        $biz = new Biz($options);
        $biz['autoload.aliases']['Example'] = 'Tests\\Example';
        $biz->register(new DoctrineServiceProvider());
        $biz->register(new RedisServiceProvider());
        $biz->register(new TargetlogServiceProvider());
        $biz->register(new TokenServiceProvider());
        $biz->register(new SchedulerServiceProvider());
        $biz->boot();

        return $biz;
    }

    /**
     * @param string $seeder
     * @param bool   $isRun
     *
     * @return ArrayCollection
     */
    protected function seed($seeder, $isRun = true)
    {
        $seeder = new $seeder($this->db);

        return $seeder->run($isRun);
    }
}
