<?php

namespace Tests\SM\EngineBundle\Utility;

use Beesofts\PrettyDumper\ExceptionNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExceptionNormalizer::class)]
class ExceptionNormalizerTest extends TestCase
{
    /**
     * @dataProvider normalizeExceptionCasesProvider
     *
     * @param array<string, string> $expected
     */
    public function testNormalizeException(array $expected, \Throwable $exception, bool $debug): void
    {
        self::assertEquals($expected, ExceptionNormalizer::normalizeException($exception, $debug));
    }

    public static function normalizeExceptionCasesProvider(): iterable
    {
        yield 'simple_exception_without_debug' => [
            [
                'message' => 'test',
                'code' => 123,
            ],
            new \Exception('test', 123),
            false,
        ];
        yield 'simple_exception_with_debug' => [
            [
                'message' => 'test',
                'code' => 123,
                'file' => __FILE__,
                'line' => 40,
                'class' => \Exception::class,
            ],
            new \Exception('test', 123),
            true,
        ];
        yield 'exception_stack_without_debug' => [
            [
                'message' => 'test',
                'code' => 123,
            ],
            new \Exception('test', 123, previous: new \Exception('another', 1337)),
            false,
        ];
        yield 'exception_stack_with_debug' => [
            [
                'message' => 'test',
                'code' => 123,
                'file' => __FILE__,
                'line' => 66,
                'class' => \Exception::class,
                'previous' => [
                    'message' => 'another',
                    'code' => 1337,
                    'file' => __FILE__,
                    'line' => 66,
                    'class' => \Exception::class,
                ],
            ],
            new \Exception('test', 123, previous: new \Exception('another', 1337)),
            true,
        ];
    }
}
