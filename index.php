<?php
/**
 * Standalone PromptPay QR Code Generator
 * No external dependencies required - uses built-in PHP functions
 */

class PromptPayStandalone {
    
    const ID_PAYLOAD_FORMAT = '00';
    const ID_POI_METHOD = '01';
    const ID_MERCHANT_INFORMATION_BOT = '29';
    const ID_TRANSACTION_CURRENCY = '53';
    const ID_TRANSACTION_AMOUNT = '54';
    const ID_COUNTRY_CODE = '58';
    const ID_CRC = '63';
    
    const PAYLOAD_FORMAT_EMV_QRCPS_MERCHANT_PRESENTED_MODE = '01';
    const POI_METHOD_STATIC = '11';
    const POI_METHOD_DYNAMIC = '12';
    const MERCHANT_INFORMATION_TEMPLATE_ID_GUID = '00';
    const BOT_ID_MERCHANT_PHONE_NUMBER = '01';
    const BOT_ID_MERCHANT_TAX_ID = '02';
    const BOT_ID_MERCHANT_EWALLET_ID = '03';
    const GUID_PROMPTPAY = 'A000000677010111';
    const TRANSACTION_CURRENCY_THB = '764';
    const COUNTRY_CODE_TH = 'TH';

    public function generatePayload($target, $amount = null) {
        $target = $this->sanitizeTarget($target);
        $targetType = strlen($target) >= 15 ? self::BOT_ID_MERCHANT_EWALLET_ID : 
                     (strlen($target) >= 13 ? self::BOT_ID_MERCHANT_TAX_ID : self::BOT_ID_MERCHANT_PHONE_NUMBER);

        $data = [
            $this->f(self::ID_PAYLOAD_FORMAT, self::PAYLOAD_FORMAT_EMV_QRCPS_MERCHANT_PRESENTED_MODE),
            $this->f(self::ID_POI_METHOD, $amount ? self::POI_METHOD_DYNAMIC : self::POI_METHOD_STATIC),
            $this->f(self::ID_MERCHANT_INFORMATION_BOT, $this->serialize([
                $this->f(self::MERCHANT_INFORMATION_TEMPLATE_ID_GUID, self::GUID_PROMPTPAY),
                $this->f($targetType, $this->formatTarget($target))
            ])),
            $this->f(self::ID_COUNTRY_CODE, self::COUNTRY_CODE_TH),
            $this->f(self::ID_TRANSACTION_CURRENCY, self::TRANSACTION_CURRENCY_THB),
        ];
        
        if ($amount !== null && $amount > 0) {
            array_push($data, $this->f(self::ID_TRANSACTION_AMOUNT, $this->formatAmount($amount)));
        }
        
        $dataToCrc = $this->serialize($data) . self::ID_CRC . '04';
        array_push($data, $this->f(self::ID_CRC, $this->crc16($dataToCrc)));
        
        return $this->serialize($data);
    }

    private function f($id, $value) {
        return implode('', [$id, substr('00' . strlen($value), -2), $value]);
    }
    
    private function serialize($xs) {
        return implode('', $xs);
    }
    
    private function sanitizeTarget($str) {
        return preg_replace('/[^0-9]/', '', $str);
    }

    private function formatTarget($target) {
        $str = $this->sanitizeTarget($target);
        if (strlen($str) >= 13) {
            return $str;
        }
        
        $str = preg_replace('/^0/', '66', $str);
        $str = '0000000000000' . $str;
        
        return substr($str, -13);
    }

    private function formatAmount($amount) {
        return number_format($amount, 2, '.', '');
    }

