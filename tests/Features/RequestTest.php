<?php

namespace Elfstack\SlimListing\Tests\Features;

use Elfstack\SlimListing\Tests\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;

class RequestTest extends TestCase
{
    public function testGet()
    {
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/test',
            'QUERY_STRING'=>''
        ]));

        $result = $this->listing->get($request);

        $this->assertCount(10, $result);
    }

    public function testGetWithSearch()
    {
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/test',
            'QUERY_STRING'=>'keyword=Alpha'
        ]));


        $result = $this->listing->attachSearching(['name'])->get($request);

        $this->assertCount(1, $result);
    }

    public function testGetWithSortAsc()
    {
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/test',
            'QUERY_STRING'=>'orderBy=name&direction=asc'
        ]));

        $result = $this->listing->attachSorting(['name'])->get($request);

        $this->assertEquals('Alpha', $result->getCollection()->first()->name);

    }

    public function testGetWithSortDesc()
    {
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/test',
            'QUERY_STRING'=>'orderBy=name&direction=desc'
        ]));

        $result = $this->listing->attachSorting(['name'])->get($request);

        $this->assertEquals('Zeta 9', $result->getCollection()->first()->name);
    }

    public function testGetWithFilter()
    {
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/test',
            'QUERY_STRING'=>'filter=number:2,3,4,999;color:yellow'
        ]));

        $result = $this->listing->attachFiltering(['number', 'color'])->get($request);

        $this->assertCount(3, $result);
    }
}
