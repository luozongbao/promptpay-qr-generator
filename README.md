# PromptPay QR Code Generator

A simple and effective tool to generate PromptPay QR codes for receiving payments in Thailand. This standalone PHP application requires zero dependencies and uses the goQR.me API for QR code generation.

Users can input a PromptPay target (phone number, National ID/Tax ID, or e-Wallet ID) and an optional amount to instantly generate a scannable QR code.

## ✨ Features

- ✅ **Zero Dependencies** - No Composer or external libraries required
- ✅ **Input Validation** - Supports phone numbers, tax IDs, and e-wallet IDs
- ✅ **Optional Amount** - Generate QR codes with or without payment amounts
- ✅ **Real-time QR Generation** - Instant QR code creation using goQR.me API
- ✅ **Download QR Code** - Save generated QR codes as PNG files
- ✅ **Automatic File Cleanup** - Old QR files are automatically removed
- ✅ **Responsive Design** - Works on desktop and mobile devices
- ✅ **Error Handling** - Comprehensive error checking and user feedback

---

## 🚀 Quick Start

### Setup

1. **Upload**: Simply upload the `index.php` file to your PHP-enabled web server
2. **Permissions**: Ensure the directory is writable for QR code file saving:
   ```bash
   # Example: Set write permissions for the web server user
   chown www-data:www-data /path/to/your/web/directory
   chmod 755 /path/to/your/web/directory
   ```
3. **Access**: Open your domain/subdirectory in a web browser

### Requirements

- PHP 7.0 or higher
- cURL extension enabled
- Internet connection (for QR code generation via goQR.me API)
- Writable directory for temporary QR code files

---

## 📱 Supported PromptPay Formats

| Format | Example | Description |
|--------|---------|-------------|
| **Phone Number** | `0899999999` or `089-999-9999` | Thai mobile phone numbers |
| **National/Tax ID** | `1-2345-67890-12-3` | 13-digit Thai identification |
| **e-Wallet ID** | `123456789012345` | 15+ digit e-wallet identifiers |

---

## 🔧 How It Works

The application follows the EMVCo Merchant-Presented QR Code standard to generate valid PromptPay payload strings.

### Payload Structure

The payload contains several standardized fields:

- **Payload Format Indicator** (ID `00`): EMV standard compliance
- **Point-of-Initiation Method** (ID `01`): Static (`11`) or Dynamic (`12`) QR codes
- **Merchant Information** (ID `29`): PromptPay GUID and recipient details
- **Country Code** (ID `58`): `TH` for Thailand
- **Transaction Currency** (ID `53`): `764` for Thai Baht (THB)
- **Transaction Amount** (ID `54`): Payment amount (if specified)
- **CRC Checksum** (ID `63`): Data integrity verification using CRC-16-CCITT

### QR Code Generation

The application uses the goQR.me API (`api.qrserver.com`) with optimized parameters:

- **Error Correction**: Medium level (15% data redundancy)
- **Quiet Zone**: 1-module border for better scanning
- **Format**: PNG with UTF-8 encoding
- **Size**: Configurable (default 300x300 pixels)

---

## 🗂️ Project Structure

```
├── index.php          # Main application (standalone, zero dependencies)
├── README.md          # This documentation
└── qr_*.png          # Generated QR code files (auto-cleaned after 1 hour)
```

---

## 🔒 Security & Privacy

- **No Data Storage**: PromptPay payloads are not stored on the server
- **Temporary Files**: QR code images are automatically deleted after 1 hour
- **External API**: QR generation uses goQR.me API (payload data is transmitted for QR creation)
- **Input Sanitization**: All user inputs are properly sanitized and validated

---

## 🚀 Deployment Tips

### For Production Use

1. **HTTPS**: Always use HTTPS in production for secure data transmission
2. **Rate Limiting**: Consider implementing rate limiting to prevent abuse
3. **Monitoring**: Monitor goQR.me API availability and response times
4. **Backup**: Keep backups of your `index.php` file

### For Development

```bash
# Quick local development with PHP built-in server
php -S localhost:8000 index.php
```

Then visit `http://localhost:8000` in your browser.

---

## 🆚 Advantages Over Other Solutions

- **Simplicity**: Single file deployment vs complex dependency management
- **Reliability**: Uses established goQR.me API vs experimental solutions
- **Maintainability**: Self-contained code vs multiple external dependencies
- **Performance**: Direct API calls vs heavy QR generation libraries
- **Portability**: Works on any PHP hosting vs specific server requirements
