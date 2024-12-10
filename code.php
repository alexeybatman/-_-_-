<?php
$directory = '.'; // set the directory to scan

// create an array to store the file contents
$files = array();

// loop through all the files in the directory
foreach (glob($directory . '/*.{php,html}', GLOB_BRACE) as $filename) {
    // get the file name
    $fileName = basename($filename);

    // read the file contents
    $fileContents = file_get_contents($filename);

    // encode the file contents to display as plain text
    $fileContents = htmlspecialchars($fileContents);

    // add the file name and contents to the array
    $files[$fileName] = $fileContents;
}

// loop through the array and display the file name and contents
foreach ($files as $fileName => $fileContents) {
    echo PHP_EOL;
    echo "Filename: " . $fileName . "\n";
    echo "Code:\n" . $fileContents . "\n\n";
}
?>