<?php

require_once "function.php";

// Define option
try {
    list ($filename, $fields, $aggregate, $desc, $pretty) = parse_arg($argv);
} catch (NoFileException $e) {
    echo "No file\n";
    exit(1);
} catch (InvalidOptionException $e) {
    echo "Invalid option " + $e->getMessage() + "\n";
    exit(1);
}

// Open and parse file
if (($handle = fopen($filename, "r")) !== FALSE) {
    $i = 0;
    // Get the first 5 lines to guess the delimiters
    $extract = [];
    while (($buffer = fgets($handle)) !== false && ++$i <= 5) {
        $extract[] = trim($buffer);
    }
    $delimiter = guess_delimiter($extract);
    fseek($handle, 0);

    // Get the json in a array
    $json = [];
    $keys = fgetcsv($handle, 0, $delimiter);
    while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
        $json[] = array_combine($keys, $data);
    }
    fclose($handle);

    // Remove non printable fields
    if ($fields !== null) {
        $json = clean_fields($json, $fields);
    }

    // Aggregate fields
    if ($aggregate) {
        if (!in_array($aggregate, $keys)) {
            echo "Aggregate fields is not valid\n";
            exit(1);
        }
        $json = aggregate_fields($json, $aggregate);
    }

    // Field description
    // TODO

    // Pretty print
    if ($pretty === true) {
        echo json_encode($json, JSON_PRETTY_PRINT);
    } else {
        echo json_encode($json);
    }

} else {
    echo "The files is not valid\n";
    exit(1);
}

exit(0);
