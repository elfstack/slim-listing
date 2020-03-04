<?php

namespace Elfstack\SlimListing\Tests\Features;

use Elfstack\SlimListing\Tests\TestCase;

class FilterTest extends TestCase
{
    public function testGetFilter()
    {
        $query = "column:val1,val2,val3;column2:val1";

        return $this->assertEquals($this->listing->getFilter($query), [
            [
                'column' => 'column',
                'values' => ['val1', 'val2', 'val3']
            ],
            [
                'column' => 'column2',
                'values' => ['val1']
            ]
        ]);
    }
}