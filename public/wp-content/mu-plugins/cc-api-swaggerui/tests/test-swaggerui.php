<?php

use CommerceCore\CcApiSwaggerui\CcApiSwaggerUI;

class TestSwaggerUI extends WP_UnitTestCase
{
    public $ui = null;

    public function set_up()
    {
        parent::set_up();

        $this->ui = new CcApiSwaggerUI();
    }

    public function test_rewriteBaseApi()
    {
        $this->assertEquals('rest-api', CcApiSwaggerUI::rewriteBaseApi());
    }

    public function test_getHost()
    {

        $host = $this->ui->getHost();

        $this->assertStringNotContainsString('http://', $host);
        $this->assertStringNotContainsString('https://', $host);
    }

    public function test_getSchemes()
    {
        $schemes = $this->ui->getSchemes();

        $this->assertTrue(! empty($schemes));
        $this->assertTrue(is_array($schemes));

        $this->assertContains('http', $schemes);
    }

    public function test_getRawPaths()
    {
        $this->assertTrue(is_array($this->ui->getRawPaths()));
    }

    public function test_getPaths()
    {
        $this->assertTrue(is_array($this->ui->getPaths()));
    }

    public function test_convertEndpoint()
    {
        $this->assertEquals('/sample/endpoint/{sample_id}', $this->ui->convertEndpoint('/sample/endpoint/(?P<sample_id>)'));
        $this->assertEquals('/other/{other_id}/edit', $this->ui->convertEndpoint('/other/(?P<other_id>[^.\/]+(?:\/[^.\/]+)?)/edit'));
    }

    public function test_detectIn()
    {
        $this->assertEquals('path', $this->ui->detectIn('id', 'get', '/sample/{id}', null));
        $this->assertEquals('query', $this->ui->detectIn('other_id', 'get', '/sample/{id}', null));
        $this->assertEquals('formData', $this->ui->detectIn('firstname', 'post', '/sample/{id}', null));
    }

    public function test_buildParams()
    {
        $params = $this->ui->buildParams('name', 'get', '/sample/{id}', [
            'type' => 'string',
            'required' => true,
            'description' => 'Sample Description',
        ]);

        $this->assertArrayHasKey('name', $params);
        $this->assertArrayHasKey('in', $params);
        $this->assertArrayHasKey('description', $params);
        $this->assertArrayHasKey('required', $params);
        $this->assertArrayHasKey('type', $params);
    }

    public function test_buildParameters()
    {
        $this->assertTrue(is_array($this->ui->getParametersFromArgs('/sample/{id}', [], [])));
    }

    public function test_getResponses()
    {
        $responses = $this->ui->getResponses('/sample/{id}');

        $this->assertTrue(is_array($responses));

        $this->assertArrayHasKey('200', $responses);
        $this->assertArrayHasKey('400', $responses);
        $this->assertArrayHasKey('404', $responses);
    }
}
