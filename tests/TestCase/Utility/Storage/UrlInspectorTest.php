<?php

namespace App\Test\TestCase\Utility\Storage;

use App\Utility\Storage\UrlInspector;
use PHPUnit\Framework\TestCase;

class UrlInspectorTest extends TestCase
{
    public function testHttp200Ok()
    {
        $inspector = new UrlInspector();
        $result = $inspector->inspectUrlConnection([
            'http_host' => 'https://httpbin.org/status/200',
            'http_method' => 'GET',
            'http_timeout' => 5
        ]);
        $report = $inspector->getInspectionReport();

        $this->assertTrue($result);
        $this->assertEquals(200, $report['http_code']);
        $this->assertTrue($report['connection']);
    }

    public function testHttp404NotFound()
    {
        $inspector = new UrlInspector();
        $result = $inspector->inspectUrlConnection([
            'http_host' => 'https://httpbin.org/status/404',
            'http_method' => 'GET',
            'http_timeout' => 5
        ]);
        $report = $inspector->getInspectionReport();

        $this->assertTrue($result);
        $this->assertEquals(404, $report['http_code']);
        $this->assertTrue($report['connection']);
    }

    public function testHttp500ServerError()
    {
        $inspector = new UrlInspector();
        $result = $inspector->inspectUrlConnection([
            'http_host' => 'https://httpbin.org/status/500',
            'http_method' => 'GET',
            'http_timeout' => 5
        ]);
        $report = $inspector->getInspectionReport();

        $this->assertTrue($result);
        $this->assertEquals(500, $report['http_code']);
        $this->assertTrue($report['connection']);
    }

    public function testHttps200Ok()
    {
        $inspector = new UrlInspector();
        $result = $inspector->inspectUrlConnection([
            'http_host' => 'https://httpbin.org/status/200',
            'http_method' => 'GET',
            'http_timeout' => 5
        ]);
        $report = $inspector->getInspectionReport();

        $this->assertTrue($result);
        $this->assertEquals(200, $report['http_code']);
        $this->assertTrue($report['connection']);
    }
}
