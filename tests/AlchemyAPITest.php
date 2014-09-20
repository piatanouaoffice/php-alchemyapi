<?php

namespace AlchemyAPI\Test;

use AlchemyAPI\AlchemyAPI;

class AlchemyAPITest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->alchemy = $this->getMock('AlchemyAPI\AlchemyAPI', ['query'], ['nokey']);
        $this->response = $this->getMock('GuzzleHttp\Message\Response', [], [200]);
        $this->endpoint = 'https://access.alchemyapi.com/calls/';
    }

    public function tearDown()
    {
        parent::tearDown();
        unset(
            $this->alchemy,
            $this->response,
            $this->endpoint
        );
    }

    public function testUrlKeywords()
    {
        $url = $this->endpoint . 'url/URLGetRankedKeywords';

        $params = [
            'apikey' => 'nokey',
            'outputMode' => 'json',
            'keywordExtractMode' => 'normal',
            'maxRetrieve' => 50,
            'sentiment' => false,
            'showSourceText' => false,
            'url' => 'http://example.com'
        ];

        $this->alchemy->expects($this->once())
            ->method('query')
            ->with($url, $params)
            ->will($this->returnValue($this->response));

        $this->alchemy->keywords('url', 'http://example.com');
    }

    public function testHtmlKeywords()
    {
        $url = $this->endpoint . 'html/HTMLGetRankedKeywords';

        $params = [
            'apikey' => 'nokey',
            'outputMode' => 'json',
            'keywordExtractMode' => 'normal',
            'maxRetrieve' => 50,
            'sentiment' => false,
            'showSourceText' => false,
            'html' => '<html></html>'
        ];

        $this->alchemy->expects($this->once())
            ->method('query')
            ->with($url, $params)
            ->will($this->returnValue($this->response));

        $this->alchemy->keywords('html', '<html></html>');
    }

    public function testTextKeywords()
    {
        $url = $this->endpoint . 'text/TextGetRankedKeywords';

        $params = [
            'apikey' => 'nokey',
            'outputMode' => 'json',
            'keywordExtractMode' => 'normal',
            'maxRetrieve' => 50,
            'sentiment' => false,
            'showSourceText' => false,
            'text' => 'lorem ipsum'
        ];

        $this->alchemy->expects($this->once())
            ->method('query')
            ->with($url, $params)
            ->will($this->returnValue($this->response));

        $this->alchemy->keywords('text', 'lorem ipsum');
    }

    public function testImageImageKeywords()
    {
        $url = $this->endpoint . 'image/ImageGetRankedImageKeywords';
        $url .= '?' . http_build_query([
            'apikey' => 'nokey',
            'outputMode' => 'json',
            'extractMode' => 'trust-metadata'
        ]);

        $params = 'test.png';

        $this->alchemy->expects($this->once())
            ->method('query')
            ->with($url, $params)
            ->will($this->returnValue($this->response));

        $this->alchemy->image_keywords('image', 'test.png');
    }

}
