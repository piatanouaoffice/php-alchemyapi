<?php

namespace AlchemyAPI;

class AlchemyAPI
{

    const URI = 'gateway-a.watsonplatform.net/calls';

    public $client;

    public $endpoint;

    public $params;

    protected $key = '';

    protected $ssl = false;

    protected $defaults = [
        'category' => ['showSourceText' => false],
        'combined' => [
            'baseUrl' => null,
            'coreference' => true,
            'disambiguate' => true,
            'extract' => 'entity, keyword, taxonomy, concept',
            'linkedData' => true,
            'maxRetrieve' => 50,
            'quotations' => false,
            'sentiment' => false,
            'showSourceText' => false,
        ],
        'concepts' => [
            'linkedData' => true,
            'maxRetrieve' => 8,
            'showSourceText' => false,
        ],
        'entities' => [
            'coreference' => true,
            'disambiguate' => false,
            'linkedData' => true,
            'maxRetrieve' => 50,
            'quotations' => false,
            'sentiment' => false,
            'showSourceText' => false,
        ],
        'image_keywords' => [
            'extractMode' => 'trust-metadata',
            'imagePostMode' => null,
        ],
        'keywords' => [
            'keywordExtractMode' => 'normal',
            'maxRetrieve' => 50,
            'sentiment' => false,
            'showSourceText' => false,
        ],
        'relations' => [
            'coreference' => true,
            'disambiguate' => true,
            'entities' => false,
            'keywords' => false,
            'linkedData' => true,
            'maxRetrieve' => 50,
            'requireEntities' => false,
            'sentiment' => false,
            'sentimentExcludeEntities' => true,
            'showSourceText' => false,
        ],
        'sentiment' => ['showSourceText' => false],
        'sentiment_targeted' => ['showSourceText' => false],
        'taxonomy' => [
            'baseUrl' => null,
            'cquery' => null,
            'showSourceText' => false,
            'sourceText' => 'cleaned_or_raw',
            'xpath' => null,
        ],
        'text' => [
            'extractLinks' => false,
            'useMetaData' => true,
        ],
        'title' => ['useMetaData' => true],
    ];

    private $services = [
        'author',
        'category',
        'combined',
        'concepts',
        'entities',
        'feeds',
        'image',
        'image_keywords',
        'keywords',
        'language',
        'microformats',
        'relations',
        'sentiment',
        'sentiment_targeted',
        'taxonomy',
        'text',
        'text_raw',
        'title'
    ];

    private $mapServices = [
        'combined' => 'CombinedData',
        'concepts' => 'RankedConcepts',
        'entities' => 'RankedNamedEntities',
        'feeds' => 'FeedLinks',
        'image_keywords' => 'RankedImageKeywords',
        'keywords' => 'RankedKeywords',
        'microformats' => 'MicroformatData',
        'sentiment' => 'TextSentiment',
        'sentiment_targeted' => 'TargetedSentiment',
        'taxonomy' => 'RankedTaxonomy',
        'text_raw' => 'RawText',
    ];

    private $acceptedFlavors = [
        'author' => ['url', 'html'],
        'combined' => ['url', 'text'],
        'feeds' => ['url', 'html'],
        'image' => ['url'],
        'image_keywords' => ['url', 'image'],
        'microformats' => ['url', 'html'],
        'text' => ['url', 'html'],
        'text_raw' => ['url', 'html'],
        'title' => ['url', 'html'],
    ];

    public function __construct($key, $ssl = true)
        {
            $this->key = $key;
            $this->ssl = $ssl;
        }

    public function __call($method, $args)
        {
            if (count($args) < 3)
                {
                    $args[] = [];
                }

            list($flavor, $data, $params) = $args;

            if (!in_array($method, $this->services))
                {
                    throw new \Exception(sprintf('Invalid service (%s)', $method));
                }

            if (!$this->accepts($method, $flavor))
                {
                    throw new \Exception(sprintf('Invalid flavor (%s) for service (%s)', $flavor, $method));
                }

            $endpoint = $this->getServiceEndpoint($method, $flavor);
            $params = $params + ['apikey' => $this->key, 'outputMode' => 'json'];

            if (!empty($this->defaults[$method]))
                {
                    $params += $this->defaults[$method];
                }

            if ('image' != $flavor)
                {
                    $params[$flavor] = $data;
                }
            else
                {
                    $endpoint .= '?' . http_build_query($params);
                    $params = $data;
                }

            $this->rawResponse = $this->query($endpoint, $params);
            $response = $this->rawResponse;

            if ('ERROR' == $response['status'])
                {
                    throw new \Exception($response['statusInfo']);
                }

            return $response;
        }

    public function accepts($service, $flavor)
        {
            if (empty($this->acceptedFlavors[$service]))
                {
                    $this->acceptedFlavors[$service] = ['url', 'text', 'html'];
                }

            return in_array($flavor, $this->acceptedFlavors[$service]);
        }

    public function disableSsl()
        {
            $this->ssl = false;
            return $this;
        }

    public function enableSsl()
        {
            $this->ssl = true;
            return $this;
        }

    public function getServiceEndpoint($service, $flavor)
        {
            if (!empty($this->mapServices[$service]))
                {
                    $service = $this->mapServices[$service];
                }

            if (in_array($flavor, ['html', 'url']))
                {
                    $flavor = strtoupper($flavor);
                }

            return sprintf(
                'http%s://%s/%s/%sGet%s',
                $this->ssl ? 's' : null,
                self::URI,
                strtolower($flavor),
                ucfirst($flavor),
                $service
            );
        }

    protected function query($endpoint, $body)
        {
            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($body),
                ],
            ];
            $context = stream_context_create($options);
            $result = json_decode(file_get_contents($endpoint, false, $context), true);

            return $result;
        }

}
