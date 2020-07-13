<?php

namespace Fouladgar\EloquentBuilder\Tests;

class CollectionMacrosTest extends TestCase
{
    /** @test */
    public function it_can_remove_lacking_value()
    {
        $inputs = collect([
            'city' => 'isfahan',
            'name',
            'gender' => '',
            'age'    => null,
            'date'   => [
                'from' => '',
                'to'   => '',
            ],
            'price' => [
                'min' => 10000,
                'max' => '',
            ],
            'area' => [
                'min' => 50,
                'max' => 100,
            ],
            'rental' => [
                'price' => [
                    'min' => '',
                    'max' => 500,
                ],
            ],
        ]);

        $this->assertEquals(
            [
                'city'  => 'isfahan',
                'price' => [
                    'min' => 10000,
                    'max' => '',
                ],
                'area' => [
                    'min' => 50,
                    'max' => 100,
                ],
                'rental' => [
                    'price' => [
                        'min' => '',
                        'max' => 500,
                    ],
                ],
            ],
            $inputs->getFilters()
        );
    }
}
