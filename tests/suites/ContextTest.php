<?php

declare(strict_types=1);

namespace Phrity\Net\Test;

use Closure;
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
        /* @phpstan-ignore method.alreadyNarrowedType */
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
        /* @phpstan-ignore method.alreadyNarrowedType */
        $this->assertIsArray($params);
        $this->assertEquals([], $params['options']);
        $context->setParams([
            'options' => [
                'test-wrapper-1' => [
                    'test-option-1-1' => 'test-option-1-1-a',
                    'test-option-1-2' => 'test-option-1-2-a',
                ],
            ],
            'notification' => 'trim',
        ]);
        $context->setParams([
            'options' => [
                'test-wrapper-1' => [
                    'test-option-1-1' => 'test-option-1-1-b',
                    'test-option-1-3' => 'test-option-1-3-a',
                ],
            ],
            'notification' => 'ltrim',
        ]);
        $context->setParam('notification', 'rtrim');
        $context->setParam('options', [
            'test-wrapper-1' => [
                'test-option-1-4' => 'test-option-1-4-a',
            ],
        ]);
        $params = $context->getParams();
        $this->assertEquals([
            'notification' => 'rtrim',
            'options' => [
                'test-wrapper-1' => [
                    'test-option-1-1' => 'test-option-1-1-b',
                    'test-option-1-2' => 'test-option-1-2-a',
                    'test-option-1-3' => 'test-option-1-3-a',
                    'test-option-1-4' => 'test-option-1-4-a',
                ],
            ],
        ], $params);
        $this->assertEquals('rtrim', $context->getParam('notification'));
    }

    public function testNotifiers(): void
    {
        $results = [];

        $context = new Context();
        $context->onResolve(function () use (&$results) {
            $results[] = 'onResolve';
        });
        $context->onConnect(function () use (&$results) {
            $results[] = 'onConnect';
        });
        $context->onAuthRequired(function () use (&$results) {
            $results[] = 'onAuthRequired';
        });
        $context->onMimeType(function (string $mimeType) use (&$results) {
            $results[] = 'onMimeType';
        });
        $context->onFileSize(function (int $fileSize) use (&$results) {
            $results[] = 'onFileSize';
        });
        $context->onRedirected(function (string $uri) use (&$results) {
            $results[] = 'onRedirected';
        });
        $context->onProgress(function (int $transferred, int $max) use (&$results) {
            $results[] = 'onProgress';
        });
        $context->onCompleted(function () use (&$results) {
            $results[] = 'onCompleted';
        });
        $context->onFailure(function (string $message, int $code) use (&$results) {
            $results[] = 'onFailure';
        });
        $context->onAuthResult(function () use (&$results) {
            $results[] = 'onAuthResult';
        });

        $this->assertInstanceOf(Closure::class, $context->getParam('notification'));

        $callback = $context->getParam('notification');
        $callback(STREAM_NOTIFY_RESOLVE, STREAM_NOTIFY_SEVERITY_INFO, null, 0, 0, 0);
        $callback(STREAM_NOTIFY_CONNECT, STREAM_NOTIFY_SEVERITY_INFO, null, 0, 0, 0);
        $callback(STREAM_NOTIFY_AUTH_REQUIRED, STREAM_NOTIFY_SEVERITY_ERR, null, 0, 0, 0);
        $callback(STREAM_NOTIFY_MIME_TYPE_IS, STREAM_NOTIFY_SEVERITY_INFO, 'text/html', 0, 0, 0);
        $callback(STREAM_NOTIFY_FILE_SIZE_IS, STREAM_NOTIFY_SEVERITY_INFO, 1024, 0, 0, 0);
        $callback(STREAM_NOTIFY_REDIRECTED, STREAM_NOTIFY_SEVERITY_INFO, 'somewhere', 0, 0, 0);
        $callback(STREAM_NOTIFY_PROGRESS, STREAM_NOTIFY_SEVERITY_INFO, null, 0, 256, 1024);
        $callback(STREAM_NOTIFY_COMPLETED, STREAM_NOTIFY_SEVERITY_INFO, null, 0, 0, 0);
        $callback(STREAM_NOTIFY_FAILURE, STREAM_NOTIFY_SEVERITY_INFO, 'error', 12, 0, 0);
        $callback(STREAM_NOTIFY_AUTH_RESULT, STREAM_NOTIFY_SEVERITY_INFO, null, 0, 0, 0);
        $this->assertEquals([
            'onResolve',
            'onConnect',
            'onAuthRequired',
            'onMimeType',
            'onFileSize',
            'onRedirected',
            'onProgress',
            'onCompleted',
            'onFailure',
            'onAuthResult',
        ], $results);
    }

    public function testCreateWithStream(): void
    {
        /** @var resource $file */
        $file = fopen(__DIR__ . '/../fixtures/stream-readonly.txt', 'r');
        $context = new Context($file);
        $this->assertEquals($file, $context->getResource());
    }

    public function testCreateInvalidTypeError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid stream provided; got type 'string'.");
        /* @phpstan-ignore argument.type */
        $context = new Context("hello");
    }

    public function testCreateClosedResourceError(): void
    {
        /** @var resource $file */
        $file = fopen(__DIR__ . '/../fixtures/stream-readonly.txt', 'r');
        fclose($file);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid stream provided; got type 'resource (closed)'.");
        $context = new Context($file);
    }

    public function testCreateInvalidResourceError(): void
    {
        /** @var resource $curl */
        $curl = proc_open('php', [], $pipes);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid stream provided; got resource type 'process'.");
        $context = new Context($curl);
    }

    public function testSetOptionError(): void
    {
        /** @var resource $file */
        $file = fopen(__DIR__ . '/../fixtures/stream-readonly.txt', 'r');
        $context = new Context($file);
        fclose($file);
        $this->expectException(StreamException::class);
        $this->expectExceptionCode(StreamException::CONTEXT_SET_ERR);
        $this->expectExceptionMessage("Failed to set option/param on context.");
        $context->setOption('a', 'b', 'c');
    }

    public function testSetOptionParamError(): void
    {
        /** @var resource $file */
        $file = fopen(__DIR__ . '/../fixtures/stream-readonly.txt', 'r');
        $context = new Context($file);
        fclose($file);
        $this->expectException(StreamException::class);
        $this->expectExceptionCode(StreamException::CONTEXT_SET_ERR);
        $this->expectExceptionMessage("Failed to set option/param on context.");
        $context->setParam('a', 'b');
    }
}
