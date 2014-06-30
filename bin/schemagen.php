#!/usr/bin/env php
<?php

# this is an unrighteous mess of code; avoid harsh judgement; avoid eye contact
# someday, it'd be nice if schema.json were generated from a more canonical format
#
# cat api.html | ./bin/regenerate.php > src/RavelryApi/schema.json

$dom = new \DOMDocument();

if (!@$dom->loadHTML(stream_get_contents(STDIN))) {
    throw new \Exception('Error loading HTML');
}

$xpath = new \DOMXPath($dom);

$schema = [
    'baseUrl' => 'https://api.ravelry.com/',
    'operations' => [],
    'models' => [
        'json' => [
            'type' => 'object',
            'additionalProperties' => [
                'location' => 'json',
            ],
        ]
    ]
];

$apiModels = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " model_attributes ")]');

for ($i = 0; $i < $apiModels->length; $i += 1) {
    $apiModel = $apiModels->item($i);

    $schemaModel = [
        'type' => 'object',
        'properties' => [],
    ];

    $schemaModelName = (string) $apiModel->previousSibling->previousSibling->attributes->getNamedItem('id')->nodeValue;

    $attributes = $xpath->query('.//table[contains(concat(" ", normalize-space(@class), " "), " parameters ")]/tbody/tr', $apiModel);

    for ($j = 0; $j < $attributes->length; $j += 1) {
        $attribute = $attributes->item($j);

        $attributeName = $xpath->query('.//td[contains(concat(" ", normalize-space(@class), " "), " name ")]', $attribute)->item(0)->textContent;
        $attributeType = $xpath->query('.//td[contains(concat(" ", normalize-space(@class), " "), " type ")]', $attribute)->item(0)->textContent;
        $attributeRequired = $xpath->query('.//td[contains(concat(" ", normalize-space(@class), " "), " required ")]', $attribute)->item(0)->textContent;
        $attributeDescription = $xpath->query('.//td[contains(concat(" ", normalize-space(@class), " "), " description ")]', $attribute)->item(0)->textContent;

        $schemaModel['properties'][$attributeName] = [
            'type' => canonicalType($attributeType),
            'required' => ('Yes' == $attributeRequired) ? true : false,
            'description' => trim($attributeDescription),
        ];

        if (preg_match('#(\sCurrently\s+accepted\s+types:\s+|\sShould\s+be\s+one\s+of:\s+|\sAccepted\s+options\sare:\s+|\s+\-\s+one\s+of:\s+|\sOptions\s+are:\s+|\sAccepted\s+options:\s+|\sOne\s+of:\s+)([^\.]+)\.?#mi', $schemaModel['properties'][$attributeName]['description'], $match)) {
            $schemaModel['properties'][$attributeName]['enum'] = preg_split('#,\s+#', preg_replace('/ and /', ' ', str_replace('"', '', trim($match[2]))));
            $schemaModel['properties'][$attributeName]['description'] = trim(preg_replace('#( +)#', ' ', str_replace(trim($match[0]), '', $schemaModel['properties'][$attributeName]['description'])));
        }
    }

    $schema['models'][strtolower($schemaModelName)] = $schemaModel;
}

$schema['models']['message_post_result']['properties']['recipient_username']['type'] = 'string';

$apiMethods = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " api_method ")]');

