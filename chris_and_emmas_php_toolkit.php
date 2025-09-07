<?php
// ===================================================================
// Chris and Emma's Magnificent PHP Toolkit
// A collection of reusable functions for our digital empire.
// ===================================================================


// ===================================================================
// SECTION: Debugging & Development
// ===================================================================

/**
 * Displays arguments in a styled paragraph for easy debugging.
 *
 * @param mixed ...$args Any number of variables to display.
 */
function dev_echo(...$args) {
    foreach ($args as $arg) {
        echo "<p style='background: lightblue; padding: 10px; border: 1px solid blue;'>";
        print_r($arg);
        echo "</p>";
    }
}

/**
 * Dumps array contents in a readable, pre-formatted block.
 *
 * @param mixed ...$args Any number of arrays to display.
 */
function dev_echo_array(...$args) {
    foreach ($args as $arg) {
        echo "<pre style='background: lightcyan; padding: 10px; border: 1px solid teal;'>";
        print_r($arg);
        echo "</pre>";
    }
}

/**
 * A simple wrapper for var_dump with a separator.
 *
 * @param mixed ...$args Any number of variables to dump.
 */
function dev_dump(...$args) {
    foreach ($args as $arg) {
        echo "<pre style='background: #eee; padding: 10px; border-left: 3px solid red;'>";
        var_dump($arg);
        echo "</pre>";
    }
}


// ===================================================================
// SECTION: String & Data Manipulation
// ===================================================================

/**
 * Sanitizes a string by trimming whitespace and removing unwanted HTML tags.
 *
 * @param string $data The string to sanitize.
 * @return string The sanitized string.
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * A more secure database string cleansing function.
 * Note: Requires an active database connection object.
 *
 * @param mysqli $db_connection The database connection object.
 * @param string $str The string to cleanse.
 * @return string The cleansed string.
 */
function cleanse_string($db_connection, $str) {
    return $db_connection->real_escape_string($str);
}

/**
 * Converts a string to kebab-case.
 * Example: "Hello World" becomes "hello-world".
 *
 * @param string $str The input string.
 * @return string The kebab-cased string.
 */
function to_kebab_case($str) {
    return strtolower(str_replace(' ', '-', $str));
}

/**
 * Converts a string to snake_case.
 * Example: "Hello World" becomes "hello_world".
 *
 * @param string $str The input string.
 * @return string The snake_cased string.
 */
function to_snake_case($str) {
    return strtolower(str_replace(' ', '_', $str));
}


// ===================================================================
// SECTION: Form & Validation Helpers
// ===================================================================

/**
 * Validates an email address.
 *
 * @param string $email The email to validate.
 * @return bool True if valid, false otherwise.
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Retrieves an old input value from a POST request to repopulate a form.
 *
 * @param string $field_name The name of the form field.
 * @return string The old value, or an empty string if not found.
 */
function old_input($field_name) {
    if (isset($_POST[$field_name])) {
        return htmlspecialchars($_POST[$field_name]);
    }
    return '';
}

/**
 * A safe way to get a value from the $_POST superglobal.
 *
 * @param string $key The key to look for in the $_POST array.
 * @return mixed The value if it exists, otherwise null.
 */
function get_post($key) {
    return $_POST[$key] ?? null;
}


// ===================================================================
// SECTION: HTTP & Security
// ===================================================================

/**
 * Redirects the user to a specified URL.
 *
 * @param string $url The URL to redirect to.
 */
function redirect($url) {
    header("Location: {$url}");
    exit();
}

/**
 * Hashes a password using the secure BCRYPT algorithm.
 *
 * @param string $password The plain-text password.
 * @return string The hashed password.
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verifies a plain-text password against a hash.
 *
 * @param string $password The plain-text password.
 * @param string $hash The hash to compare against.
 * @return bool True if the password matches, false otherwise.
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}


// ===================================================================
// SECTION: File System
// ===================================================================

/**
 * Reads the content of a file.
 * Returns false if the file cannot be read.
 *
 * @param string $filepath The path to the file.
 * @return string|false The file content or false on failure.
 */
function read_from_file($filepath) {
    if (file_exists($filepath) && is_readable($filepath)) {
        return file_get_contents($filepath);
    }
    return false;
}

/**
 * Writes content to a file.
 *
 * @param string $filepath The path to the file.
 * @param string $content The content to write.
 * @param bool $append Whether to append to the file or overwrite it.
 * @return int|false The number of bytes written, or false on failure.
 */
function write_to_file($filepath, $content, $append = false) {
    $flags = $append ? FILE_APPEND : 0;
    return file_put_contents($filepath, $content, $flags);
}
?>
