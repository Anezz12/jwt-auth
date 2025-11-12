<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {

        $authors = User::where('role', 'admin')->get();
        if ($authors->isEmpty()) {
            User::create([
                'name' => 'Default Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);
            $authors = User::where('role', 'admin')->get();
        }

        // Ensure at least one active category exists
        $categories = Category::where('is_active', true)->get();
        if ($categories->isEmpty()) {
            Category::create([
                'name' => 'General',
                'slug' => 'general',
                'description' => 'General category',
                'is_active' => true,
            ]);
            $categories = Category::where('is_active', true)->get();
        }

        // Ensure at least some tags exist
        $tags = Tag::all();
        if ($tags->isEmpty()) {
            Tag::create(['name' => 'Laravel', 'slug' => 'laravel']);
            Tag::create(['name' => 'PHP', 'slug' => 'php']);
            Tag::create(['name' => 'JavaScript', 'slug' => 'javascript']);
            Tag::create(['name' => 'Tutorial', 'slug' => 'tutorial']);
            $tags = Tag::all();
        }

        $articles = [
            [
                'title' => 'Getting Started with Laravel 11',
                'slug' => 'getting-started-with-laravel-11',
                'excerpt' => 'Learn the basics of Laravel 11 and build your first application',
                'content' => 'Laravel 11 brings exciting new features...',
                'status' => 'published',
                'is_featured' => true,
                'is_editor_pick' => true,
                'views_count' => 1250,
                'published_at' => now()->subDays(2),
                'meta_title' => 'Laravel 11 Tutorial - Getting Started Guide',
                'meta_description' => 'Complete guide to getting started with Laravel 11 framework',
                'meta_keywords' => ['laravel', 'php', 'framework', 'tutorial'],
            ],
            [
                'title' => 'Building REST APIs with Laravel Sanctum',
                'slug' => 'building-rest-apis-with-laravel-sanctum',
                'excerpt' => 'Secure your Laravel APIs with Sanctum authentication',
                'content' => 'Laravel Sanctum provides a simple way to authenticate...',
                'status' => 'published',
                'is_featured' => true,
                'is_editor_pick' => false,
                'views_count' => 890,
                'published_at' => now()->subDays(5),
                'meta_title' => 'Laravel Sanctum API Authentication',
                'meta_description' => 'Learn how to secure your Laravel APIs with Sanctum',
                'meta_keywords' => ['laravel', 'sanctum', 'api', 'authentication'],
            ],
            [
                'title' => 'React Hooks: A Complete Guide',
                'slug' => 'react-hooks-complete-guide',
                'excerpt' => 'Master React Hooks with practical examples',
                'content' => 'React Hooks revolutionized how we write React components...',
                'status' => 'published',
                'is_featured' => false,
                'is_editor_pick' => true,
                'views_count' => 2100,
                'published_at' => now()->subDays(7),
                'meta_title' => 'React Hooks Complete Guide 2024',
                'meta_description' => 'Master React Hooks with practical examples and best practices',
                'meta_keywords' => ['react', 'hooks', 'javascript', 'frontend'],
            ],
            [
                'title' => 'Docker for PHP Developers',
                'slug' => 'docker-for-php-developers',
                'excerpt' => 'Containerize your PHP applications with Docker',
                'content' => 'Docker simplifies development and deployment...',
                'status' => 'published',
                'is_featured' => false,
                'is_editor_pick' => false,
                'views_count' => 675,
                'published_at' => now()->subDays(10),
                'meta_title' => 'Docker Tutorial for PHP Developers',
                'meta_description' => 'Learn Docker containerization for PHP applications',
                'meta_keywords' => ['docker', 'php', 'containers', 'devops'],
            ],
            [
                'title' => 'Advanced MySQL Query Optimization',
                'slug' => 'advanced-mysql-query-optimization',
                'excerpt' => 'Improve your database query performance',
                'content' => 'Database optimization is crucial for application performance...',
                'status' => 'draft',
                'is_featured' => false,
                'is_editor_pick' => false,
                'views_count' => 0,
                'published_at' => null,
                'meta_title' => 'MySQL Query Optimization Techniques',
                'meta_description' => 'Advanced techniques for optimizing MySQL queries',
                'meta_keywords' => ['mysql', 'database', 'optimization', 'performance'],
            ],
            [
                'title' => 'Vue.js 3 Composition API',
                'slug' => 'vue-js-3-composition-api',
                'excerpt' => 'Explore the new Composition API in Vue.js 3',
                'content' => 'Vue.js 3 introduced the Composition API...',
                'status' => 'published',
                'is_featured' => false,
                'is_editor_pick' => false,
                'views_count' => 450,
                'published_at' => now()->subDays(14),
                'meta_title' => 'Vue.js 3 Composition API Guide',
                'meta_description' => 'Learn Vue.js 3 Composition API with examples',
                'meta_keywords' => ['vue', 'javascript', 'frontend', 'composition-api'],
            ],
            [
                'title' => 'Python Data Analysis with Pandas',
                'slug' => 'python-data-analysis-with-pandas',
                'excerpt' => 'Master data analysis with Python and Pandas',
                'content' => 'Pandas is the go-to library for data manipulation...',
                'status' => 'published',
                'is_featured' => true,
                'is_editor_pick' => false,
                'views_count' => 1800,
                'published_at' => now()->subDays(18),
                'meta_title' => 'Python Data Analysis with Pandas',
                'meta_description' => 'Complete guide to data analysis with Python and Pandas',
                'meta_keywords' => ['python', 'pandas', 'data-analysis', 'machine-learning'],
            ],
            [
                'title' => 'AWS Lambda Functions Guide',
                'slug' => 'aws-lambda-functions-guide',
                'excerpt' => 'Build serverless applications with AWS Lambda',
                'content' => 'AWS Lambda enables serverless computing...',
                'status' => 'published',
                'is_featured' => false,
                'is_editor_pick' => false,
                'views_count' => 320,
                'published_at' => now()->subDays(21),
                'meta_title' => 'AWS Lambda Functions Complete Guide',
                'meta_description' => 'Learn to build serverless applications with AWS Lambda',
                'meta_keywords' => ['aws', 'lambda', 'serverless', 'cloud'],
            ],
        ];

        foreach ($articles as $articleData) {
            $author = $authors->random();
            $category = $categories->random();
            $randomTags = $tags->random(min(rand(2, 5), $tags->count()));

            $article = Article::create([
                'author_id' => $author->id,
                'category_id' => $category->id,
                'title' => $articleData['title'],
                'slug' => $articleData['slug'],
                'excerpt' => $articleData['excerpt'],
                'content' => $articleData['content']."\n\n".Str::random(1000),
                'status' => $articleData['status'],
                'is_featured' => $articleData['is_featured'],
                'is_editor_pick' => $articleData['is_editor_pick'],
                'views_count' => $articleData['views_count'],
                'published_at' => $articleData['published_at'],
                'meta_title' => $articleData['meta_title'],
                'meta_description' => $articleData['meta_description'],
                'meta_keywords' => $articleData['meta_keywords'],
                'featured_image' => 'https://picsum.photos/800/400?random='.rand(1, 1000),
                'image_alt' => $articleData['title'].' featured image',
            ]);

            // Attach tags
            $article->tags()->attach($randomTags->pluck('id'));
        }

        // Create additional random articles
        Article::factory(20)->create()->each(function ($article) use ($tags) {
            $randomTags = $tags->random(min(rand(1, 4), $tags->count()));
            $article->tags()->attach($randomTags->pluck('id'));
        });
    }
}