for ($i = 0; $i < $apiMethods->length; $i += 1) {
    $apiMethod = $apiMethods->item($i);

    $schemaOperationName = null;
    $schemaOperation = [
        'httpMethod' => null,
        'uri' => null,
        'responseModel' => 'json',
        'documentationUrl' => null,
        'parameters' => [],
    ];

    $name = $xpath->query('.//h3', $apiMethod)->item(0);

    $schemaOperation['documentationUrl'] = 'http://www.ravelry.com/api#' . rawurlencode($name->attributes->getNamedItem('id')->nodeValue);

    $name = $name->textContent;
    $name = trim(preg_replace('/(\s+)/m', ' ', $name));
    $name = preg_replace('#^(.*) authenticated$#', '$1', $name);
    $schemaOperation['_cliname'] = trim(preg_replace('#/#', ':', preg_replace('#_#', '-', $name)), ':');
    $schemaOperationName = trim(preg_replace('#/#', '_', preg_replace('#_#', '', $name)), '_');

    $description = $xpath->query('.//div[contains(concat(" ", normalize-space(@class), " "), " description ")]', $apiMethod)->item(0)->textContent;
    $schemaOperation['description'] = trim(preg_replace('#(\s+)#m', ' ', $description));

    $uri = $xpath->query('.//div[contains(concat(" ", normalize-space(@class), " "), " uri ")]', $apiMethod)->item(0)->textContent;
    $uri = trim(preg_replace('#(\s+)#m', ' ', $uri));

    preg_match('#^([A-Z]+) (.*)$#', $uri, $uriPieces);
    $schemaOperation['httpMethod'] = $uriPieces[1];
    $schemaOperation['uri'] = $uriPieces[2];

    preg_match_all('#\{([^\}]+)\}#', $schemaOperation['uri'], $uriParameters, PREG_SET_ORDER);

    foreach ($uriParameters as $uriParameter) {
        $schemaOperation['parameters'][$uriParameter[1]] = [
            'location' => 'uri',
            'required' => true,
            'type' => 'string',
        ];
    }

    parseParameters(
        $schema,
        $schemaOperation,
        $xpath,
        $xpath->query('.//div[contains(concat(" ", normalize-space(@class), " "), " uri_parameters ")]/table/tbody/tr', $apiMethod),
        [
            'location' => 'uri',
        ]
    );

    parseParameters(
        $schema,
        $schemaOperation,
        $xpath,
        $xpath->query('.//div[contains(concat(" ", normalize-space(@class), " "), " input_parameters ")]/table/tbody/tr', $apiMethod)
    );

    if ('upload_image' == $schemaOperationName) {
        $ten = $schemaOperation['parameters']['file9'];
        unset($schemaOperation['parameters']['file9']);
        $schemaOperation['parameters']['file2']['description'] = 'File data, third file';
        $schemaOperation['parameters']['file3'] = $schemaOperation['parameters']['file1'];
        $schemaOperation['parameters']['file3']['description'] = 'File data, fourth file';
        $schemaOperation['parameters']['file4'] = $schemaOperation['parameters']['file1'];
        $schemaOperation['parameters']['file4']['description'] = 'File data, fifth file';
        $schemaOperation['parameters']['file5'] = $schemaOperation['parameters']['file1'];
        $schemaOperation['parameters']['file5']['description'] = 'File data, sixth file';
        $schemaOperation['parameters']['file6'] = $schemaOperation['parameters']['file1'];
        $schemaOperation['parameters']['file6']['description'] = 'File data, seventh file';
        $schemaOperation['parameters']['file7'] = $schemaOperation['parameters']['file1'];
        $schemaOperation['parameters']['file7']['description'] = 'File data, eighth file';
        $schemaOperation['parameters']['file8'] = $schemaOperation['parameters']['file1'];
        $schemaOperation['parameters']['file8']['description'] = 'File data, ninth file';
        $schemaOperation['parameters']['file9'] = $ten;
    }

    $schemaOperation['parameters']['etag'] = [
        'type' => 'string',
        'location' => 'header',
        'description' => 'The HTTP Etag to present upstream.',
        'required' => false,
        'sentAs' => 'If-None-Match',
        'filters' => [
            'RavelryApi\\TypeConversion::toQuotedEtag',
        ],
    ];

    $schemaOperation['parameters']['extras'] = [
        'type' => 'boolean',
        'location' => 'query',
        'description' => 'Include extra "polling" data in the result.',
        'required' => false,
    ];

    $schemaOperation['parameters']['debug'] = [
        'type' => 'boolean',
        'location' => 'query',
        'description' => 'Encourage Ravelry servers to retain debug information about this API call.',
        'required' => false,
    ];

    foreach ($schemaOperation['parameters'] as $k => &$v) {
        if (!isset($v['location'])) {
            $v['location'] = 'json';
        }

        if ((isset($v['type'])) && ('float' == $v['type'])) {
            // i wish guzzle would run filters before type checks
            unset($v['type']);
            $v['filters'][] = [
                'method' => 'RavelryApi\\TypeConversion::toFloat',
                'args' => [
                    '@value',
                    '@api',
                ]
            ];
        }
    }

    $schema['operations'][$schemaOperationName] = $schemaOperation;
}

$schema['operations']['patterns_search']['additionalParameters'] = [
    '_cliname' => 'facet',
    'description' => 'Facet key/value pairs derived from ravelry.com interface',
    'location' => 'query',
    'type' => 'string',
];

$schema['operations']['projects_search']['additionalParameters'] = [
    '_cliname' => 'facet',
    'description' => 'Facet key/value pairs derived from ravelry.com interface',
    'location' => 'query',
    'type' => 'string',
];

