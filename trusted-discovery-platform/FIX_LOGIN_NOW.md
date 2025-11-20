# Login Not Working - Step by Step Fix

## STEP 1: Check Database is Running
1. Open WAMP Control Panel
2. Verify **Apache** and **MySQL** are BOTH running (green lights)
3. If not green, click them to start

## STEP 2: View Your Database Users
Go to: **http://localhost/trusted-discovery-platform/show-users.php**

This shows:
- All users in database
- Their phone numbers
- Their names

**Write down one of the phone numbers!**

## STEP 3: Test Login Works  
Go to: **http://localhost/trusted-discovery-platform/quick-login-test.php**

1. Enter a phone number from Step 2
2. Enter the password you used when registering
3. Click "Test Login"
4. If it works → Success message appears

**Important:** If this test works, then your login system is fine!

## STEP 4: Use login.html
If Step 3 worked:
1. Go to: **http://localhost/trusted-discovery-platform/login.html**
2. Enter same phone & password
3. Click "Sign In"
4. Should redirect to home page with profile visible

## STEP 5: Open Browser Console (F12)
If login still fails:
1. Open **login.html**
2. Press **F12** to open Developer Tools
3. Click **Console** tab
4. Try to login
5. Look for messages like:
   - "=== LOGIN ATTEMPT ===" (good, form was submitted)
   - "LOGIN SUCCESS!" (great, working!)
   - "LOGIN FAILED: Invalid password" (password wrong)
   - "=== NETWORK ERROR ===" (server problem)

## STEP 6: Check Debug Info
Go to: **http://localhost/trusted-discovery-platform/debug-db.php**

This shows:
- User table structure
- All users
- Password hash length

## Common Issues & Fixes

| Issue | Solution |
|-------|----------|
| "Please fill in all fields" | This should NOT happen - there's a server issue |
| "Phone number not registered" | Use a phone from show-users.php that exists |
| "Invalid password" | Make sure you're using correct password |
| Nothing happens when clicking login | Check F12 Console for errors |
| Page shows blank | Check server status in WAMP |
| Redirects but no profile | Check F12 Console, localStorage might not be updating |

## Files to Check if Still Broken

1. **show-users.php** - Shows all registered users
2. **quick-login-test.php** - Test login mechanism
3. **debug-db.php** - Database structure
4. **login-diagnostics.php** - Full diagnostics
5. **F12 Console** - JavaScript errors

## What Should Happen

### Successful Flow:
```
1. Fill form with phone & password
2. Console shows: "=== LOGIN ATTEMPT ==="
3. Console shows: "Response status: 200"
4. Console shows: "LOGIN SUCCESS!"
5. Redirects to index.html
6. Profile appears in navbar
```

### Failed Flow:
```
1. Fill form with wrong credentials
2. Console shows: "LOGIN FAILED: Invalid password"
3. Error message appears on page
4. Button re-enables for retry
```

## If Nothing Works

1. **Clear browser cache** (Ctrl+Shift+Delete)
2. **Restart WAMP** server
3. **Restart browser**
4. Go to **show-users.php** and verify users exist
5. Open **F12 Console** and check for all errors
6. Share the console output

## Testing Without Browser

You can test directly using `quick-login-test.php`:
- Shows all registered users
- Has test form
- Tests password verification
- Shows exact responses from server

**Start here if confused!**
