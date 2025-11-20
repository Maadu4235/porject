# Login & Profile System Setup Guide

## What Was Updated

Your application now has a complete login system with profile sidebar that shows after login:

### 1. **Login Flow**
- User fills login form with phone number and password
- `login.php` checks the database
- If credentials match → redirects to `index.html`
- Session is created with user data

### 2. **Profile Display**
- When logged in, navbar shows profile dropdown instead of Login/Register buttons
- User can see their name in the navbar
- Profile sidebar shows detailed user information:
  - User's full name
  - Phone number
  - User type (Job Seeker / Service Provider)
  - Links to edit profile, settings, and logout

### 3. **Files Modified**

| File | Changes |
|------|---------|
| `index.html` | Added profile sidebar, navbar user dropdown, login check |
| `login.php` | Added phone number to response |
| `check_login.php` | Added phone number to session check |
| `logout.php` | Already working properly |

## How to Test

### Step 1: Register a Test Account
1. Go to: `http://localhost/trusted-discovery-platform/register.html`
2. Fill in all fields:
   - First Name: John
   - Last Name: Doe
   - Phone: +919876543210
   - Password: test123456
   - User Type: Job Seeker
3. Click Register

### Step 2: Login with Test Account
1. Go to: `http://localhost/trusted-discovery-platform/login.html`
2. Enter phone: +919876543210
3. Enter password: test123456
4. Click Sign In
5. You should be redirected to home page with profile sidebar

### Step 3: Verify Profile Sidebar
- Look for profile dropdown in navbar (top-right)
- Click on your name to see dropdown menu
- You should see: My Profile, Settings, Logout options
- Profile information shows: Name, Phone, User Type

### Step 4: Test Logout
- Click logout button
- You'll be logged out and redirected to home
- Navbar should show Login/Register buttons again

## Testing Tool

Use this page to test all components:
`http://localhost/trusted-discovery-platform/test-login.php`

This page allows you to:
- Test database connection
- Test login with any credentials
- Check current session status

## Session Information Stored

When a user logs in, the following is stored in PHP session:
- `user_id` - Database ID
- `user_name` - First + Last Name
- `user_phone` - Phone number
- `user_type` - seeker or provider

LocalStorage also stores for frontend use:
- `dwar_user_logged_in` - true/false
- `dwar_user_name` - User's full name
- `dwar_user_type` - User type
- `dwar_user_phone` - Phone number

## Profile Sidebar Features

The profile sidebar includes:
- User avatar icon
- User name and type badge
- Phone number display
- Edit Profile button
- Settings button
- Logout button

It appears on the right side of the page and slides in with animation.

## Troubleshooting

### Issue: Login says "Phone number not registered"
- Make sure you registered first
- Check the phone number matches exactly
- Verify database has the user table

### Issue: After login, no profile appears
- Check browser console for errors (F12)
- Verify `check_login.php` is working
- Check if session is being created

### Issue: Profile dropdown doesn't show
- Make sure JavaScript is enabled
- Wait for page to load completely
- Try refreshing the page

## Next Steps

To enhance the profile system further, you can:
1. Add profile picture upload
2. Add edit profile functionality
3. Add user ratings and reviews
4. Add notification system
5. Add preferences/settings page
