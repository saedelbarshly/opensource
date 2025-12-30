<?php

namespace App\Console\Commands;

use Closure;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\RequiredIf;
use Illuminate\Validation\Rules\Unique;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

class GeneratePostmanCollection extends Command
{
    protected $signature = 'postman:generate
                            {--update : Update existing collection (merge new routes)}
                            {--output=postman_collection.json : Output file name}
                            {--exclude=* : Exclude routes containing these patterns}';

    protected $description = 'Generate Postman collection from Laravel routes';

    /**
     * Routes/paths to always exclude
     */
    protected array $excludedPatterns = [
        'telescope',
        'horizon',
        'sanctum',
        '_ignition',
        'debugbar',
    ];

    /**
     * User types that require user_type header
     * Maps folder name to user_type value
     */
    protected array $userTypeFolders = [
        'dashboard' => 'admin',
        'admin' => 'admin',
        'client' => 'client',
        'driver' => 'driver',
        'vendor' => 'vendor',
        'provider' => 'provider',
        'merchant' => 'merchant',
        'user' => 'client',
    ];

    /**
     * Sample values for common field names
     * Uses {{variable}} syntax to reference collection variables
     */
    protected array $sampleValues = [
        // Auth - using collection variables
        'email' => '{{test_email}}',
        'auth' => '{{test_phone}}',
        'password' => '{{password}}',
        'password_confirmation' => '{{password}}',
        'current_password' => '{{password}}',

        // Phone - using collection variables
        'phone' => '{{test_phone}}',
        'phone_code' => '{{phone_code}}',

        // OTP
        'code' => '{{otp_code}}',
        'otp' => '{{otp_code}}',
        'reset_code' => '{{otp_code}}',

        // Device - using collection variables
        'device_token' => '{{device_token}}',
        'new_device_token' => '{{new_device_token}}',
        'fcm_token' => '{{device_token}}',
        'type' => '{{type}}',
        'auth_type' => '{{auth_type}}',

        // User info
        'name' => 'Mohamed Elsharkawy',
        'full_name' => 'Mohamed Elsharkawy',
        'first_name' => 'Mohamed',
        'last_name' => 'Elsharkawy',
        'username' => 'mohamed-elsharkawy',

        // Content
        'title' => 'Sample Title',
        'description' => 'Sample description text',
        'content' => 'Sample content text',
        'body' => 'Sample body text',
        'message' => 'Sample message',
        'subject' => 'Sample subject',

        // Status/Type
        'status' => '1',
        'is_active' => '1',
        'is_featured' => '0',

        // Numeric
        'price' => '100.00',
        'amount' => '50',
        'quantity' => '1',
        'order' => '1',
        'sort_order' => '1',

        // Location
        'lat' => '30.0444',
        'lng' => '31.2357',
        'latitude' => '30.0444',
        'longitude' => '31.2357',
        'address' => '123 Main Street',
        'city' => 'Cairo',
        'country' => 'Egypt',
        'zip_code' => '12345',

        // Dates
        'date' => '2024-01-01',
        'start_date' => '2024-01-01',
        'end_date' => '2024-12-31',
        'birth_date' => '1990-01-01',

        // IDs (UUIDs)
        'user_id' => '',
        'category_id' => '',
        'parent_id' => '',

        // Media - using collection variables
        'avatar' => '{{avatar}}',

        // Locale - using collection variables
        'locale' => '{{locale}}',
        'language' => '{{locale}}',
    ];

    /**
     * Field type mappings based on field name patterns
     */
    protected array $fieldTypePatterns = [
        'file' => ['image', 'avatar', 'photo', 'file', 'attachment', 'document', 'video', 'media', 'logo', 'icon', 'cover', 'banner', 'thumbnail'],
        'text' => ['*'],
    ];

    public function handle(): void
    {
        $excludeOptions = $this->option('exclude');
        if (!empty($excludeOptions)) {
            $this->excludedPatterns = array_merge($this->excludedPatterns, $excludeOptions);
        }

        $outputFile = $this->option('output');

        $collection = $this->option('update') && file_exists(base_path($outputFile))
            ? $this->updateExistingCollection($outputFile)
            : $this->createNewCollection();

        $json = json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        file_put_contents(base_path($outputFile), $json);

        $routeCount = $this->countRoutes($collection['item']);
        $this->info("Postman collection generated successfully!");
        $this->info("Output: {$outputFile}");
        $this->info("Total routes: {$routeCount}");
    }

