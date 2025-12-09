<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'SIMONTA BENCANA API Documentation',
                'description' => 'Sistem Informasi Manajemen Bencana Terpadu - RESTful API untuk pengelolaan data bencana, laporan, monitoring, dan integrasi dengan BMKG.',
                'version' => '1.0.0',
                'contact' => [
                    'name' => 'API Support',
                    'email' => 'support@simonta-bencana.id',
                ],
                'servers' => [
                    [
                        'url' => env('APP_URL', 'http://localhost:8000').'/api',
                        'description' => 'Development server',
                    ],
                ],
            ],

            'routes' => [
                /*
                 * Route for accessing api documentation interface
                 */
                'api' => 'api/documentation',
            ],
            'paths' => [
                /*
                 * Edit to include full URL in ui for assets
                 */
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),

                /*
                * Edit to set path where swagger ui assets should be stored
                */
                'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),

                /*
                 * File name of the generated json documentation file
                 */
                'docs_json' => 'api-docs.json',

                /*
                 * File name of the generated YAML documentation file
                 */
                'docs_yaml' => 'api-docs.yaml',

                /*
                 * Set this to `json` or `yaml` to determine which documentation file to use in UI
                 */
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

                /*
                 * Absolute paths to directory containing the swagger annotations are stored.
                 */
                'annotations' => [
                    base_path('app/Http/Controllers'),
                ],
            ],
        ],
    ],
    'defaults' => [
        'routes' => [
            /*
             * Route for accessing parsed swagger annotations.
             */
            'docs' => 'docs',

            /*
             * Route for Oauth2 authentication callback.
             */
            'oauth2_callback' => 'api/oauth2-callback',

            /*
             * Middleware allows to prevent unexpected access to API documentation
             */
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],

            /*
             * Route Group options
             */
            'group_options' => [],
        ],

        'paths' => [
            /*
             * Absolute path to location where parsed annotations will be stored
             */
            'docs' => storage_path('api-docs'),

            /*
             * Absolute path to directory where to export views
             */
            'views' => base_path('resources/views/vendor/l5-swagger'),

            /*
             * Edit to set the api's base path
             */
            'base' => env('L5_SWAGGER_BASE_PATH', null),

            /*
             * Edit to set path where swagger ui assets should be stored
             */
            'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),

            /*
             * Absolute path to directories that should be exclude from scanning
             */
            'excludes' => [],
        ],

        /*
         * API security definitions. Will be generated into documentation file.
        */
        'securityDefinitions' => [
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                    'description' => 'JWT authentication token. Dapatkan token dengan melakukan login ke endpoint /api/auth/login menggunakan username "admintest" dan password "123456".',
                ],
            ],
            'security' => [
                [
                    'bearerAuth' => [],
                ],
            ],
        ],

        /*
         * Set this to `true` in development to generate specs always on start.
         */
        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),

        /*
         * Set this to `true` to generate swagger when on production environment.
         */
        'generate_on_production' => env('L5_SWAGGER_GENERATE_ON_PRODUCTION', false),

        /*
         * Set to `true` to generate docs only if it does not exist.
         * Set to `true` to use the already generated docs (if they exist),
         * only generate docs when no docs are available.
         */
        'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),

        /*
         * Define used for the @OA\Info annotation, will be automatically generated if not set.
         */
        'proxy' => false,

        /*
         * Define config for additional headers which will be added to the swagger-ui api call.
         */

        /*
         * Operation filter used to filter out specific operations with the condition "prefix".
         * E.g. to only generate operations for the "api/v1" routes:
         * 'operations_filter' => [
         *      'prefix' => 'api/v1'
         *  ]
         */
        'operations_filter' => [],

        /*
         * Operation sort order
         */
        'operations_sort' => null,

        /*
         * Validator URL
         */
        'validator_url' => null,

        /*
         * Additional config URL
         */
        'additional_config_url' => null,

        /*
         * UI Customization for Documentation, more info: https://swagger.io/tools/swagger-ui/customization/
         */
        'ui' => [
            'display' => [
                /*
                 * Controls the default expansion setting for the operations and tags. It can be :
                 * 'list' (expands only the tags),
                 * 'full' (expands the tags and operations),
                 * 'none' (expands nothing).
                 */
                'default_models_expand_depth' => env('L5_SWAGGER_UI_DEFAULT_MODELS_EXPAND_DEPTH', 1),

                /*
                 * Controls the display of operation request duration before the response.
                 */
                'display_request_duration' => env('L5_SWAGGER_UI_DISPLAY_REQUEST_DURATION', false),

                /*
                 * Controls the default expansion setting for the operations and tags.
                 */
                'doc_expansion' => env('L5_SWAGGER_UI_DOC_EXPANSION', 'none'),

                /*
                 * Controls whether the "Try it out" section is enabled.
                 */
                'try_it_out_enabled' => env('L5_SWAGGER_UI_TRY_IT_OUT_ENABLED', true),

                /*
                 * OAuth redirect URL.
                 */
                'oauth2_redirect_url' => env('L5_SWAGGER_UI_OAUTH2_REDIRECT_URL', null),

                /*
                 * If set, enables filtering. The top bar will show an edit box that
                 * you can use to filter the tagged operations that are shown. Can be
                 * Boolean to enable or disable, or a string, in which case filtering
                 * will be enabled using that string as the filter expression.
                 */
                'filter' => env('L5_SWAGGER_UI_FILTERS', true), // true | false
                /*
                 * If set, enables a deep link for each operation.
                 */
                'deep_linking' => env('L5_SWAGGER_UI_DEEP_LINKING', true),

                /*
                 * Controls the display of operationId in operations list.
                 */
                'display_operation_id' => env('L5_SWAGGER_UI_DISPLAY_OPERATION_ID', false),

                /*
                 * Controls whether "Authorize" button is displayed or not.
                 */
                'show_extensions' => env('L5_SWAGGER_UI_SHOW_EXTENSIONS', false),

                /*
                 * Controls the display of vendor extension (x-) fields and values for Operations, Parameters, and Schema.
                 */
                'show_common_extensions' => env('L5_SWAGGER_UI_SHOW_COMMON_EXTENSIONS', false),

                /*
                 * List of HTTP methods that have the "Try it out" feature enabled.
                 * An empty array disables "Try it out" for all operations.
                 * This does not affect the Swagger UI.
                 */
                'supported_submit_methods' => env('L5_SWAGGER_UI_SUPPORTED_SUBMIT_METHODS', [
                    'get',
                    'post',
                    'put',
                    'delete',
                    'patch'
                ]),

                /*
                 * OAuth default client id.
                 */
                'oauth_default_client_id' => env('L5_SWAGGER_UI_OAUTH_DEFAULT_CLIENT_ID', null),

                /*
                 * OAuth default client secret.
                 */
                'oauth_default_client_secret' => env('L5_SWAGGER_UI_OAUTH_DEFAULT_CLIENT_SECRET', null),

                /*
                 * OAuth default realm.
                 */
                'oauth_default_realm' => env('L5_SWAGGER_UI_OAUTH_DEFAULT_REALM', null),

                /*
                 * OAuth default app name.
                 */
                'oauth_default_app_name' => env('L5_SWAGGER_UI_OAUTH_DEFAULT_APP_NAME', null),

                /*
                 * OAuth default scopes.
                 */
                'oauth_default_scopes' => env('L5_SWAGGER_UI_OAUTH_DEFAULT_SCOPES', null),

                /*
                 * OAuth additional query string parameters.
                 */
                'oauth_additional_query_string_params' => env('L5_SWAGGER_UI_OAUTH_ADDITIONAL_QUERY_STRING_PARAMS', []),

                /*
                 * If set to true, it persists authorization data, and it would not be lost on browser close/refresh.
                 */
                'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', false),

                'oauth2' => [
                    /*
                     * If set to true, adds PKCE to AuthorizationCodeGrant flow
                     */
                    'use_pkce_with_authorization_code_grant' => false,
                ],
            ],
        ],
        /*
         * Constants which can be used in annotations
         */
        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://my-default-host.com'),
        ],
    ],
];