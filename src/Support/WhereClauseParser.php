<?php

namespace InnoBrain\OnofficeCli\Support;

use InnoBrain\OnofficeCli\Exceptions\ValidationException;

class WhereClauseParser
{
    /**
     * Regex patterns for operators, ordered by specificity (longer/more specific first).
     * Word boundary patterns ensure "not like" matches before "like".
     */
    protected const OPERATOR_PATTERNS = [
        '/\s+not\s+like\s+/i' => 'not like',
        '/\s+like\s+/i' => 'like',
        '/!=/' => '!=',
        '/>=/' => '>=',
        '/<=/' => '<=',
        '/>/' => '>',
        '/</' => '<',
        '/=/' => '=',
    ];

    /**
     * Parse a where clause string into field, operator, and value.
     *
     * @return array{field: string, operator: string, value: mixed}
     */
    public static function parse(string $clause): array
    {
        $clause = trim($clause);

        foreach (self::OPERATOR_PATTERNS as $pattern => $operator) {
            if (preg_match($pattern, $clause, $matches, PREG_OFFSET_CAPTURE)) {
                $matchedString = $matches[0][0];
                $pos = $matches[0][1];

                $field = trim(substr($clause, 0, $pos));
                $value = trim(substr($clause, $pos + strlen($matchedString)));

                if ($field === '') {
                    throw new ValidationException(
                        "Invalid where clause '{$clause}': field name is required"
                    );
                }

                if ($value === '') {
                    throw new ValidationException(
                        "Invalid where clause '{$clause}': value is required"
                    );
                }

                return [
                    'field' => $field,
                    'operator' => $operator,
                    'value' => self::castValue($value),
                ];
            }
        }

        throw new ValidationException(
            "Invalid where clause '{$clause}': no valid operator found. ".
            'Supported operators: =, !=, <, >, <=, >=, like, not like'
        );
    }

    /**
     * Parse multiple where clauses.
     *
     * @param  array<string>  $clauses
     * @return array<array{field: string, operator: string, value: mixed}>
     */
    public static function parseMany(array $clauses): array
    {
        return array_map(fn (string $clause): array => self::parse($clause), $clauses);
    }

    /**
     * Cast string value to appropriate PHP type.
     */
    protected static function castValue(string $value): mixed
    {
        $lowered = strtolower($value);

        if ($lowered === 'true') {
            return true;
        }

        if ($lowered === 'false') {
            return false;
        }

        if ($lowered === 'null') {
            return null;
        }

        if (! is_numeric($value)) {
            return $value;
        }

        return str_contains($value, '.') ? (float) $value : (int) $value;
    }
}
