<?php

namespace Beesofts\PrettyDumper;

/**
 * @phpstan-type ConfigArray array{newLineChar?: string, stringDelimiter?: string, tabulationChar?: string, maxIterations?: int|null, tabCount?: int}
 */
class PrettyDumper
{
    public const PRESET_DEFAULT = [
        'newLineChar' => "\n",
        'stringDelimiter' => '"',
        'tabulationChar' => '    ',
        'maxIterations' => null,
        'tabCount' => 0,
    ];

    public const PRESET_INLINE = [
        'newLineChar' => ' ',
        'stringDelimiter' => '"',
        'tabulationChar' => '',
        'maxIterations' => null,
        'tabCount' => 0,
    ];

    /**
     * Prettier dump than print_r. For object's protected/private fields, consider implementing \IteratorAggregate & \Countable.
     *
     * @param mixed $data array, object, or any scalar
     * @param bool $return if true result is returned, else result is printed
     * @param ConfigArray $configuration
     *
     * @return string|1
     *
     * @throws \Exception
     */
    public static function prettyDump(
        mixed $data,
        bool $return = false,
        array $configuration = self::PRESET_DEFAULT
    ) {
        $optionsFull = array_merge(self::PRESET_DEFAULT, $configuration);
        $optionsRecursive = array_merge(
            $optionsFull,
            [
                'maxIterations' => null === $optionsFull['maxIterations'] ? null : $optionsFull['maxIterations'] - 1,
                'tabCount' => $optionsFull['tabCount'] + 1,
            ]
        );

        $newLineChar = $optionsFull['newLineChar'];
        $stringDelimiter = $optionsFull['stringDelimiter'];
        $tabulationChar = $optionsFull['tabulationChar'];
        $tabCount = $optionsFull['tabCount'];
        $maxIterations = $optionsFull['maxIterations'];

        $result = '';
        if (is_array($data) || is_object($data)) {
            if ((null !== $maxIterations) && ($maxIterations <= 0)) {
                return '-=MAX_ITERATIONS_REACHED=-';
            }

            if (is_array($data)) {
                $openingBlockCharacter = '[';
                $closingBlockCharacter = ']';
                $fieldValueSeparator = '=>';
                $itemsCount = count($data);
            } else {
                $openingBlockCharacter = '{';
                $closingBlockCharacter = '}';
                $fieldValueSeparator = ':';
                $itemsCount = $data instanceof \Countable ? count($data) : count((array) $data);
            }
            $tabs = str_repeat($tabulationChar, $tabCount + 1);
            $tabsEnd = str_repeat($tabulationChar, $tabCount);
            $result .= $openingBlockCharacter . $newLineChar;
            $counter = 0;
            foreach ($data as $key => $value) {
                $isLast = ($counter == ($itemsCount - 1));
                $result .=
                    $tabs .
                    sprintf('%s %s %s', $key, $fieldValueSeparator, self::prettyDump($value, true, $optionsRecursive)) .
                    ($isLast ? '' : ',') .
                    $newLineChar
                ;
                ++$counter;
            }
            $result .= $tabsEnd . $closingBlockCharacter;
        } else {
            if (is_bool($data)) {
                $result .= $data ? 'TRUE' : 'FALSE';
            } elseif (is_null($data)) {
                $result .= 'NULL';
            } elseif (is_string($data)) {
                $result .= sprintf('%s%s%s', $stringDelimiter, $data, $stringDelimiter);
            } else {
                $result .= $data;
            }
        }

        if ($return) {
            return $result;
        }

        return print $result;
    }
}
