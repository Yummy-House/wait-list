# 📧 PHPMailer Email Configuration Guide

## ✅ **Installation Complete**

PHPMailer has been successfully installed and integrated into your YummyHouse Waitlist application!

## 🔧 **Configuration Steps**

### 1. Update Your `.env` File

Open `c:\wamp64\www\yummyhouse-waitlist\.env` and update the following settings:

```env
# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_AUTH=true
SMTP_USERNAME=your-actual-email@gmail.com
SMTP_PASSWORD=your-app-password

# Email Settings
MAIL_FROM_ADDRESS=your-actual-email@gmail.com
MAIL_FROM_NAME="YummyHouse Waitlist"
SUPPORT_EMAIL=support@yummyhouse.com
```

### 2. Gmail Configuration (Recommended)

If you're using Gmail:

1. **Enable 2-Factor Authentication** on your Google account
2. **Generate an App Password:**
   - Go to [Google Account Settings](https://myaccount.google.com/)
   - Security → 2-Step Verification → App passwords
   - Generate password for "Mail"
   - Use this password in `SMTP_PASSWORD` (not your regular Gmail password)

### 3. Other Email Providers

#### **Outlook/Hotmail:**
```env
SMTP_HOST=smtp-mail.outlook.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
```

#### **Yahoo:**
```env
SMTP_HOST=smtp.mail.yahoo.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
```

#### **Custom SMTP Server:**
Update the settings according to your provider's documentation.

## 🧪 **Testing Your Configuration**

### Method 1: Web Interface
1. Open `http://localhost/yummyhouse-waitlist/email-test.html` in your browser
2. Click "Test SMTP Connection" to verify settings
3. Send a test welcome email to yourself

### Method 2: API Testing
Test SMTP connection:
```bash
curl -X POST http://localhost/yummyhouse-waitlist/api/email-test.php \
  -H "Content-Type: application/json" \
  -d '{"action": "test_connection"}'
```

Send test welcome email:
```bash
curl -X POST http://localhost/yummyhouse-waitlist/api/email-test.php \
  -H "Content-Type: application/json" \
  -d '{"action": "test_welcome", "email": "your-email@example.com"}'
```

## 🚀 **Features Available**

### ✨ **Automatic Welcome Emails**
- Sent automatically when users register via `api/waitlist.php`
- Personalized based on user type and preferences
- Beautiful HTML templates with your branding

### 📊 **Email Logging**
- All email attempts logged to `email_logs` table
- Track success/failure rates
- Debug email issues

### 📤 **Bulk Email Functionality**
- Send announcements to all waitlist users
- Filter by user type or preferences
- Built-in rate limiting to prevent spam issues

### 🎨 **Professional Email Templates**
- Responsive HTML design
- Branded styling with gradients
- Unsubscribe links and proper headers

## 📋 **API Endpoints**

### Registration with Auto-Email
```
POST /api/waitlist.php
```
Now automatically sends welcome email after successful registration.

### Email Testing
```
POST /api/email-test.php
```
Available actions:
- `test_connection` - Test SMTP settings
- `test_welcome` - Send test welcome email
- `send_bulk` - Send bulk email to all users

## 🔍 **Troubleshooting**

### Common Issues:

1. **"SMTP connection failed"**
   - Check your credentials in `.env`
   - Verify app password for Gmail
   - Check firewall/antivirus blocking SMTP

2. **"Authentication failed"**
   - Double-check username/password
   - For Gmail, ensure you're using app password, not regular password

3. **"Connection timeout"**
   - Verify SMTP host and port
   - Check if your ISP blocks SMTP ports

4. **Emails go to spam**
   - Add proper SPF/DKIM records to your domain
   - Use a proper "from" address
   - Avoid spam trigger words

## 📈 **Email Analytics**

The system now logs all email attempts in the `email_logs` table:
- Recipient email
- Subject line
- Template used
- Status (sent/failed)
- Error messages
- Timestamp

## 🛡️ **Security Best Practices**

1. **Never commit `.env` file** to version control
2. **Use app passwords** instead of main account passwords
3. **Regularly rotate** SMTP credentials
4. **Monitor email logs** for suspicious activity
5. **Implement rate limiting** for bulk emails

## 🎯 **Next Steps**

1. Update your `.env` file with real credentials
2. Test the connection using the web interface
3. Send yourself a test welcome email
4. Customize email templates if needed
5. Set up proper domain records for better deliverability

Your email system is now ready to use! 🎉
