# CommerceCore SwaggerUI for WordPress REST API

## Description

SwaggerUI used to make WordPress REST API endpoint have a interactive UI, so we can check our API endpoint directly from the website it self.

### Features

- Support for `GET`, `POST`, `PUT`, `PATCH` and `DELETE` request methods
- All WP REST API routes are included automatically except default Wordpress routes

## REST API Customization

To customize how your created endpoints are shown at Swagger, here is an example with all the possible arguments you can add to your route:

### GET

```php
register_rest_route(
    'pet',
    '/(?P<petId>\d+)',
    [
        'methods'              => 'GET',
        'callback'             => $service->get_callback(),
        'summary'              => 'Find pet by ID',
        'description'          => 'Returns a single pet',
        'produces'             => ['application/json', 'application/xml'],
        'responses'            => [
            '200' => [
                'description' => 'successful operation',
                'schema'      => [
                    'type'       => 'object',
                    'required'   => ['name', 'photoUrls'],
                    'properties' => [
                        'id' => [
                            'type'   => 'integer',
                            'format' => 'int64',
                        ],
                        'name' => [
                            'type'    => 'string',
                            'example' => 'doggie',
                        ],
                        'status' => [
                            'type'        => 'string',
                            'description' => 'pet status in the store',
                            'enum'        => ['available', 'pending', 'sold']
                        ]
                    ],
                    'example'    => [
                        'id'     => 1,
                        'name'   => 'doggie',
                        'status' => ' available'
                    ],
                    'xml' => [
                        'name' => 'Pet'
                    ]
                ]
            ],
            '400' => [
                'description' => 'Invalid ID supplied'
            ],
            '404' => [
                'description' => 'Pet not found'
            ]

        ],
        'args'                 => [
            'petId'   => [
                'in'          => 'path',
                'description' => 'ID of pet to return',
                'required'    => true,
                'type'        => 'integer',
                'format'      => 'int64'
            ]
        ],
        'permission_callback'  => '__return_true'
    ]
)
```

### POST

```php
register_rest_route(
    'user',
    '/',
[
  'methods'              => 'POST',
  'callback'             => $service->get_callback(),
  'summary'              => 'Create user',
  'description'          => 'This can only be done by the logged in user.',
  'consumes'             => ['application/json'],
  'produces'             => ['application/json', 'application/xml'],
  'responses'            => [
      'default' => [
          'description' => 'successful operation',
      ]
  ],
  'args'                 => [
      'body'   => [
          'in'          => 'body',
          'description' => 'Created user object',
          'required'    => true,
          'type'        => 'object',
          'schema'      => [
              'type'       => 'object',
              'properties' => [
                  'id' => [
                      'type'   => 'integer',
                      'format' => 'int64',
                  ],
                  'username' => [
                      'type' => 'string'
                  ],
                  'email' => [
                      'type' => 'string'
                  ],
                  'password' => [
                      'type' => 'string'
                  ],
              ],
              'example' => [
                  'id'       => 0,
                  'username' => 'string',
                  'email'    => 'string',
                  'password' => 'string',
              ]
          ]
      ]
  ],
  'permission_callback'  => '__return_true'
]
)
```

> These examples were based on the defaults presented at [Swagger Editor](https://editor.swagger.io)

## Guide

If you need help undesrtadning any of of the parameters used on this documentation, please refer to [Swagger Documentation](https://swagger.io/docs/specification/basic-structure/).  
Here is some useful links:

- [Adding Examples](https://swagger.io/docs/specification/adding-examples/)
- [Describing Responses](https://swagger.io/docs/specification/describing-responses/)