$schema['operations']['stash_search']['additionalParameters'] = [
    '_cliname' => 'facet',
    'description' => 'Facet key/value pairs derived from ravelry.com interface',
    'location' => 'query',
    'type' => 'string',
];

$schema['operations']['yarns_search']['additionalParameters'] = [
    '_cliname' => 'facet',
    'description' => 'Facet key/value pairs derived from ravelry.com interface',
    'location' => 'query',
    'type' => 'string',
];

$schema['operations']['app_config_set']['additionalParameters'] = $schema['operations']['app_config_set']['parameters']['(key_names)'];
$schema['operations']['app_config_set']['additionalParameters']['_cliname'] = 'set';
$schema['operations']['app_config_set']['additionalParameters']['location'] = 'query';
unset($schema['operations']['app_config_set']['parameters']['(key_names)']);

unset($schema['operations']['app_config_get']['parameters']['keys']['type']);
$schema['operations']['app_config_get']['parameters']['keys']['filters'][] = [
    'method' => 'RavelryApi\\TypeConversion::toSpaceSeparated',
    'args' => [
        '@value',
        '@api',
    ]
];

$schema['operations']['app_config_delete']['parameters']['keys']['location'] = 'query';
unset($schema['operations']['app_config_delete']['parameters']['keys']['type']);
$schema['operations']['app_config_delete']['parameters']['keys']['filters'][] = [
    'method' => 'RavelryApi\\TypeConversion::toSpaceSeparated',
    'args' => [
        '@value',
        '@api',
    ]
];

$schema['operations']['app_data_set']['additionalParameters'] = $schema['operations']['app_data_set']['parameters']['(key_names)'];
$schema['operations']['app_data_set']['additionalParameters']['_cliname'] = 'set';
$schema['operations']['app_data_set']['additionalParameters']['location'] = 'query';
unset($schema['operations']['app_data_set']['parameters']['(key_names)']);

unset($schema['operations']['app_data_get']['parameters']['keys']['type']);
$schema['operations']['app_data_get']['parameters']['keys']['filters'][] = [
    'method' => 'RavelryApi\\TypeConversion::toSpaceSeparated',
    'args' => [
        '@value',
        '@api',
    ]
];

$schema['operations']['app_data_delete']['parameters']['keys']['location'] = 'query';
unset($schema['operations']['app_data_delete']['parameters']['keys']['type']);
$schema['operations']['app_data_delete']['parameters']['keys']['filters'][] = [
    'method' => 'RavelryApi\\TypeConversion::toSpaceSeparated',
    'args' => [
        '@value',
        '@api',
    ]
];

$schema['operations']['upload_image']['parameters']['upload_token']['location'] = 'postField';
$schema['operations']['upload_image']['parameters']['access_key']['location'] = 'postField';
$schema['operations']['upload_image']['parameters']['access_key']['static'] = true;
$schema['operations']['upload_image']['parameters']['access_key']['default'] = 'anonymous';

for ($i = 0; $i < 10; $i += 1) {
    $schema['operations']['upload_image']['parameters']['file' . $i]['filters'] = [
        [
            'method' => 'RavelryApi\\TypeConversion::toRavelryPostFile',
            'args' => [
                '@value',
                '@api',
            ]
        ]
    ];
}

$json = $schema['models']['json'];
unset($schema['models']);
$schema['models'] = ['json' => $json];

ensureConsistency($schema);

