# PromptPay QR Code Generator

A professional Thai PromptPay QR Code generator with a clean web interface and REST API. Generate QR codes for payments using phone numbers, tax IDs, or e-wallet IDs with optional amounts.

## 🌟 Features

- ✅ **Zero Dependencies** - No Composer or external libraries required
- ✅ **Clean Architecture** - Separated frontend and API
- ✅ **Multiple Input Types** - Phone numbers, tax IDs, e-wallet IDs
- ✅ **Optional Amounts** - Generate QR codes with or without payment amounts
- ✅ **Professional UI** - Responsive design with PromptPay branding
- ✅ **REST API** - Easy integration with other applications
- ✅ **Multiple Output Formats** - Image, JSON, Base64
- ✅ **CORS Enabled** - Cross-origin request support
- ✅ **Mobile Friendly** - Works on all devices

## 📁 Project Structure

```
├── index.php                          # Frontend web interface
├── api/
│   └── index.php                      # REST API endpoint
├── assets/
│   └── images/
│       └── promptpay-logo.svg         # PromptPay official logo
│       └── thai-qr-payment.svg        # PromptPay official logo
└── README.md                          # Documentation
```

## 🚀 Quick Start

### Requirements
- PHP 7.0 or higher
- Web server (Apache, Nginx, or PHP built-in server)
- Internet connection (for QR code generation via goQR.me API)

### Installation
1. **Download or clone** the project files
2. **Upload to your web server** or run locally
3. **Set proper permissions** (755 for directories, 644 for files)
4. **Access the application** via your web browser

### Local Development
```bash
# Navigate to project directory
cd /path/to/promptpay-generator

# Start PHP development server
php -S localhost:8000

# Open in browser
open http://localhost:8000
```

## 🖥️ Web Interface

The main interface (`index.php`) provides:
- **User-friendly form** for entering PromptPay details
- **Real-time QR code generation** using the internal API
- **PromptPay logo branding** for professional appearance
- **Download functionality** for generated QR codes
- **API documentation** section for developers

### Supported PromptPay Formats

| Type | Format | Example |
|------|--------|---------|
| **Mobile Phone** | 10 digits starting with 0 | `0812345678` |
| **Tax ID** | 13 digits | `1234567890123` |
| **e-Wallet ID** | 15 digits | `123456789012345` |

## 🔌 REST API

### Base URL
```
https://yourdomain.com/api/
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `target` | string | ✅ Yes | Phone number, Tax ID, or e-Wallet ID |
| `amount` | float | ❌ No | Payment amount in Thai Baht |
| `size` | integer | ❌ No | QR code size in pixels (50-1000, default: 300) |
| `format` | string | ❌ No | Response format: `image`, `json`, `base64` |

### Response Formats

#### 1. Image Format (Default)
Returns PNG image directly with proper headers.

```bash
curl "https://yourdomain.com/api/?target=0812345678&amount=100.50" \
  --output qr-code.png
