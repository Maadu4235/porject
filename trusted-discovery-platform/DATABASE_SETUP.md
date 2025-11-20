## Job Notification Table
To store notifications for users when new jobs matching their criteria are available, create this table:

```sql
CREATE TABLE job_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    job_id VARCHAR(100),
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    notified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id)
);
```

**Steps:**
1. Go to `http://localhost/phpmyadmin`
2. Select the `project` database
3. Click **SQL** tab
4. Paste the SQL code above
5. Click **Go**
# Database Connection Troubleshooting Guide

## Quick Fixes

### 1. **Verify WAMP is Running**
- Open WAMP Control Panel (look for the icon in system tray)
- Make sure **Apache** and **MySQL** are both running (green lights)
- If not running, click to start them

### 2. **Test Database Connection**
1. Start WAMP server
2. Open browser and navigate to: `http://localhost/trusted-discovery-platform/test-db-connection.php`
3. This will show you exactly what's working and what's not

### 3. **Create Database (if not exists)**
1. Open **phpMyAdmin** (usually at `http://localhost/phpmyadmin`)
2. Login with username: `root` (no password)
3. Click **Databases** tab
4. Enter database name: `project`
5. Click **Create**

### 4. **Create the User Table**
If the test shows "user table NOT FOUND", run this SQL in phpMyAdmin:

```sql
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type VARCHAR(50) DEFAULT 'seeker',
    email VARCHAR(255) DEFAULT NULL,
        email_confirmed TINYINT(1) DEFAULT 0,
            email_otp VARCHAR(10) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Steps:**
1. Go to `http://localhost/phpmyadmin`
2. Select the `project` database (left sidebar)
3. Click **SQL** tab at top
4. Paste the SQL code above
5. Click **Go**

### 5. **Test Registration**
1. Go to `http://localhost/trusted-discovery-platform/register.html`
2. Fill in the form and submit
3. Check the error message - it will now show specific details if something fails

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| "Database connection error" | Start MySQL in WAMP Control Panel |
| "Table 'project.user' doesn't exist" | Run the CREATE TABLE SQL above |
| "Duplicate entry for phone" | Phone number already registered - use different number |
| Network error displayed | Make sure WAMP is running on localhost |

## Debug Log Location

## Job Search Logging Table
To log job searches, create a new table:

```sql
CREATE TABLE job_search_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    location VARCHAR(100),
    keyword VARCHAR(100),
    qualification VARCHAR(100),
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id)
);
```

**Steps:**
1. Go to `http://localhost/phpmyadmin`
2. Select the `project` database
3. Click **SQL** tab
4. Paste the SQL code above
5. Click **Go**

## Additional Configuration
Edit `config.php` if your setup is different:
- **Host**: `localhost` (usually correct for WAMP)
- **Database**: `project` (can be changed if you use different name)
- **Username**: `root` (default MySQL user)
- **Password**: `` (empty by default)

## Still Having Issues?
1. Run the diagnostic at: `http://localhost/trusted-discovery-platform/test-db-connection.php`
2. Share the output to get more specific help
3. Check WAMP error logs for detailed error messages
