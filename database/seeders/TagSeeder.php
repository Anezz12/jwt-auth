<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'Laravel', 'slug' => 'laravel'],
            ['name' => 'PHP', 'slug' => 'php'],
            ['name' => 'JavaScript', 'slug' => 'javascript'],
            ['name' => 'React', 'slug' => 'react'],
            ['name' => 'Vue.js', 'slug' => 'vue-js'],
            ['name' => 'Node.js', 'slug' => 'node-js'],
            ['name' => 'Python', 'slug' => 'python'],
            ['name' => 'Docker', 'slug' => 'docker'],
            ['name' => 'AWS', 'slug' => 'aws'],
            ['name' => 'MySQL', 'slug' => 'mysql'],
            ['name' => 'PostgreSQL', 'slug' => 'postgresql'],
            ['name' => 'MongoDB', 'slug' => 'mongodb'],
            ['name' => 'API', 'slug' => 'api'],
            ['name' => 'REST', 'slug' => 'rest'],
            ['name' => 'GraphQL', 'slug' => 'graphql'],
            ['name' => 'Testing', 'slug' => 'testing'],
            ['name' => 'Security', 'slug' => 'security'],
            ['name' => 'Performance', 'slug' => 'performance'],
            ['name' => 'UI/UX', 'slug' => 'ui-ux'],
            ['name' => 'Mobile', 'slug' => 'mobile'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