```

#### 2. JSON Format
Returns structured data with PromptPay payload and QR URL.

```bash
curl "https://yourdomain.com/api/?target=0812345678&amount=100.50&format=json"
```

**Response:**
```json
{
  "success": true,
  "target": "0812345678",
  "amount": 100.50,
  "payload": "00020101021129370016A000000677010111011300668123456785802TH5303764540610...",
  "qr_url": "https://api.qrserver.com/v1/create-qr-code/?data=00020101...",
  "size": 300
}
```

#### 3. Base64 Format
Returns base64-encoded image with metadata.

```bash
curl "https://yourdomain.com/api/?target=0812345678&format=base64"
```

**Response:**
```json
{
  "success": true,
  "target": "0812345678",
  "amount": null,
  "image_base64": "iVBORw0KGgoAAAANSUhEUgAA...",
  "mime_type": "image/png",
  "size": 300
}
```

### Error Responses

All errors return JSON with proper HTTP status codes:

```json
{
  "error": true,
  "message": "Target is required"
}
```

**Common Error Codes:**
- `400` - Bad Request (missing/invalid parameters)
- `500` - Internal Server Error (QR generation failed)

## 🔧 How It Works

### PromptPay Payload Structure
The application generates EMV-compliant PromptPay payloads with these components:

1. **Payload Format Indicator** (ID 00): Version "01"
2. **Point of Initiation** (ID 01): "12" for dynamic QR
3. **Merchant Account Information** (ID 29): PromptPay data
4. **Country Code** (ID 58): "TH" for Thailand
5. **Transaction Currency** (ID 53): "764" (Thai Baht)
6. **Transaction Amount** (ID 54): Amount in THB (if specified)
7. **CRC16** (ID 63): Checksum for data integrity

### QR Code Generation
- Uses **goQR.me API** for reliable QR code generation
- **Error correction level M** (15% redundancy) for better scanning
- **300x300 pixels** default size with customizable dimensions
- **PNG format** for high quality and broad compatibility

## 🛡️ Security & Privacy

- **No data storage** - QR codes are generated on-demand
- **External QR generation** - Uses goQR.me API (requires internet)
- **Input validation** - Validates PromptPay formats
- **CORS enabled** - Configurable for your domain
- **No sensitive data logging** - Minimal server-side processing

## 🌐 Integration Examples

### JavaScript/AJAX
```javascript
fetch('https://yourdomain.com/api/?target=0812345678&amount=100&format=json')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      document.getElementById('qr-img').src = data.qr_url;
    }
  });
```

### PHP
```php
$response = file_get_contents('https://yourdomain.com/api/?target=0812345678&format=json');
$data = json_decode($response, true);

if ($data['success']) {
    echo "QR URL: " . $data['qr_url'];
}
```

### Python
```python
import requests

response = requests.get('https://yourdomain.com/api/', params={
    'target': '0812345678',
    'amount': 100.50,
    'format': 'json'
})

data = response.json()
if data['success']:
    print(f"QR URL: {data['qr_url']}")
```

## 🚀 Deployment

### Production Setup
1. **Upload files** to your web server
2. **Configure web server** to serve the application
3. **Set proper permissions** (755/644)
4. **Test both frontend and API** endpoints
5. **Configure CORS** if needed for cross-origin requests

### Development Setup
```bash
# Clone or download project
git clone https://github.com/youruser/promptpay-qr-generator.git
cd promptpay-qr-generator

# Start development server
php -S localhost:8000

# Test API endpoint
curl "http://localhost:8000/api/?target=0812345678&format=json"
```

## 📋 API Usage Examples

### Generate QR Code for Phone Payment
```bash
curl "https://yourdomain.com/api/?target=0899999999&amount=150.75" \
  --output payment-qr.png
```

### Generate QR Code for Tax ID (No Amount)
```bash
curl "https://yourdomain.com/api/?target=1234567890123&format=json"
```

### Generate Large QR Code
```bash
curl "https://yourdomain.com/api/?target=0812345678&size=500&format=image" \
  --output large-qr.png
```

### Get Base64 for Email/SMS
```bash
curl "https://yourdomain.com/api/?target=0812345678&amount=50&format=base64"
```

## 🎯 Advantages

- **Self-contained** - Single file deployment for each component
- **Modern architecture** - Clean separation of frontend and API
- **Professional appearance** - Official PromptPay branding
- **Developer-friendly** - Comprehensive API with multiple formats
- **Production-ready** - Proper error handling and validation
- **Mobile-optimized** - Responsive design for all devices

## 📞 Support

This PromptPay QR Code Generator follows the official Thai PromptPay specification and generates EMV-compliant QR codes compatible with all major Thai banking applications.

For technical support or feature requests, please refer to the project documentation or create an issue in the project repository.

---

**Made with ❤️ for the Thai digital payment ecosystem**