    /**
     * Count total routes in collection
     */
    private function countRoutes(array $items): int
    {
        $count = 0;
        foreach ($items as $item) {
            if (isset($item['request'])) {
                $count++;
            }
            if (isset($item['item'])) {
                $count += $this->countRoutes($item['item']);
            }
        }
        return $count;
    }

    /**
     * Update existing collection by merging new routes
     */
    private function updateExistingCollection(string $outputFile): array
    {
        $existingCollection = json_decode(file_get_contents(base_path($outputFile)), true);

        if (!$existingCollection || !isset($existingCollection['item'])) {
            $this->warn('Invalid existing collection, creating new one...');
            return $this->createNewCollection();
        }

        $newStructure = $this->generateFolderStructure();
        $existingCollection['item'] = $this->mergeItems($existingCollection['item'], $newStructure);

        $this->info('Collection updated with new routes.');

        return $existingCollection;
    }

    /**
     * Merge new items into existing structure
     */
    private function mergeItems(array $existing, array $new): array
    {
        $existingByName = [];
        foreach ($existing as $index => $item) {
            $existingByName[$item['name']] = $index;
        }

        foreach ($new as $newItem) {
            $name = $newItem['name'];

            if (isset($existingByName[$name])) {
                $existingIndex = $existingByName[$name];

                if (isset($newItem['item']) && isset($existing[$existingIndex]['item'])) {
                    $existing[$existingIndex]['item'] = $this->mergeItems(
                        $existing[$existingIndex]['item'],
                        $newItem['item']
                    );
                }
            } else {
                $existing[] = $newItem;
            }
        }

        return $existing;
    }

    /**
     * Create new collection from scratch
     */
    private function createNewCollection(): array
    {
        $collectionId = Str::uuid()->toString();

        return [
            'info' => [
                '_postman_id' => $collectionId,
                'name' => config('app.name') . ' API',
                'description' => 'Auto-generated API collection for ' . config('app.name'),
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
            ],
            'variable' => $this->getCollectionVariables(),
            'item' => $this->generateFolderStructure(),
            'event' => [$this->getPreRequestScript()],
            'auth' => [
                'type' => 'bearer',
                'bearer' => [
                    [
                        'key' => 'token',
                        'value' => '{{token}}',
                        'type' => 'string'
                    ]
                ]
            ]
        ];
    }

    /**
     * Get collection variables
     */
    private function getCollectionVariables(): array
    {
        return [
            // Base URL
            [
                'key' => 'URL',
                'value' => 'http://127.0.0.1:8000/api',
                'type' => 'string',
                'description' => 'Base API URL'
            ],

            // Auth Tokens
            [
                'key' => 'token',
                'value' => '',
                'type' => 'string',
                'description' => 'Current active bearer token'
            ],
            [
                'key' => 'admin_token',
                'value' => '',
                'type' => 'string',
                'description' => 'Admin bearer token'
            ],
            [
                'key' => 'client_token',
                'value' => '',
                'type' => 'string',
                'description' => 'Client bearer token'
            ],
            [
                'key' => 'driver_token',
                'value' => '',
                'type' => 'string',
                'description' => 'Driver bearer token'
            ],
            [
                'key' => 'dashboard_token',
                'value' => '',
                'type' => 'string',
                'description' => 'Dashboard/Admin bearer token'
            ],

            // Test Credentials
            [
                'key' => 'test_phone',
                'value' => '500000000',
                'type' => 'string',
                'description' => 'Test phone number for authentication'
            ],
            [
                'key' => 'phone_code',
                'value' => '966',
                'type' => 'string',
                'description' => 'Phone country code'
            ],
            [
                'key' => 'test_email',
                'value' => 'test@example.com',
                'type' => 'string',
                'description' => 'Test email for authentication'
            ],
            [
                'key' => 'password',
                'value' => 'password',
                'type' => 'string',
                'description' => 'Test password'
            ],

            // Device Info
            [
                'key' => 'device_token',
                'value' => 'test_device_token',
                'type' => 'string',
                'description' => 'FCM/Push notification device token'
            ],
            [
                'key' => 'new_device_token',
                'value' => '',
                'type' => 'string',
                'description' => 'New device token for token refresh'
            ],
            [
                'key' => 'type',
                'value' => 'ios',
                'type' => 'string',
                'description' => 'Device type: ios, android, huawei, web'
            ],

            // Auth Settings
            [
                'key' => 'auth_type',
                'value' => 'phone',
                'type' => 'string',
                'description' => 'Authentication type: phone or email'
            ],
            [
                'key' => 'locale',
                'value' => 'ar',
                'type' => 'string',
                'description' => 'Language locale: ar or en'
            ],

            // Media
            [
                'key' => 'avatar',
                'value' => '',
                'type' => 'string',
                'description' => 'Avatar media UUID'
            ],

            // OTP
            [
                'key' => 'otp_code',
                'value' => '',
                'type' => 'string',
                'description' => 'OTP verification code'
            ],
        ];
    }

