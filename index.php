<?php
/**
 * PromptPay QR Code Generator - Frontend
 * Uses the /api/ endpoint for QR code generation
 */

$error = '';
$success = false;
$qrCodeUrl = '';
$payload = '';
$target = '';
$amount = '';

if ($_POST) {
    $target = trim($_POST['target'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    
    if (empty($target)) {
        $error = 'Please enter a phone number, tax ID, or e-wallet ID';
    } else {
        // Call our API endpoint
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $currentDir = dirname($_SERVER['REQUEST_URI']);
        if ($currentDir === '/') $currentDir = '';
        $apiUrl = $protocol . '://' . $host . $currentDir . '/api/?format=json';
        
        $postData = [
            'target' => $target,
            'amount' => $amount,
            'size' => 300
        ];
        
        // Use cURL to call our API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($response === false || !empty($curlError)) {
            $error = 'Failed to connect to API: ' . $curlError;
        } else {
            $data = json_decode($response, true);
            
            if ($httpCode === 200 && $data && isset($data['success']) && $data['success']) {
                $success = true;
                $qrCodeUrl = $data['qr_url'];
                $payload = $data['payload'];
            } else {
                $error = $data['message'] ?? 'Unknown error occurred';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PromptPay QR Code Generator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="text"]:focus, input[type="number"]:focus {
            border-color: #4CAF50;
            outline: none;
        }
        button {
            width: 100%;
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        .qr-container {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .qr-image {
            margin: 20px auto;
            border: 2px solid #ddd;
            border-radius: 5px;
            max-width: 100%;
        }
        .payload-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #e8f5e8;
            border-radius: 5px;
            word-break: break-all;
            font-family: monospace;
            font-size: 12px;
        }
        .error {
            color: red;
            margin-top: 10px;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 5px;
        }
        .info {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .download-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .download-btn:hover {
            background-color: #1976D2;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .api-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #495057;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 14px;
        }
        .api-info h3 {
            margin-top: 0;
            color: #333;
        }
        .api-info code {
            background-color: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            word-break: break-all;
        }
        .api-info ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .api-info li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🇹🇭 PromptPay QR Code Generator</h1>
        <div class="warning">
            <strong>⚠️ Note:</strong> This application uses goQR.me API for QR code generation and requires an internet connection.
        </div>
        
        <div class="info">
            <strong>Supported formats:</strong><br>
            • Phone: 0899999999 or 089-999-9999<br>
            • Tax ID: 1-2345-67890-12-3<br>
            • e-Wallet ID: 15+ digits
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="target">Phone Number / Tax ID / e-Wallet ID:</label>
                <input type="text" id="target" name="target" 
                       value="<?= htmlspecialchars($_POST['target'] ?? '') ?>" 
                       placeholder="0899999999 or 1-2345-67890-12-3" required>
            </div>

            <div class="form-group">
                <label for="amount">Amount (THB) - Optional:</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0" 
                       value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>" 
                       placeholder="100.00">
            </div>

            <button type="submit">Generate QR Code</button>
        </form>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($qrCodeUrl): ?>
            <div class="qr-container">
                <h3>Your PromptPay QR Code</h3>
                <img src="<?= htmlspecialchars($qrCodeUrl) ?>" alt="PromptPay QR Code" class="qr-image">
                <br>
                <?php if ($qrCodePath && file_exists($qrCodePath)): ?>
                    <a href="<?= htmlspecialchars($qrCodePath) ?>" download class="download-btn">Download QR Code</a>
                <?php endif; ?>
                
                <?php if ($payload): ?>
                    <div class="payload-info">
                        <strong>Payload:</strong><br><?= htmlspecialchars($payload) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="api-info">
            <h3>🔌 API Endpoint</h3>
            <p>This application provides a REST API endpoint for programmatic access:</p>
            <p><strong>Endpoint:</strong> <code><?= (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/') ?>/api/</code></p>
            <p><strong>Parameters:</strong></p>
            <ul>
                <li><code>target</code> - Phone number, Tax ID, or e-Wallet ID (required)</li>
                <li><code>amount</code> - Amount in THB (optional)</li>
                <li><code>size</code> - QR code size in pixels (optional, default: 300, range: 50-1000)</li>
                <li><code>format</code> - Response format: <code>image</code>, <code>json</code>, or <code>base64</code> (optional, default: image)</li>
            </ul>
            <p><strong>Examples:</strong></p>
            <ul>
                <li>Get QR image: <code><?= (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/') ?>/api/?target=0891234567&amp;amount=100</code></li>
                <li>Get JSON response: <code><?= (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/') ?>/api/?target=0891234567&amp;amount=100&amp;format=json</code></li>
            </ul>
        </div>
    </div>

    <?php
    // Clean up old QR code files (optional)
    $files = glob('qr_*.png');
    foreach ($files as $file) {
        if (filemtime($file) < time() - 3600) { // Delete files older than 1 hour
            unlink($file);
        }
    }
    ?>
</body>
</html>
