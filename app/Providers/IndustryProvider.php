<?php
namespace App\Providers;

use Faker\Provider\Base;
use Faker\Generator;

class IndustryProvider extends Base
{
    protected $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public static function industry()
    {
        return [
            'Agriculture',
            'Automotive',
            'Construction',
            'Education',
            'Energy',
            'Finance',
            'Food and Beverages',
            'Healthcare',
            'Hospitality',
            'Information Technology',
            'Manufacturing',
            'Media',
            'Pharmaceuticals',
            'Retail',
            'Telecommunications',
            'Transportation',
            'Utilities'
        ];
    }
}