    /**
     * Generate folder structure from routes
     */
    private function generateFolderStructure(): array
    {
        $structure = [];

        foreach (Route::getRoutes() as $route) {
            if ($this->shouldExcludeRoute($route->uri())) {
                continue;
            }

            $controllerAction = $route->getAction('controller');
            if (!$controllerAction || !str_contains($controllerAction, '@')) {
                continue;
            }

            [$controllerClass, $method] = explode('@', $controllerAction);

            $namespaceSegments = explode('\\', $controllerClass);
            $controllerName = str_replace('Controller', '', array_pop($namespaceSegments));
            $namespace = str_replace('App\Http\Controllers\\', '', implode('\\', $namespaceSegments));

            $folders = array_values(array_filter(explode('\\', $namespace), fn($f) => $f !== 'Api'));

            $currentLevel = &$structure;

            // Navigate/create folder structure with user_type scripts
            foreach ($folders as $index => $folder) {
                $isRootFolder = ($index === 0);
                $currentLevel = &$this->findOrCreateFolder($currentLevel, $folder, $isRootFolder);
            }

            // Navigate/create controller folder
            $currentLevel = &$this->findOrCreateFolder($currentLevel, $controllerName, false);

            // Add request item
            $currentLevel[] = $this->createRequestItem($route, $controllerName);
        }

        return array_values($structure);
    }

