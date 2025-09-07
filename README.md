# PromptPay QR Code Generator

This project provides a simple and effective tool to generate PromptPay QR codes for receiving payments in Thailand. It comes in two flavors: a server-side PHP version and a client-side HTML/JavaScript version.

Users can input a PromptPay target (phone number, National ID/Tax ID, or e-Wallet ID) and an optional amount to instantly generate a scannable QR code.

## Features

| Feature | PHP Version | HTML/JS Version |
| :--- | :---: | :---: |
| **QR Code Generation** | Server-side (PNG) | Client-side (Canvas) |
| **Dependencies** | `kittinan/php-promptpay-qr` | `qrcode.js` (CDN) |
| **Input Validation** | ✅ | ✅ |
| **Supports Phone, Tax ID, e-Wallet** | ✅ | ✅ |
| **Optional Amount** | ✅ | ✅ |
| **Real-time QR Generation** | ✅ | ✅ |
| **Download QR Code** | ✅ | ❌ |
| **Automatic File Cleanup** | ✅ | N/A |
| **Works Offline** | ❌ | ✅ (after first load) |
| **Responsive Design** | ✅ | ✅ |

---

## Setup and Usage

You can choose the version that best fits your needs.

### 1. PHP Version (`promptpay-qr.php`)

This version generates a `.png` image of the QR code on the server, allows users to download it, and automatically cleans up old files.

**Setup:**

1.  **Install dependencies:** Make sure you have [Composer](https://getcomposer.org/) installed. Run the following command in your project directory:
    ```bash
    composer require kittinan/php-promptpay-qr
    ```
2.  **Deploy:** Place the `promptpay-qr.php` file and the `vendor` directory on your PHP-enabled web server.
3.  **Permissions:** Ensure the directory where `promptpay-qr.php` resides is writable by the web server, so it can create the QR code image files.
    ```bash
    # Example: Set write permissions for the web server user
    chown www-data:www-data /path/to/your/web/directory
    chmod 755 /path/to/your/web/directory
    ```
4.  **Access:** Open the URL to `promptpay-qr.php` in your web browser.

### 2. HTML/JavaScript Version (`promptpay.html`)

This version is a single, self-contained HTML file that works entirely within the user's web browser. It's highly portable and requires no server-side processing.

**Setup:**

1.  **Save the file:** Simply save the code as `promptpay.html`.
2.  **Open in browser:** Open the file directly in any modern web browser. An internet connection is needed on the first run to load the `qrcode.js` library from the CDN.

---

## How It Works

Both versions follow the EMVCo Merchant-Presented QR Code standard to generate a valid PromptPay payload string.

The payload is constructed by concatenating several fields, each with a specific ID, length, and value. Key fields include:
-   **Payload Format Indicator** (ID `00`)
-   **Point-of-Initiation Method** (ID `01`): `11` for static QR (no amount), `12` for dynamic QR (with amount).
-   **Merchant Information** (ID `29`): Contains the PromptPay GUID (`A000000677010111`) and the recipient's formatted phone number, Tax ID, or e-Wallet ID.
-   **Country Code** (ID `58`): `TH` for Thailand.
-   **Transaction Currency** (ID `53`): `764` for Thai Baht.
-   **Transaction Amount** (ID `54`): The amount of the transaction, if specified.
-   **CRC Checksum** (ID `63`): A CRC-16-CCITT checksum calculated over the entire payload string to ensure data integrity.

This payload string is then encoded into a QR code image.
