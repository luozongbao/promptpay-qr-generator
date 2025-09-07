## Setup Instructions

a complete webpage that allows users to input the target (phone/tax ID) and amount to generate a PromptPay QR code. 

### For HTML/JavaScript Version:
1. Save as `promptpay.html`
2. Open in any web browser
3. Works entirely client-side

### For PHP Version:
1. Install the library: `composer require kittinan/php-promptpay-qr`
2. Save as `promptpay_qr.php`
3. Place in your web server directory
4. Make sure the directory is writable for QR code generation
5. Access via web browser

## Features:
- ✅ Input validation
- ✅ Support for phone numbers, tax IDs, and e-wallet IDs
- ✅ Optional amount field
- ✅ Real-time QR code generation
- ✅ Responsive design
- ✅ Error handling
- ✅ Download functionality (PHP version)
- ✅ Automatic cleanup of old QR files (PHP version)

The JavaScript version works entirely in the browser, while the PHP version generates actual PNG files on the server and provides download functionality.