    /**
     * Check if route should be excluded
     */
    private function shouldExcludeRoute(string $uri): bool
    {
        foreach ($this->excludedPatterns as $pattern) {
            if (str_contains($uri, $pattern)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Find or create a folder in the structure
     */
    private function &findOrCreateFolder(array &$level, string $name, bool $isRootFolder = false): array
    {
        foreach ($level as &$item) {
            if (($item['name'] ?? '') === $name && isset($item['item'])) {
                return $item['item'];
            }
        }

        $folder = [
            'name' => $name,
            'item' => [],
        ];

        // Add user_type pre-request script for root folders
        if ($isRootFolder) {
            $userType = $this->getUserTypeForFolder($name);
            if ($userType) {
                $folder['event'] = [$this->getFolderPreRequestScript($userType, $name)];
                $folder['auth'] = $this->getFolderAuth($name);
            }
        }

        $level[] = $folder;

        return $level[array_key_last($level)]['item'];
    }

    /**
     * Get user_type value for a folder
     */
    private function getUserTypeForFolder(string $folderName): ?string
    {
        $folderLower = strtolower($folderName);

        if (isset($this->userTypeFolders[$folderLower])) {
            return $this->userTypeFolders[$folderLower];
        }

        // Check partial matches
        foreach ($this->userTypeFolders as $key => $value) {
            if (str_contains($folderLower, $key)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Get pre-request script for folder that adds user_type header
     */
    private function getFolderPreRequestScript(string $userType, string $folderName): array
    {
        $tokenVar = strtolower($folderName) . '_token';

        return [
            'listen' => 'prerequest',
            'script' => [
                'type' => 'text/javascript',
                'exec' => [
                    '// Auto-add user-type header for ' . $folderName . ' folder',
                    'pm.request.headers.add({',
                    '    key: "user-type",',
                    '    value: "' . $userType . '"',
                    '});',
                    '',
                    '// Use folder-specific token if available',
                    'const folderToken = pm.collectionVariables.get("' . $tokenVar . '");',
                    'if (folderToken) {',
                    '    pm.request.headers.add({',
                    '        key: "Authorization",',
                    '        value: "Bearer " + folderToken',
                    '    });',
                    '}',
                ]
            ]
        ];
    }

    /**
     * Get auth configuration for folder
     */
    private function getFolderAuth(string $folderName): array
    {
        $tokenVar = strtolower($folderName) . '_token';

        return [
            'type' => 'bearer',
            'bearer' => [
                [
                    'key' => 'token',
                    'value' => '{{' . $tokenVar . '}}',
                    'type' => 'string'
                ]
            ]
        ];
    }

    /**
     * Create a request item for Postman
     */
    private function createRequestItem($route, string $controllerName): array
    {
        $controllerAction = $route->getAction('controller');
        $methods = $route->methods();
        $httpMethod = $methods[0];
        $uri = $this->processUri($route->uri());

        $actionName = last(explode('@', $route->getAction()['uses'] ?? '')) ?: '';
        $name = $this->formatActionName($actionName) ?: Str::title(str_replace('.', ' ', $route->getName() ?? '')) ?: $uri;

        $requestItem = [
            'name' => $name,
            'request' => [
                'method' => in_array(strtoupper($httpMethod), ['PUT', 'PATCH']) ? 'POST' : strtoupper($httpMethod),
                'header' => $this->getRequestHeaders($route),
                'body' => $this->getRequestBody($controllerAction, $httpMethod),
                'url' => $this->parseUrl($uri),
                'description' => $this->getRouteDescription($controllerAction)
            ],
            'response' => []
        ];

        // Add auth script for login endpoints
        if ($this->isLoginEndpoint($uri)) {
            $requestItem['event'] = [$this->getLoginTestScript($controllerName)];
        }

        return $requestItem;
    }

    /**
     * Format action name to readable format
     */
    private function formatActionName(string $action): string
    {
        if (empty($action)) {
            return '';
        }

        return Str::title(
            trim(preg_replace('/(?<!^)[A-Z]/', ' $0', $action))
        );
    }

    /**
     * Check if endpoint is a login endpoint
     */
    private function isLoginEndpoint(string $uri): bool
    {
        return str_contains($uri, 'login') || str_contains($uri, 'auth/login');
    }

    /**
     * Get request headers based on route middleware
     */
    private function getRequestHeaders($route): array
    {
        $headers = [];

        $middlewares = $route->gatherMiddleware();

        if (in_array('user.type', $middlewares) || str_contains($route->uri(), 'admin')) {
            $headers[] = [
                'key' => 'user-type',
                'value' => 'admin',
                'type' => 'text',
                'description' => 'User type: admin or client'
            ];
        }

        return $headers;
    }

    /**
     * Process URI for Postman format
     */
    private function processUri(string $uri): string
    {
        $processed = preg_replace('/\{(\w+?)\??\}/', ':$1', $uri);
        return 'api/' . ltrim($processed, '/');
    }

    /**
     * Get request body from validation rules
     */
    private function getRequestBody(string $controllerAction, string $method): array
    {
        $rules = $this->getValidationRules($controllerAction);

        $formData = collect($rules)->flatMap(function ($rule, $field) {
            $data = [[
                'key' => $this->formatFieldName($field),
                'value' => $this->getSampleValue($field, $rule),
                'type' => $this->getFieldType($field, $rule),
                'description' => $this->formatRuleDescription($rule)
            ]];

            // Add confirmation field if needed
            if ($this->hasConfirmationRule($rule)) {
                $data[] = [
                    'key' => "{$field}_confirmation",
                    'value' => $this->getSampleValue($field, $rule),
                    'type' => 'text',
                    'description' => "Confirmation for {$field}"
                ];
            }

            return $data;
        })->values()->toArray();

        // Add _method for PUT/PATCH requests
        if (in_array(strtoupper($method), ['PUT', 'PATCH'])) {
            $formData[] = [
                'key' => '_method',
                'value' => strtoupper($method),
                'type' => 'text',
                'description' => 'HTTP method override for Laravel'
            ];
        }

        return [
            'mode' => 'formdata',
            'formdata' => $formData
        ];
    }

    /**
     * Check if rule has confirmation
     */
    private function hasConfirmationRule($rule): bool
    {
        if (is_string($rule)) {
            return str_contains($rule, 'confirmed');
        }

        if (is_array($rule)) {
            return in_array('confirmed', $rule);
        }

        return false;
    }

    /**
     * Format field name for nested fields
     */
    private function formatFieldName(string $field): string
    {
        if (!str_contains($field, '.')) {
            return $field;
        }

        $parts = explode('.', $field);
        $base = array_shift($parts);

        return $base . implode('', array_map(fn($p) => "[{$p}]", $parts));
    }

    /**
     * Get field type based on field name and rules
     */
    private function getFieldType(string $field, $rule): string
    {
        $ruleString = is_array($rule) ? implode('|', array_filter($rule, 'is_string')) : (string)$rule;

        // Check for file rules
        if (str_contains($ruleString, 'image') || str_contains($ruleString, 'file') || str_contains($ruleString, 'mimes')) {
            return 'file';
        }

        // Check field name patterns
        $fieldLower = strtolower($field);
        foreach ($this->fieldTypePatterns['file'] as $pattern) {
            if (str_contains($fieldLower, $pattern)) {
                return 'file';
            }
        }

        return 'text';
    }

    /**
     * Get validation rules from controller action
     */
    private function getValidationRules(string $controllerAction): array
    {
        if (!str_contains($controllerAction, '@')) {
            return [];
        }

        [$controller, $method] = explode('@', $controllerAction);

        try {
            $reflector = new ReflectionClass($controller);
            $parameters = $reflector->getMethod($method)->getParameters();

            foreach ($parameters as $parameter) {
                $class = $parameter->getType()?->getName();

                // Skip list requests
                if ($class === \App\Http\Requests\Api\General\ListRequest::class) {
                    continue;
                }

                if ($class && is_subclass_of($class, Request::class)) {
                    try {
                        $request = new $class();
                        return $request->rules();
                    } catch (Exception) {
                        continue;
                    }
                }
            }
        } catch (ReflectionException) {
            // Silently handle reflection errors
        }

        return [];
    }

    /**
     * Get sample value for a field
     */
    private function getSampleValue(string $field, $rule = null): string
    {
        // Direct match
        if (isset($this->sampleValues[$field])) {
            return $this->sampleValues[$field];
        }

        // Partial match
        $fieldLower = strtolower($field);
        foreach ($this->sampleValues as $key => $value) {
            if (str_contains($fieldLower, $key)) {
                return $value;
            }
        }

        // Check for boolean rules
        $ruleString = is_array($rule) ? implode('|', array_filter($rule, 'is_string')) : (string)$rule;
        if (str_contains($ruleString, 'boolean')) {
            return '1';
        }

        if (str_contains($ruleString, 'numeric') || str_contains($ruleString, 'integer')) {
            return '1';
        }

        return '';
    }

    /**
     * Format rule description for Postman
     */
    private function formatRuleDescription($rule): string
    {
        if ($rule instanceof Exists) {
            return 'Must exist in database';
        }

        if ($rule instanceof Unique) {
            return 'Must be unique';
        }

        if ($rule instanceof RequiredIf) {
            return 'Required conditionally';
        }

        if (is_array($rule)) {
            $descriptions = [];

            foreach ($rule as $r) {
                if ($r instanceof Exists) {
                    $descriptions[] = 'exists';
                } elseif ($r instanceof Unique) {
                    $descriptions[] = 'unique';
                } elseif ($r instanceof RequiredIf) {
                    $descriptions[] = 'required_if';
                } elseif ($r instanceof Closure) {
                    $closureName = $this->identifyClosureType($r);
                    if ($closureName) {
                        $descriptions[] = $closureName;
                    }
                } elseif (is_string($r)) {
                    $descriptions[] = $r;
                }
            }

            return implode(' | ', $descriptions);
        }

        return is_string($rule) ? $rule : '';
    }

    /**
     * Identify closure validation type
     */
    private function identifyClosureType(Closure $closure): string
    {
        try {
            $reflection = new ReflectionFunction($closure);
            $code = file($reflection->getFileName());
            $start = $reflection->getStartLine() - 1;
            $end = $reflection->getEndLine();
            $closureCode = strtolower(implode('', array_slice($code, $start, $end - $start)));

            $patterns = [
                'otp_validation' => ['otp', 'code', 'verify'],
                'password_check' => ['hash::check', 'password'],
                'auth_check' => ['auth()', 'auth()->'],
                'unique_check' => ['where', 'exists', 'count'],
            ];

            foreach ($patterns as $name => $keywords) {
                $matches = 0;
                foreach ($keywords as $keyword) {
                    if (str_contains($closureCode, $keyword)) {
                        $matches++;
                    }
                }

                if ($matches >= 2) {
                    return $name;
                }
            }
        } catch (Exception) {
            // Silently handle errors
        }

        return 'custom_validation';
    }

    /**
     * Parse URL for Postman format
     */
    private function parseUrl(string $uri): array
    {
        $urlParts = parse_url($uri);
        $path = array_values(array_filter(
            explode('/', $urlParts['path'] ?? $uri),
            fn($p) => $p !== 'api' && $p !== ''
        ));

        $query = [];
        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $queryParams);
            foreach ($queryParams as $key => $value) {
                $query[] = [
                    'key' => $key,
                    'value' => $value,
                    'disabled' => true
                ];
            }
        }

        return [
            'raw' => '{{URL}}/' . implode('/', $path),
            'host' => ['{{URL}}'],
            'path' => $path,
            'query' => $query
        ];
    }

    /**
     * Get route description from docblock
     */
    private function getRouteDescription(string $controllerAction): string
    {
        if (!str_contains($controllerAction, '@')) {
            return '';
        }

        [$controllerClass, $method] = explode('@', $controllerAction);

        try {
            $reflection = new ReflectionMethod($controllerClass, $method);
            $docComment = $reflection->getDocComment();

            if ($docComment) {
                $lines = preg_split('/\r\n|\r|\n/', $docComment);

                foreach ($lines as $line) {
                    $line = trim($line, "/* \t");

                    if (empty($line) || str_starts_with($line, '@')) {
                        continue;
                    }

                    return $line;
                }
            }
        } catch (ReflectionException) {
            // Silently handle reflection errors
        }

        return '';
    }

    /**
     * Get pre-request script for collection
     */
    private function getPreRequestScript(): array
    {
        return [
            'listen' => 'prerequest',
            'script' => [
                'type' => 'text/javascript',
                'exec' => [
                    '// Auto-add common headers',
                    'pm.request.headers.add({',
                    '    key: "Accept",',
                    '    value: "application/json"',
                    '});',
                    '',
                    'pm.request.headers.add({',
                    '    key: "Accept-Language",',
                    '    value: pm.collectionVariables.get("locale") || "en"',
                    '});',
                    '',
                    'pm.request.headers.add({',
                    '    key: "Timezone",',
                    '    value: "Africa/Cairo"',
                    '});',
                ]
            ]
        ];
    }

    /**
     * Get login test script to save token
     */
    private function getLoginTestScript(string $platform): array
    {
        $tokenVar = strtolower($platform) . '_token';

        return [
            'listen' => 'test',
            'script' => [
                'type' => 'text/javascript',
                'exec' => [
                    'pm.test("Login successful", function() {',
                    '    pm.response.to.have.status(200);',
                    '    ',
                    '    const response = pm.response.json();',
                    '    ',
                    '    if (response && response.data && response.data.token) {',
                    '        pm.collectionVariables.set("token", response.data.token);',
                    '        pm.collectionVariables.set("' . $tokenVar . '", response.data.token);',
                    '        console.log("Token saved successfully");',
                    '    }',
                    '});',
                ]
            ]
        ];
    }
}