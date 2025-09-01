<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Middleware\ModifyRequestDataMiddleware;
use Cake\TestSuite\TestCase;

/**
 * App\Middleware\ModifyRequestDataMiddleware Test Case
 */
class ModifyRequestDataMiddlewareTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Middleware\ModifyRequestDataMiddleware
     */
    protected $ModifyRequestData;

    /**
     * Test process method
     *
     * @return void
     * @uses \App\Middleware\ModifyRequestDataMiddleware::process()
     */
    public function testProcess(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
