<?php

namespace Fouladgar\EloquentBuilder\Tests;

class CollectionMacrosTest extends TestCase
{
    /** @test */
    public function it_can_remove_lacking_value()
    {
        $inputs = collect([
            'city'=> 'isfahan',
            'name',
            'gender'=> '',
            'age'   => null,
        ]);

        $this->assertEquals(['city'=>'isfahan'], $inputs->getFilters());
    }
}
