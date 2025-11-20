# Login Page Troubleshooting

## Quick Diagnostics

1. **First, run the diagnostic page:**
   - Go to: `http://localhost/trusted-discovery-platform/login-diagnostics.php`
   - This will show you exactly what's wrong

2. **Check these items in order:**

   ✓ **Database Connection OK?**
   - If NO: Start WAMP, make sure MySQL is running
   
   ✓ **User Table Exists?**
   - If NO: Create it using the SQL provided on diagnostic page
   
   ✓ **Users in Database?**
   - If NO: Go to register.html and create a user first
   
   ✓ **Test Login Works?**
   - If NO: Use the test form on diagnostic page to see exact error

## Common Issues & Solutions

### Issue 1: "Database connection error"
**Problem:** MySQL is not running
**Solution:**
1. Open WAMP Control Panel
2. Click to start MySQL (should turn green)
3. Refresh the page

### Issue 2: "User Table Not Found"
**Problem:** Table wasn't created
**Solution:**
1. Go to http://localhost/phpmyadmin
2. Select the `project` database
3. Go to SQL tab
4. Copy the SQL from diagnostic page
5. Paste and click Go

### Issue 3: "Phone number not registered"
**Problem:** User doesn't exist in database
**Solution:**
1. Go to register.html
2. Create a new user account
3. Remember the phone & password
4. Then login with those credentials

### Issue 4: "Invalid password"
**Problem:** Password doesn't match
**Solution:**
1. Double-check you're typing the password correctly
2. Check Caps Lock
3. Create a new account to be sure of credentials

### Issue 5: Page shows blank or error
**Problem:** Something else is wrong
**Solution:**
1. Open browser developer tools (F12)
2. Go to Console tab
3. Look for error messages
4. Share the console error with diagnostic info

## Step-by-Step Flow

```
1. Make sure WAMP is running (Apache + MySQL green)
   ↓
2. Check login-diagnostics.php
   ↓
3. If all checks pass, go to register.html
   ↓
4. Create a test account (remember phone & password!)
   ↓
5. Go to login.html
   ↓
6. Enter phone & password
   ↓
7. Click Sign In
   ↓
8. Should redirect to index.html with profile visible
```

## Console Debugging

If login.html shows an error, open browser console (F12) and look for messages like:
- "Sending login request with phone: +919876543210"
- "Response status: 200"
- "Login response: {success: true, ...}"

If you see JSON parse errors, the server isn't returning valid JSON.

## Files to Check

| File | Purpose |
|------|---------|
| login.html | Frontend form |
| login.php | Backend authentication |
| config.php | Database connection |
| check_login.php | Session verification |
| logout.php | Logout handler |

## PHP Errors

Check WAMP error logs:
- Apache errors: `C:\wamp64\logs\apache_error.log`
- PHP errors: `C:\wamp64\logs\php_error.log`

Open these files to see detailed error messages.

## Still Not Working?

1. Run diagnostic page: `http://localhost/trusted-discovery-platform/login-diagnostics.php`
2. Take screenshot of all status boxes
3. Check WAMP logs for errors
4. Verify database using phpMyAdmin
5. Try creating a new database account and testing login
