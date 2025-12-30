<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class GeneratePostmanCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:postman';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Postman collection from API routes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $collection = [
            'info' => [
                'name' => config('app.name') . ' API',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
            ],
            'variable' => [
                [
                    'key' => 'base_url',
                    'value' => url('/'),
                    'type' => 'string'
                ]
            ],
            'item' => []
        ];

        $routes = Route::getRoutes();

        $groups = [];

        foreach ($routes as $route) {
            $uri = $route->uri();

            // Heuristic to filter API routes. 
            // Often they start with 'api/' or have 'api' middleware.
            // We'll check for 'api' prefix or 'api' middleware presence in the stack.
            $middleware = $route->gatherMiddleware();
            $isApi = Str::startsWith($uri, 'api/') || in_array('api', $middleware);
            
            if (!$isApi) {
                // If strictly not api, skip? 
                // Let's assume user wants to document everything that looks like an API.
                // But typically web routes (login, etc) might confuse 'api' collection.
                // If it starts with 'sanctum' or '_ignition', skip.
                if (Str::startsWith($uri, 'sanctum') || Str::startsWith($uri, '_ignition')) {
                    continue; 
                }
                // If it's the default '/' route, maybe skip or keep?
                if ($uri === '/') continue;
            }

            // Exclude specific framework routes
            if (Str::startsWith($uri, '_ignition')) continue;
            if (Str::startsWith($uri, 'sanctum')) continue;

            $methods = $route->methods();
            $method = $methods[0];
            if ($method === 'HEAD') continue; 

            // Group by first segment after 'api/' if present, or just first segment
            $pathParts = explode('/', $uri);
            $groupName = 'General';
            
            if ($pathParts[0] === 'api' && isset($pathParts[1])) {
                $groupName = ucfirst($pathParts[1]);
            } elseif (count($pathParts) > 0) {
                $groupName = ucfirst($pathParts[0]);
            }

            $parameters = [];
             // Extract parameters {param}
            preg_match_all('/\{(.*?)\}/', $uri, $matches);
            foreach ($matches[1] as $param) {
                $itemParam = str_replace('?', '', $param); // Handle optional params
                 $parameters[] = [
                    'key' => $itemParam,
                    'value' => '{{' . $itemParam . '}}',
                    'description' => "The $itemParam"
                ];
            }

            // Construct the item
            $item = [
                'name' => $uri,
                'request' => [
                    'method' => $method,
                    'header' => [
                        [
                            'key' => 'Accept',
                            'value' => 'application/json',
                            'type' => 'text'
                        ]
                    ],
                    'url' => [
                        'raw' => '{{base_url}}/' . $uri,
                        'host' => ['{{base_url}}'],
                        'path' => $pathParts,
                        'variable' => $parameters
                    ]
                ]
            ];

            if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                $item['request']['body'] = [
                    'mode' => 'raw',
                    'raw' => json_encode([], JSON_PRETTY_PRINT),
                    'options' => [
                       'raw' => [
                           'language' => 'json'
                       ]
                   ]
                ];
            }

            $groups[$groupName][] = $item;
        }

        // Flatten groups into folders
        foreach ($groups as $name => $items) {
            $collection['item'][] = [
                'name' => $name,
                'item' => $items
            ];
        }

        $json = json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        $fileName = 'postman_collection.json';
        $path = base_path($fileName);
        file_put_contents($path, $json);

        $this->info("Postman collection generated successfully at: $path");
        $this->info("Import this file into Postman to view your collection.");
    }
}
