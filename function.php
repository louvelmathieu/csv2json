<?php

class NoFileException extends Exception
{
}

class InvalidOptionException extends Exception
{
}

function parse_arg(array $argv): array
{
    unset($argv[0]); // Php file name
    $argv = array_values($argv);

    $pretty = false;
    $fields = null;
    $aggregate = null;
    $desc = null;

    if ($key = array_search('--fields', $argv) !== false) {
        $fields = explode(',', $argv[$key + 1]);
        unset($argv[$key], $argv[$key + 1]);
        $argv = array_values($argv);
    }

    if ($key = array_search('--aggregate', $argv) !== false) {
        $aggregate = $argv[$key + 1];
        unset($argv[$key], $argv[$key + 1]);
        $argv = array_values($argv);
    }

    if ($key = array_search('--desc', $argv) !== false) {
        $desc = $argv[$key + 1];
        unset($argv[$key], $argv[$key + 1]);
        $argv = array_values($argv);
    }

    if ($key = array_search('--pretty', $argv) !== false) {
        $pretty = true;
        unset($argv[$key]);
        $argv = array_values($argv);
    }

    if (count($argv) === 0) {
        throw new NoFileException();
    } else if (count($argv) > 1) {
        throw new InvalidOptionException($argv[1]);
    }

    $file = $argv[0];

    return [$file, $fields, $aggregate, $desc, $pretty];
}

/**
 * Find the CSV delimieters from an extract of the files
 *
 * @param array $lines
 *
 * @return string
 */
function guess_delimiter(array $lines): string
{
    // List all available delimiters
    $delimiters = [
        '-' => 0,
        ',' => 0,
        ':' => 0,
        ';' => 0,
        '|' => 0,
        '.' => 0,
        "\t" => 0,
    ];

    // Test all delimiters
    foreach ($delimiters as $delimiter => $val) {
        $count = 0;
        if (strpos($lines[0] ?? '', $delimiter) !== false) {
            // On each line, check the number of fields with the delimiters
            foreach ($lines as $i => $line) {
                if ($i === 0) {
                    $count = count(str_getcsv($line, $delimiter));
                } else if ($count !== count(str_getcsv($line, $delimiter))) {
                    // If lines dont have the same fields, the delimiters is not good
                    continue 2;
                }
            }

            // All lines have the same number of fiels with this delimiters
            $delimiters[$delimiter] = $count;
        }
    }
    arsort($delimiters, SORT_NUMERIC);

    return array_key_first($delimiters);
}

/**
 * Remove all keys from $json witch are not in $fields
 *
 * @param array $json
 * @param array $fields
 *
 * @return array
 */
function clean_fields(array $json, array $fields): array
{
    $clean_json = [];
    foreach ($json as $j) {
        $clean_json[] = array_intersect_key($j, array_flip($fields));
    }

    return $clean_json;
}

/**
 * Aggregate on one fields
 *
 * @param array $json
 * @param array $aggregate
 *
 * @return array
 */
function aggregate_fields(array $json, string $aggregate): array
{
    $aggregate_json = [];
    foreach ($json as $j) {
        if (!isset($aggregate_json[$j[$aggregate]])) {
            $aggregate_json[$j[$aggregate]] = [];
        }
        $tmp = $j[$aggregate];
        unset($j[$aggregate]);
        $aggregate_json[$tmp][] = $j;
    }

    return $aggregate_json;
}
