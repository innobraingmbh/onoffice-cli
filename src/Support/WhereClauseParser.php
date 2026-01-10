<?php

namespace InnoBrain\OnofficeCli\Support;

use InvalidArgumentException;

class WhereClauseParser
{
    /**
     * Supported operators in order of precedence (longer operators first).
     */
    protected const OPERATORS = ['!=', '>=', '<=', '>', '<', '=', ' not like ', ' like '];

    /**
     * Parse a where clause string into field, operator, and value.
     *
     * @return array{field: string, operator: string, value: string}
     */
    public static function parse(string $clause): array
    {
        $clause = trim($clause);

        foreach (self::OPERATORS as $operator) {
            $normalizedOperator = trim($operator);
            $pos = stripos($clause, $operator);

            if ($pos !== false) {
                $field = trim(substr($clause, 0, $pos));
                $value = trim(substr($clause, $pos + strlen($operator)));

                if ($field === '' || $value === '') {
                    throw new InvalidArgumentException(
                        "Invalid where clause '{$clause}': field and value are required"
                    );
                }

                return [
                    'field' => $field,
                    'operator' => $normalizedOperator,
                    'value' => self::castValue($value),
                ];
            }
        }

        throw new InvalidArgumentException(
            "Invalid where clause '{$clause}': no valid operator found. ".
            'Supported operators: =, !=, <, >, <=, >=, like, not like'
        );
    }

    /**
     * Parse multiple where clauses.
     *
     * @param  array<string>  $clauses
     * @return array<array{field: string, operator: string, value: string}>
     */
    public static function parseMany(array $clauses): array
    {
        return array_map(fn (string $clause) => self::parse($clause), $clauses);
    }

    protected static function castValue(string $value): mixed
    {
        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => is_numeric($value)
                ? (str_contains($value, '.') ? (float) $value : (int) $value)
                : $value,
        };
    }
}
