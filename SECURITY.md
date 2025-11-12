# Security Considerations

## Implemented Security Measures

### 1. Password Security
- User passwords are hashed using bcrypt (via `password_hash()`)
- SMTP passwords are base64 encoded (should be upgraded to proper encryption in production)

### 2. SQL Injection Protection
- All database queries use PDO prepared statements
- User input is parameterized, not concatenated

### 3. XSS Protection
- All output in views uses `htmlspecialchars()` for escaping
- User-generated content is sanitized before display

### 4. Authentication & Authorization
- Session-based authentication
- Role-based access control (admin, manager, user)
- Admin-only actions are protected at controller level

### 5. Multi-tenancy Isolation
- All queries filter by `organization_id`
- Users can only access data from their organization

## Security Recommendations for Production

### 1. SMTP Password Encryption
Current implementation uses base64 encoding which is NOT secure. Implement proper encryption:
```php
// Use openssl_encrypt with a secure key
$key = getenv('ENCRYPTION_KEY'); // Store securely
$encrypted = openssl_encrypt($password, 'AES-256-CBC', $key, 0, $iv);
```

### 2. CSRF Protection
Add CSRF tokens to all forms:
```php
// Generate token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// In forms
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// Validate in controllers
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF validation failed');
}
```

### 3. Rate Limiting
Implement rate limiting for:
- Login attempts
- API endpoints
- Email sending

### 4. HTTPS Only
- Enforce HTTPS in production
- Set secure cookie flags
- Use HSTS headers

### 5. Input Validation
- Validate all user input server-side
- Sanitize file uploads
- Validate email addresses
- Check file types and sizes

### 6. Session Security
```php
// In production
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
```

### 7. Database Security
- Use strong database passwords
- Limit database user permissions
- Enable database encryption at rest
- Regular backups

### 8. Email Security
- Implement SPF, DKIM, DMARC records
- Validate unsubscribe tokens
- Prevent email injection attacks
- Rate limit per recipient

### 9. File Upload Security
For CSV uploads:
- Validate file extension
- Check MIME type
- Scan for malicious content
- Store outside web root
- Set maximum file size

### 10. Logging & Monitoring
- Log security events
- Monitor for suspicious activity
- Set up alerts for failed logins
- Regular security audits

## Known Issues

1. **SMTP Password Storage**: Currently uses base64 encoding. Need proper encryption.
2. **No CSRF Protection**: Forms are vulnerable to CSRF attacks.
3. **No Rate Limiting**: Application is vulnerable to brute force attacks.
4. **Session Configuration**: Default PHP session settings should be hardened.
5. **No Email Validation**: Email addresses are not validated thoroughly.

## Reporting Security Issues

If you discover a security vulnerability, please email security@example.com instead of using the public issue tracker.
