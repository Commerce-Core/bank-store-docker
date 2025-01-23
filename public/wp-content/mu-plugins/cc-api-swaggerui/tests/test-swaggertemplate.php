<?php

use CommerceCore\CcApiSwaggerui\SwaggerTemplate;

class TestSwaggerTemplate extends WP_UnitTestCase
{
    public $template = null;

    public function set_up()
    {
        parent::set_up();

        $this->template = new SwaggerTemplate();
    }

    public function test_instance()
    {
        $this->assertTrue($this->template instanceof SwaggerTemplate);
    }

    public function test_info()
    {
        $info = $this->template->getAssetInfo('assets/js/app');
        $this->assertTrue(is_array($info));

        $this->assertArrayHasKey('dependencies', $info);
        $this->assertArrayHasKey('version', $info);
    }
}
