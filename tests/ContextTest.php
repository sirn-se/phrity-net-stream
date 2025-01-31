<?php

declare(strict_types=1);

namespace Phrity\Net\Test;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Phrity\Net\{
    Context,
    StreamException,
};

class ContextTest extends TestCase
{
    public function testContextOptions(): void
    {
        $context = new Context();
        $options = $context->getOptions();
        $this->assertIsArray($options);
        $this->assertEmpty($options);
        $context->setOptions([
            'test-wrapper-1' => [
                'test-option-1-1' => 'test-option-1-1-a',
                'test-option-1-2' => 'test-option-1-2-a',
            ],
            'test-wrapper-2' => [
                'test-option-2-1' => 'test-option-2-1-a',
                'test-option-2-2' => 'test-option-2-2-a',
            ],
        ]);
        $context->setOptions([
            'test-wrapper-1' => [
                'test-option-1-1' => 'test-option-1-1-b',
            ],
            'test-wrapper-2' => [
                'test-option-2-3' => 'test-option-2-3-a',
            ],
        ]);
        $context->setOption('test-wrapper-2', 'test-option-2-1', 'test-option-2-1-b');
        $context->setOption('test-wrapper-3', 'test-option-3-1', 'test-option-3-1-a');
        $options = $context->getOptions();
        $this->assertEquals([
            'test-wrapper-1' => [
                'test-option-1-1' => 'test-option-1-1-b',
                'test-option-1-2' => 'test-option-1-2-a',
            ],
            'test-wrapper-2' => [
                'test-option-2-1' => 'test-option-2-1-b',
                'test-option-2-2' => 'test-option-2-2-a',
                'test-option-2-3' => 'test-option-2-3-a',
            ],
            'test-wrapper-3' => [
                'test-option-3-1' => 'test-option-3-1-a',
            ],
        ], $options);
        $this->assertEquals(
            'test-option-1-1-b',
            $context->getOption('test-wrapper-1', 'test-option-1-1')
        );
    }

    public function testContextParams(): void
    {
        $context = new Context();
        $params = $context->getParams();
        $this->assertIsArray($params);
        $this->assertEquals(['options' => []], $params);
        $context->setParams([
            'options' => [
                'test-wrapper-1' => [
                    'test-option-1-1' => 'test-option-1-1-a',
                    'test-option-1-2' => 'test-option-1-2-a',
                ],
            ],
            'notification' => 'test-notification-a',
        ]);
        $context->setParams([
            'options' => [
                'test-wrapper-1' => [
                    'test-option-1-1' => 'test-option-1-1-b',
                    'test-option-1-3' => 'test-option-1-3-a',
                ],
            ],
            'notification' => 'test-notification-b',
        ]);
        $context->setParam('notification', 'test-notification-c');
        $context->setParam('options', [
            'test-wrapper-1' => [
                'test-option-1-4' => 'test-option-1-4-a',
            ],
        ]);
        $params = $context->getParams();
        $this->assertEquals([
            'notification' => 'test-notification-c',
            'options' => [
                'test-wrapper-1' => [
                    'test-option-1-1' => 'test-option-1-1-b',
                    'test-option-1-2' => 'test-option-1-2-a',
                    'test-option-1-3' => 'test-option-1-3-a',
                    'test-option-1-4' => 'test-option-1-4-a',
                ],
            ],
        ], $params);
        $this->assertEquals('test-notification-c', $context->getParam('notification'));
    }

    public function testCreateWithStream(): void
    {
        $file = fopen(__DIR__ . '/fixtures/stream-readonly.txt', 'r');
        $context = new Context($file);
        $this->assertEquals($file, $context->getResource());
    }

    public function testCreateInvalidTypeError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid stream provided; got type 'string'.");
        $context = new Context("hello");
    }

    public function testCreateClosedResourceError(): void
    {
        $file = fopen(__DIR__ . '/fixtures/stream-readonly.txt', 'r');
        fclose($file);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid stream provided; got type 'resource (closed)'.");
        $context = new Context($file);
    }

    public function testCreateInvalidResourceError(): void
    {
        $curl = proc_open('php', [], $pipes);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid stream provided; got resource type 'process'.");
        $context = new Context($curl);
    }

    public function testSetOptionError(): void
    {
        $file = fopen(__DIR__ . '/fixtures/stream-readonly.txt', 'r');
        $context = new Context($file);
        fclose($file);
        $this->expectException(StreamException::class);
        $this->expectExceptionMessage("Failed to set option/param on context.");
        $context->setOption('a', 'b', 'c');
    }

    public function testSetOptionParamError(): void
    {
        $file = fopen(__DIR__ . '/fixtures/stream-readonly.txt', 'r');
        $context = new Context($file);
        fclose($file);
        $this->expectException(StreamException::class);
        $this->expectExceptionMessage("Failed to set option/param on context.");
        $context->setParam('a', 'b');
    }
}
