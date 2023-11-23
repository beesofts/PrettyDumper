<?php

namespace Beesofts\PrettyDumper;

/**
 * @phpstan-type NormalizedException array{
 *     message: string,
 *     code: int,
 *     file?: string,
 *     line?: int,
 *     class?: class-string<\Throwable>,
 *     previous?: NormalizedException,
 * }
 */
class ExceptionNormalizer
{
    /**
     * @return NormalizedException
     */
    public static function normalizeException(\Throwable $exception, bool $debug): array
    {
        $output = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];

        if ($debug) {
            $output += [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'class' => get_class($exception),
            ];
            if (!is_null($exception->getPrevious())) {
                $output['previous'] = self::normalizeException($exception->getPrevious(), $debug);
            }
        }

        return $output;
    }
}