    private function crc16($data) {
        $crc = 0xFFFF;
        $polynomial = 0x1021;
        
        for ($i = 0; $i < strlen($data); $i++) {
            $crc ^= ord($data[$i]) << 8;
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ $polynomial;
                } else {
                    $crc = $crc << 1;
                }
            }
        }
        
        return strtoupper(dechex($crc & 0xFFFF));
    }

    /**
     * Generate QR code using goQR.me API (no local dependencies)
     * Note: Requires internet connection
     */
    public function generateQrCodeUrl($target, $amount = null, $size = 300) {
        $payload = $this->generatePayload($target, $amount);
        
        // Use goQR.me API with better parameters
        $params = [
            'data' => $payload,
            'size' => $size . 'x' . $size,
            'ecc' => 'M',  // Medium error correction (15% redundancy)
            'format' => 'png',
            'qzone' => 1,  // Quiet zone for better scanning
            'charset-source' => 'UTF-8',
            'charset-target' => 'UTF-8'
        ];
        
        return "https://api.qrserver.com/v1/create-qr-code/?" . http_build_query($params);
    }

    /**
     * Generate and save QR code using goQR.me API
     */
    public function generateQrCodeFile($savePath, $target, $amount = null, $size = 300) {
        $qrUrl = $this->generateQrCodeUrl($target, $amount, $size);
        
        // Use cURL for better error handling
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $qrUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $qrData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($qrData === false || !empty($error) || $httpCode !== 200) {
            throw new Exception('Failed to generate QR code: ' . ($error ?: 'HTTP ' . $httpCode));
        }
        
        return file_put_contents($savePath, $qrData);
    }

    /**
     * Generate and return QR code image data directly
     */
    public function generateQrCodeData($target, $amount = null, $size = 300) {
        $qrUrl = $this->generateQrCodeUrl($target, $amount, $size);
        
        // Use cURL for better error handling
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $qrUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $qrData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($qrData === false || !empty($error) || $httpCode !== 200) {
            throw new Exception('Failed to generate QR code: ' . ($error ?: 'HTTP ' . $httpCode));
        }
        
        return $qrData;
    }
}

// API endpoint check
if (isset($_GET['api']) && $_GET['api'] === '1') {
    header('Content-Type: application/json');
    
    try {
        // Get parameters from GET or POST
        $target = trim($_REQUEST['target'] ?? '');
        $amount = trim($_REQUEST['amount'] ?? '');
        $size = intval($_REQUEST['size'] ?? 300);
        $format = strtolower($_REQUEST['format'] ?? 'image');
        
        // Validate size parameter
        if ($size < 50 || $size > 1000) {
            $size = 300;
        }
        
        // Validate required parameters
        if (empty($target)) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Missing required parameter: target',
                'message' => 'Please provide a phone number, tax ID, or e-wallet ID'
            ]);
            exit;
        }
        
        $pp = new PromptPayStandalone();
        
        if ($format === 'image') {
            // Return QR code image directly
            $qrData = $pp->generateQrCodeData($target, $amount ? floatval($amount) : null, $size);
            
            header('Content-Type: image/png');
            header('Content-Disposition: inline; filename="promptpay-qr.png"');
            header('Cache-Control: public, max-age=3600'); // Cache for 1 hour
            echo $qrData;
            
        } else if ($format === 'json') {
            // Return JSON response with payload and QR URL
            $payload = $pp->generatePayload($target, $amount ? floatval($amount) : null);
            $qrUrl = $pp->generateQrCodeUrl($target, $amount ? floatval($amount) : null, $size);
            
            echo json_encode([
                'success' => true,
                'payload' => $payload,
                'qr_url' => $qrUrl,
                'target' => $target,
                'amount' => $amount ? floatval($amount) : null,
                'size' => $size
            ]);
            
        } else if ($format === 'base64') {
            // Return base64 encoded image
            $qrData = $pp->generateQrCodeData($target, $amount ? floatval($amount) : null, $size);
            $base64 = base64_encode($qrData);
            
            echo json_encode([
                'success' => true,
                'image_base64' => 'data:image/png;base64,' . $base64,
                'payload' => $pp->generatePayload($target, $amount ? floatval($amount) : null),
                'target' => $target,
                'amount' => $amount ? floatval($amount) : null,
                'size' => $size
            ]);
            
        } else {
            http_response_code(400);
            echo json_encode([
                'error' => 'Invalid format parameter',
                'message' => 'Supported formats: image, json, base64'
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Internal server error',
            'message' => $e->getMessage()
        ]);
    }
    
    exit; // Stop execution for API calls
}

// Main application logic for web interface
$qrCodePath = '';
$payload = '';
$error = '';
$qrCodeUrl = '';

if ($_POST) {
    $target = trim($_POST['target'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    
    if (empty($target)) {
        $error = 'Please enter a phone number, tax ID, or e-wallet ID';
    } else {
        try {
            $pp = new PromptPayStandalone();
            $payload = $pp->generatePayload($target, $amount ? floatval($amount) : null);
            
            // Generate QR code URL for display
            $qrCodeUrl = $pp->generateQrCodeUrl($target, $amount ? floatval($amount) : null, 300);
            
            // Optionally save QR code file
            $qrCodePath = 'qr_' . time() . '.png';
            $pp->generateQrCodeFile($qrCodePath, $target, $amount ? floatval($amount) : null, 300);
            
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PromptPay QR Code Generator - Standalone</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🇹🇭 PromptPay QR Code Generator</h1>
        <div class="warning">
            <strong>⚠️ Note:</strong> This standalone version uses goQR.me API for QR code generation and requires an internet connection.
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
