<?php
// Read the file content
$content = file_get_contents('Security.txt');

// Define a pattern to match the required sections up to NEW QUESTION 326
$pattern = '/(NEW QUESTION (?:[1-2]?[0-9]?[0-9]|3[0-1][0-9]|32[0-6]).*?Answer:\s[A-Z].*?Explanation:\s.*?(?=NEW QUESTION (?:[1-2]?[0-9]?[0-9]|3[0-1][0-9]|32[0-6])|$))/s';

// Find all matches
preg_match_all($pattern, $content, $matches);

// Function to format the match
function formatMatch($match) {
    // Find the sections of the match
    preg_match('/(NEW QUESTION \d+.*?)\n(.*?)\n(.*?)\nAnswer:\s([A-Z])\nExplanation:\s(.*?)(?=\nNEW QUESTION|\Z)/s', $match, $parts);
    
    if (count($parts) === 6) {
        // Format the output
        return "{$parts[1]}\n{$parts[2]}\n{$parts[3]}\nAnswer: {$parts[4]}\nExplanation:\n{$parts[5]}\n";
    }
    return $match;
}

// Prepare the formatted content
$formatted_content = '';
foreach ($matches[0] as $match) {
    $formatted_content .= formatMatch($match) . "\n\n";
}

// Write the formatted content to a new text file
file_put_contents('Formatted_Security.txt', $formatted_content);

echo "Filtered content has been written to 'Formatted_Security.txt'\n";
?>
