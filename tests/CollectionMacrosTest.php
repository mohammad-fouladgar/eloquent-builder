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
            'level'  => 0,
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
                'min' => 0,
                'max' => 100,
            ],
            'rental' => [
                'price' => [
                    'min' => '',
                    'max' => 500,
                ],
            ],
            'weight' => [
                'min' => 0,
                'max' => 0,
            ],
        ]);

        $this->assertEquals(
            [
                'city'  => 'isfahan',
                'level' => 0,
                'price' => [
                    'min' => 10000,
                    'max' => '',
                ],
                'area' => [
                    'min' => 0,
                    'max' => 100,
                ],
                'rental' => [
                    'price' => [
                        'min' => '',
                        'max' => 500,
                    ],
                ],
                'weight' => [
                    'min' => 0,
                    'max' => 0,
                ],
            ],
            $inputs->getFilters()
        );
    }
}
