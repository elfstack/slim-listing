<?php

namespace Elfstack\SlimListing\Tests;

use Elfstack\SlimListing\Listing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Listing
     */
    protected $listing;

    /**
     * @var \Slim\App
     */
    private $app;

    public function setUp(): void
    {
        parent::setUp();
        $this->getEnvironmentSetUp();
        $this->setUpDatabase($this->app);
        $this->listing = Listing::create(new TestModel());
    }

    protected function getEnvironmentSetUp()
    {
        $config = [
            'settings' => [
                'displayErrorDetails' => true,
                'db' => [
                    'driver' => 'sqlite',
                    'database' => __DIR__.'/../database.sqlite'
                ]
            ],
        ];

        $app = new \Slim\App($config);

        $container = $app->getContainer();

        $capsule = new \Illuminate\Database\Capsule\Manager;
        $capsule->addConnection($container['settings']['db']);
        $capsule->bootEloquent();
        $capsule->setAsGlobal();

        //pass the connection to global container (created in previous article)
        $container['db'] = function ($container) use ($capsule){
            return $capsule;
        };

        $this->app = $app;
    }

    /**
     * @param \Slim\App $app
     */
    protected function setUpDatabase($app)
    {
        /** @var Builder $schema */
        $schema = $app->getContainer()['db']->connection()->getSchemaBuilder();
        $schema->dropIfExists('test_models');
        $schema->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('color');
            $table->integer('number');
            $table->dateTime('published_at');
        });

        TestModel::create([
            'name' => 'Alpha',
            'color' => 'red',
            'number' => 999,
            'published_at' => '2000-06-01 00:00:00',
        ]);

        collect(range(2, 10))->each(function ($i) {
            TestModel::create([
                'name' => 'Zeta '.$i,
                'color' => 'yellow',
                'number' => $i,
                'published_at' => (1998+$i).'-01-01 00:00:00',
            ]);
        });
    }
}
