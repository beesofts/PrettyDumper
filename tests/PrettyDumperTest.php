<?php

use Beesofts\PrettyDumper\PrettyDumper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PrettyDumper::class)]
class PrettyDumperTest extends TestCase
{
    public static function prettyDumpWithDefaultPresetCasesProvider(): iterable
    {
        yield 'null' => [
            'NULL',
            null,
        ];

        yield 'bool_true' => [
            'TRUE',
            true,
        ];

        yield 'bool_false' => [
            'FALSE',
            false,
        ];

        yield 'int' => [
            '1',
            1,
        ];

        yield 'string' => [
            '"foo"',
            'foo',
        ];

        yield 'empty_string' => [
            '""',
            '',
        ];

        yield 'simple_array' => [
            <<<EXPECTED
[
    string => "John",
    int => 33,
    bool => TRUE,
    null => NULL
]
EXPECTED,
            ['string' => 'John', 'int' => 33, 'bool' => true, 'null' => null],
        ];

        yield 'array_in_array' => [
            <<<EXPECTED
[
    user => [
        name => "John",
        age => 33
    ],
    company => [
        name => "Big Society",
        size => 51
    ]
]
EXPECTED,
            ['user' => ['name' => 'John', 'age' => 33], 'company' => ['name' => 'Big Society', 'size' => 51]],
        ];

        yield 'simple_object' => [
            <<<EXPECTED
{
    string : "John",
    int : 33,
    bool : TRUE,
    null : NULL
}
EXPECTED,
            (object) ['string' => 'John', 'int' => 33, 'bool' => true, 'null' => null],
        ];

        yield 'object_in_object' => [
            <<<EXPECTED
{
    user : {
        name : "John",
        age : 33
    },
    company : {
        name : "Big Society",
        size : 51
    }
}
EXPECTED,
            (object) ['user' => (object) ['name' => 'John', 'age' => 33], 'company' => (object) ['name' => 'Big Society', 'size' => 51]],
        ];

        $objectImplementingIteratorAggregate = new class() implements IteratorAggregate, Countable {
            /** @var array<string, string|int> */
            private array $values = [
                'name' => 'John',
                'age' => 33,
            ];

            public function count(): int
            {
                return count($this->values);
            }

            public function getIterator(): Traversable
            {
                return new ArrayIterator($this->values);
            }
        };
        yield 'object_implementing_IteratorAggregate' => [
            <<<EXPECTED
{
    name : "John",
    age : 33
}
EXPECTED,
            $objectImplementingIteratorAggregate,
        ];

        $objectNotImplementingIteratorAggregate = new class() {
            /** @var array<string, string|int> */
            private array $values = [
                'name' => 'John',
                'age' => 33,
            ];

            public function count(): int
            {
                return count($this->values);
            }

            /** @return \ArrayIterator<string, string|int> */
            public function getIterator(): Traversable
            {
                return new ArrayIterator($this->values);
            }
        };
        yield 'object_not_implementing_IteratorAggregate' => [
            <<<EXPECTED
{
}
EXPECTED,
            $objectNotImplementingIteratorAggregate,
        ];

        yield 'object_from_spl' => [
            <<<EXPECTED
{
}
EXPECTED,
            new \Exception('foo'),
        ];
    }

    /**
     * @dataProvider prettyDumpWithDefaultPresetCasesProvider
     */
    public function testPrettyDumpWithDefaultPreset(string $expected, mixed $data): void
    {
        self::assertEquals($expected, PrettyDumper::prettyDump($data, true));
    }

    public function testPrettyDumpWithInlinePreset(): void
    {
        $expected = '[ name => "John", age => 33 ]';
        $data = [
            'name' => 'John',
            'age' => 33,
        ];

        self::assertEquals($expected, PrettyDumper::prettyDump($data, true, PrettyDumper::PRESET_INLINE));
    }

    public function testPrettyDumpWithCustomConfig(): void
    {
        $expected = '[name => #John#,age => 33]';
        $data = [
            'name' => 'John',
            'age' => 33,
        ];
        $configuration = [
            'newLineChar' => '',
            'stringDelimiter' => '#',
            'tabulationChar' => '',
        ];

        self::assertEquals($expected, PrettyDumper::prettyDump($data, true, $configuration));
    }
}
