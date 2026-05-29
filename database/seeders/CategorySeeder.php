<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Electronics',
                'slug'        => 'electronics',
                'description' => 'Gadgets, devices, and consumer electronics.',
                'children'    => [
                    ['name' => 'Mobile Phones',     'slug' => 'mobile-phones'],
                    ['name' => 'Laptops',            'slug' => 'laptops'],
                    ['name' => 'Tablets',            'slug' => 'tablets'],
                    ['name' => 'Cameras',            'slug' => 'cameras'],
                    ['name' => 'Audio & Headphones', 'slug' => 'audio-headphones'],
                ],
            ],
            [
                'name'        => 'Vehicles',
                'slug'        => 'vehicles',
                'description' => 'Cars, motorcycles, and other vehicles.',
                'children'    => [
                    ['name' => 'Cars',          'slug' => 'cars'],
                    ['name' => 'Motorcycles',   'slug' => 'motorcycles'],
                    ['name' => 'Trucks & Vans', 'slug' => 'trucks-vans'],
                    ['name' => 'Boats',         'slug' => 'boats'],
                ],
            ],
            [
                'name'        => 'Real Estate',
                'slug'        => 'real-estate',
                'description' => 'Residential, commercial, and land auctions.',
                'children'    => [
                    ['name' => 'Residential',  'slug' => 'residential'],
                    ['name' => 'Commercial',   'slug' => 'commercial'],
                    ['name' => 'Land & Plots', 'slug' => 'land-plots'],
                ],
            ],
            [
                'name'        => 'Fashion & Apparel',
                'slug'        => 'fashion-apparel',
                'description' => 'Clothing, shoes, and accessories.',
                'children'    => [
                    ['name' => 'Men\'s Clothing',  'slug' => 'mens-clothing'],
                    ['name' => 'Women\'s Clothing', 'slug' => 'womens-clothing'],
                    ['name' => 'Shoes',             'slug' => 'shoes'],
                    ['name' => 'Watches',           'slug' => 'watches'],
                    ['name' => 'Jewellery',         'slug' => 'jewellery'],
                ],
            ],
            [
                'name'        => 'Home & Garden',
                'slug'        => 'home-garden',
                'description' => 'Furniture, appliances, and garden equipment.',
                'children'    => [
                    ['name' => 'Furniture',        'slug' => 'furniture'],
                    ['name' => 'Home Appliances',  'slug' => 'home-appliances'],
                    ['name' => 'Garden & Outdoor', 'slug' => 'garden-outdoor'],
                    ['name' => 'Kitchen & Dining', 'slug' => 'kitchen-dining'],
                ],
            ],
            [
                'name'        => 'Art & Collectibles',
                'slug'        => 'art-collectibles',
                'description' => 'Fine art, antiques, coins, and memorabilia.',
                'children'    => [
                    ['name' => 'Paintings & Prints', 'slug' => 'paintings-prints'],
                    ['name' => 'Antiques',            'slug' => 'antiques'],
                    ['name' => 'Coins & Stamps',      'slug' => 'coins-stamps'],
                    ['name' => 'Sports Memorabilia',  'slug' => 'sports-memorabilia'],
                ],
            ],
            [
                'name'        => 'Books & Media',
                'slug'        => 'books-media',
                'description' => 'Books, music, films, and games.',
                'children'    => [
                    ['name' => 'Books',         'slug' => 'books'],
                    ['name' => 'Music & Vinyl', 'slug' => 'music-vinyl'],
                    ['name' => 'Films & DVDs',  'slug' => 'films-dvds'],
                    ['name' => 'Video Games',   'slug' => 'video-games'],
                ],
            ],
            [
                'name'        => 'Sports & Leisure',
                'slug'        => 'sports-leisure',
                'description' => 'Sports equipment, fitness, and outdoor gear.',
                'children'    => [
                    ['name' => 'Fitness Equipment', 'slug' => 'fitness-equipment'],
                    ['name' => 'Outdoor & Camping', 'slug' => 'outdoor-camping'],
                    ['name' => 'Cycling',           'slug' => 'cycling'],
                    ['name' => 'Water Sports',      'slug' => 'water-sports'],
                ],
            ],
            [
                'name'        => 'Business & Industrial',
                'slug'        => 'business-industrial',
                'description' => 'Machinery, tools, and business equipment.',
                'children'    => [
                    ['name' => 'Heavy Machinery',  'slug' => 'heavy-machinery'],
                    ['name' => 'Office Equipment', 'slug' => 'office-equipment'],
                    ['name' => 'Tools & Hardware', 'slug' => 'tools-hardware'],
                ],
            ],
            [
                'name'        => 'Other',
                'slug'        => 'other',
                'description' => 'Miscellaneous items that do not fit other categories.',
                'children'    => [],
            ],
        ];

        foreach ($categories as $data) {
            $parent = Category::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name'        => $data['name'],
                    'description' => $data['description'],
                    'parent_id'   => null,
                    'is_active'   => true,
                ]
            );

            foreach ($data['children'] as $child) {
                Category::updateOrCreate(
                    ['slug' => $child['slug']],
                    [
                        'name'        => $child['name'],
                        'description' => null,
                        'parent_id'   => $parent->id,
                        'is_active'   => true,
                    ]
                );
            }
        }
    }
}