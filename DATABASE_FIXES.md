# Database Query Fixes for landing.php

## Overview
Updated all database queries in `landing.php` to handle missing tables, missing columns, and schema mismatches gracefully. No errors will be thrown if expected tables don't exist or if columns are missing.

## Key Changes

### 1. Featured Gallery Query
**File:** `landing.php` (lines ~160-180)

**Before:**
- Directly executed prepared statement without checking for errors
- No fallback if query failed

**After:**
- Tries to fetch with all responsive columns (image_small, image_medium, image_large, webp_path, effect_strength)
- If that fails, falls back to simple query without those columns
- All result fields have default values to prevent undefined index errors
- Prepared statement is properly closed

**Tables expected:**
- `gallery` (required)
  - Columns: id, image_path, title, description
  - Optional: image_small, image_medium, image_large, webp_path, effect_strength, display_order

---

### 2. Gallery Preview Query
**File:** `landing.php` (lines ~240-260)

**Before:**
- Direct execution without error checking
- Could fail silently if schema didn't match

**After:**
- Same fallback logic as Featured Gallery
- Gracefully handles missing responsive image columns
- Returns normalized array with all expected fields

**Tables expected:**
- `gallery` (required)

---

### 3. Quotes Query
**File:** `landing.php` (lines ~280-300)

**Before:**
- No check if `execute()` succeeded
- Didn't handle missing quote data

**After:**
- Checks `$rq->execute()` return value before fetching
- All fields have default values
- Statement is properly closed
- Fallback quote displayed if table is empty

**Tables expected:**
- `quotes` (required)
  - Columns: id, quote, author

---

### 4. Individual Quote Lookup
**File:** `landing.php` (lines ~380-410)

**Before:**
- Didn't check execute() return value
- Could overwrite quote text/author with undefined values

**After:**
- Validates `execute()` returns true
- Uses null-coalescing to preserve original values if query fails
- Properly closes statement

---

### 5. Marquee Items (Notifications/Events/News)
**File:** `landing.php` (lines ~570-630)

**Status:** Already handles errors gracefully
- Uses `@$conn->query()` to suppress warnings
- Checks `num_rows > 0` before fetching
- Has fallback "No items available" message
- Properly handles multiple table name variations (notifications vs notification)

**Tables queried:**
- `notifications` or `notification` (try notifications first, fall back to notification)
  - Columns: id, title, content/message, created_at
- `events`
  - Columns: id, title, description, event_date
- `blog_posts`
  - Columns: id, title, content, created_at

---

### 6. Staff Sections (Founders, Principals, Staff)
**File:** `landing.php` (lines ~830-880)

**Before:**
- Directly added raw query results
- Could crash if fields were missing from result

**After:**
- Normalizes each row with default values
- All arrays have consistent structure
- Missing photo defaults to 'assets/images/default.jpg'
- Missing text fields default to empty strings or 'Unknown'

**Tables expected:**
- `staff` (required)
  - Columns: name, designation, photo, qualification, bio

---

## Testing Recommendations

1. **Test with missing tables:**
   ```sql
   DROP TABLE IF EXISTS gallery;
   ```
   Expected: No error, empty sections with fallback content

2. **Test with incomplete schema:**
   ```sql
   ALTER TABLE gallery DROP COLUMN image_small;
   ```
   Expected: Gallery loads with simple query fallback

3. **Test with empty data:**
   - Delete all rows from tables
   - Expected: Fallback content displays (e.g., default quotes for empty quotes table)

4. **Browser test:**
   - Open landing.php in browser
   - Check console for errors (should be none)
   - Verify all sections display (even if empty)

## Database Schema Assumptions

For best results, ensure your database has these tables and columns:

```sql
-- Gallery table
CREATE TABLE gallery (
    id INT PRIMARY KEY AUTO_INCREMENT,
    image_path VARCHAR(255),
    image_small VARCHAR(255),
    image_medium VARCHAR(255),
    image_large VARCHAR(255),
    webp_path VARCHAR(255),
    title VARCHAR(255),
    description TEXT,
    effect_strength INT DEFAULT 0,
    display_order INT
);

-- Quotes table
CREATE TABLE quotes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quote TEXT,
    author VARCHAR(255)
);

-- Notifications (or notification)
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    content TEXT,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    description TEXT,
    event_date DATE
);

-- Blog posts
CREATE TABLE blog_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Staff
CREATE TABLE staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    designation VARCHAR(255),
    photo VARCHAR(255),
    qualification VARCHAR(255),
    bio TEXT
);
```

## Notes

- All database error handling uses `@` operator to suppress warnings
- Fallbacks ensure page always renders even with DB issues
- Consider adding error logging for failed queries to debug production issues
- Run `scripts/migrate_gallery_schema.php` to ensure gallery table has all expected columns