fwrite(STDOUT, json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

function ensureConsistency(&$value)
{
    if (is_array($value)) {
        foreach ($value as $k => &$v) {
            ensureConsistency($v);
        }

        ksort($value);
    }
}

function canonicalType($parameterType)
{
    $parameterType = strtolower(trim($parameterType));

    if (preg_match('#^([^\s]+)\s+\(([^\)]+)\)$#', $parameterType, $parameterTypeRegex)) {
        $parameterType = str_replace(' ', '_', $parameterTypeRegex[1] . '_' . $parameterTypeRegex[2] . '_result');
    }

    return $parameterType ?: 'string';
}

function resolveType(array $schema, $parameterType, array $defaults = [])
{
    if (is_array($parameterType)) {
        $result = $parameterType;
    } elseif ($parameterType == 'multipart') {
        $result = [
            'type' => null,
            'location' => 'postFile',
        ];
    } elseif ($parameterType == 'object') {
        // @todo necessary?
        $result = [
            'type' => 'object',
            'additionalProperties' => [
                'location' => 'json'
            ],
        ];
    } elseif ($parameterType == 'datetime_post_result') {
        $result = [
            'type' => 'string',
            #'filters' => [
            #    'RavelryApi\\TypeConversion::toDateTime',
            #],
        ];
    } elseif ($parameterType == 'array_post_result') {
        $result = [
            'type' => 'array',
        ];
    } elseif ($parameterType == 'date') {
        $result = [
            'type' => 'string',
            #'filters' => [
            #    'RavelryApi\\TypeConversion::toDate',
            #],
        ];
    } elseif ($parameterType == 'boolean') {
        $result = [
            'type' => 'boolean',
        ];
    } elseif ($parameterType == 'integer') {
        $result = [
            'type' => 'integer',
            #'filters' => [
            #    'RavelryApi\\TypeConversion::toInteger',
            #],
        ];
    } elseif (preg_match('#_result$#', $parameterType)) {
        if (!isset($schema['models'][$parameterType])) {
            throw new \LogicException('Unable to find model for ' . $parameterType);
        }

        $result = [
            'type' => 'object',
            'properties' => [],
        ];

        foreach ($schema['models'][$parameterType]['properties'] as $key => $value) {
            $result['properties'][$key] = array_merge($value, resolveType($schema, $value['type']), $defaults);
        }
    } else {
        $result = [
            'type' => $parameterType,
        ];
    }

    return array_merge(
        $defaults,
        $result
    );
}

function parseParameters(array $schema, array &$schemaOperation, \DOMXPath $xpath, \DOMNodeList $parameters, array $defaults = [])
{
    for ($j = 0; $j < $parameters->length; $j += 1) {
        $parameter = $parameters->item($j);

        $parameterName = $xpath->query('.//td[contains(concat(" ", normalize-space(@class), " "), " name ")]', $parameter)->item(0)->textContent;
        $parameterType = canonicalType($xpath->query('.//td[contains(concat(" ", normalize-space(@class), " "), " type ")]', $parameter)->item(0)->textContent);
        $parameterRequired = trim($xpath->query('.//td[contains(concat(" ", normalize-space(@class), " "), " required ")]', $parameter)->item(0)->textContent);
        $parameterDescription = $xpath->query('.//td[contains(concat(" ", normalize-space(@class), " "), " description ")]', $parameter)->item(0)->textContent;

        if (!isset($schemaOperation['parameters'][$parameterName])) {
            $schemaOperation['parameters'][$parameterName] = [];
        }

        $resolved = resolveType($schema, $parameterType, $defaults);

        $detected = [
            'required' => ('Yes' == $parameterRequired) ? true : false,
            'description' => trim($parameterDescription),
        ];

        if ((!isset($defaults['location'])) && (!isset($resolved['location']))) {
            if ('GET' == $schemaOperation['httpMethod']) {
                $detected['location'] = 'query';
            } elseif ($parameterName == 'data') {
                $detected['location'] = 'json';
            } else {
                $detected['location'] = 'postField';
            }
        }

        if (preg_match('#(\sCurrently\s+accepted\s+types:\s+|\sShould\s+be\s+one\s+of:\s+|\sAccepted\s+options\sare:\s+|\s+\-\s+one\s+of:\s+|\sOptions\s+are:\s+|\sAccepted\s+options:\s+|\sOne\s+of:\s+)([^\.]+)\.?#mi', $detected['description'], $match)) {
            $detected['enum'] = preg_split('#,\s+#', preg_replace('/ and /', ' ', str_replace('"', '', trim($match[2]))));
            $detected['description'] = trim(preg_replace('#( +)#', ' ', str_replace(trim($match[0]), '', $detected['description'])));
        }

        $merged = array_merge(
            $resolved,
            $detected,
            $schemaOperation['parameters'][$parameterName]
        );

        if ('array,string' == preg_replace('/\s+/', '', $merged['type'])) {
            $merged['_clitype'] = 'array';
            // we can't be strict here since we'll use a filter
            unset($merged['type']);
            $merged['filters'][] = [
                'method' => 'RavelryApi\\TypeConversion::toSpaceSeparated',
                'args' => [
                    '@value',
                    '@api',
                ]
            ];
        }

        $schemaOperation['parameters'][$parameterName] = $merged;

        if ((1 == $parameters->length) && ('object' == $schemaOperation['parameters'][$parameterName]['type'])) {
            $old = $schemaOperation['parameters'][$parameterName];
            unset($schemaOperation['parameters'][$parameterName]);

            $schemaOperation['parameters'] = array_merge($schemaOperation['parameters'], $old['properties']);
        }
    }
}
