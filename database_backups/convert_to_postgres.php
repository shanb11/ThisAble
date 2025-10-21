<?php
$input_file = 'jobportal_db_backup_2025.sql';
$output_file = 'jobportal_db_postgres.sql';

$contents = file_get_contents($input_file);

// Remove MySQL-specific comments and syntax
$contents = preg_replace('/\/\*![0-9]+\s+.*?\*\//s', '', $contents); // /*! ... */
$contents = preg_replace('/--.*?\n/', "\n", $contents); // -- comments
$contents = preg_replace('/`/', '', $contents); // backticks
$contents = preg_replace('/ENGINE=\w+\s*/i', '', $contents);
$contents = preg_replace('/DEFAULT CHARSET=\w+/i', '', $contents);
$contents = preg_replace('/COLLATE=\w+/i', '', $contents);
$contents = preg_replace('/ON UPDATE CURRENT_TIMESTAMP/i', '', $contents);

// Convert integer types with width to PostgreSQL types
$contents = preg_replace_callback(
    '/\b(int|bigint|mediumint|smallint|tinyint)\(\d+\)\b/i',
    function($matches) {
        $type = strtolower($matches[1]);
        if ($type == 'tinyint') return 'BOOLEAN'; // tinyint(1) → BOOLEAN
        elseif ($type == 'mediumint') return 'INTEGER';
        else return strtoupper($type); // int → INTEGER, bigint → BIGINT
    },
    $contents
);

// Convert AUTO_INCREMENT → SERIAL PRIMARY KEY
$contents = preg_replace_callback('/(\w+)\s+INTEGER\s+NOT NULL\s+AUTO_INCREMENT/i', function($matches) {
    return "{$matches[1]} SERIAL PRIMARY KEY";
}, $contents);

// Convert ENUMs to PostgreSQL ENUM types
preg_match_all('/enum\((.*?)\)/i', $contents, $matches);
$enum_map = [];
$enum_counter = 1;
foreach ($matches[1] as $enum_values) {
    $enum_name = "enum_type_$enum_counter";
    $values = array_map(function($v) {
        return "'" . str_replace("'", "''", trim($v, "'")) . "'";
    }, explode(',', $enum_values));
    $enum_map[$enum_name] = implode(',', $values);
    $contents = preg_replace('/enum\(' . preg_quote($enum_values, '/') . '\)/i', $enum_name, $contents, 1);
    $enum_counter++;
}

// Convert JSON validation LONGTEXT → JSONB
$contents = preg_replace('/longtext.*CHECK\s*\(json_valid\([^)]+\)\)/i', 'JSONB', $contents);

// Boolean defaults
$contents = preg_replace('/DEFAULT\s+0\b/i', 'DEFAULT FALSE', $contents);
$contents = preg_replace('/DEFAULT\s+1\b/i', 'DEFAULT TRUE', $contents);

// Convert INSERT statements numeric booleans → TRUE/FALSE
$contents = preg_replace_callback('/INSERT INTO (.*?) VALUES\s*\((.*?)\);/is', function($matches) {
    $table = $matches[1];
    $values = $matches[2];
    // Only replace 0/1 standalone numeric
    $values = preg_replace('/\b0\b/', 'FALSE', $values);
    $values = preg_replace('/\b1\b/', 'TRUE', $values);
    return "INSERT INTO $table VALUES ($values);";
}, $contents);

// Write ENUM types at the top
$enum_sql = '';
foreach ($enum_map as $name => $values) {
    $enum_sql .= "CREATE TYPE $name AS ENUM ($values);\n";
}

// Combine ENUMs and table definitions
$final_sql = $enum_sql . "\n" . $contents;

// Remove multiple blank lines
$final_sql = preg_replace("/\n\s*\n/", "\n\n", $final_sql);

// Save final SQL
file_put_contents($output_file, $final_sql);

echo "Conversion complete! Output saved to $output_file\n";
?>
