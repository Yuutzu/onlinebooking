# COMPLETE REFERENCE - FILE UPLOAD SECURITY IMPLEMENTATION

## FILES THAT NEED UPDATING

### Status Overview

```
✅ DONE (3 files):
   - client/register.php
   - admin/walkin_book.php
   - admin/add_product.php

⏳ TODO (10+ files):
   - profile_edit.php, profile.php, room_book.php
   - room_add.php, room_update.php, services_add.php, services_update.php
   - update_product.php, update_site_settings.php, update_client.php, update_admin.php
```

---

## HOW TO APPLY FIX TO ANY FILE

### 3-Step Process (< 2 minutes per file)

#### STEP 1: Add Include Statement

Find the opening `<?php` block and add:

```php
<?php
include('./config/config.php');
require_once('./inc/FileUploadHandler.php');  // ← ADD THIS
```

Path variations:

- Same level: `./inc/FileUploadHandler.php`
- Parent dir: `../admin/inc/FileUploadHandler.php`
- Adjust based on file location

#### STEP 2: Find `move_uploaded_file()` Call

Search for this pattern in the file:

```php
move_uploaded_file($_FILES["fieldname"]["tmp_name"], $target_file);
```

Or this variation:

```php
if (move_uploaded_file($_FILES["field"]["tmp_name"], "path/" . $name)) {
    // database insert
}
```

#### STEP 3: Replace with Handler

Replace the move_uploaded_file code with:

**Simple version (single file):**

```php
$uploadHandler = new FileUploadHandler('dist/img/');
$result = $uploadHandler->upload($_FILES['fieldname']);

if (!$result['success']) {
    echo json_encode(['success' => false, 'message' => $result['message']]);
    exit;
}

$secure_filename = $result['filename'];  // Use this for database
```

**For form submission:**

```php
$uploadHandler = new FileUploadHandler('dist/img/');
$result = $uploadHandler->upload($_FILES['fieldname']);

if (!$result['success']) {
    echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href='form.php';</script>";
    exit;
}

$secure_filename = $result['filename'];
```

---

## EXACT FILE LOCATIONS & LINE NUMBERS

### CLIENT SIDE FILES

#### File 1: [client/profile_edit.php](../../client/profile_edit.php)

**Lines to replace:** ~23, ~33

```
Line 23: move_uploaded_file($_FILES["client_picture"]["tmp_name"], $target_file);
Line 33: move_uploaded_file($_FILES["client_id_picture"]["tmp_name"], $target_file);
```

**Pattern:** Fixed path variable + filename variable
**Approach:** Create handler once, use twice

#### File 2: [client/profile.php](../../client/profile.php)

**Line to replace:** ~27

```
Line 27: move_uploaded_file($_FILES["client_picture"]["tmp_name"], $target_file);
```

**Pattern:** Similar to profile_edit
**Approach:** Single file upload

#### File 3: [client/room_book.php](../../client/room_book.php)

**Line to replace:** ~26

```
Line 26: move_uploaded_file($_FILES["gcash_screenshot"]["tmp_name"], $target_file);
```

**Pattern:** Payment proof image
**Approach:** Single file upload

---

### ADMIN SIDE FILES

#### File 4: [admin/room_add.php](../../admin/room_add.php)

**Line to replace:** ~19

```
Line 19: move_uploaded_file($_FILES["room_picture"]["tmp_name"], "dist/img/" . $_FILES["room_picture"]["name"]);
```

**Pattern:** Direct upload without path variable
**Approach:** Replace with handler

#### File 5: [admin/room_update.php](../../admin/room_update.php)

**Line to replace:** ~20

```
Line 20: move_uploaded_file($_FILES["room_picture"]["tmp_name"], "dist/img/" . $_FILES["room_picture"]["name"]);
```

**Pattern:** Same as room_add (update vs add)
**Approach:** Single file, optional

#### File 6: [admin/services_add.php](../../admin/services_add.php)

**Line to replace:** ~18

```
Line 18: move_uploaded_file($_FILES["service_pic"]["tmp_name"], "dist/img/" . $_FILES["service_pic"]["name"]);
```

**Pattern:** Service image upload
**Approach:** Single file upload

#### File 7: [admin/services_update.php](../../admin/services_update.php)

**Line to replace:** ~19

```
Line 19: move_uploaded_file($_FILES["service_pic"]["tmp_name"], "dist/img/" . $_FILES["service_pic"]["name"]);
```

**Pattern:** Service image update
**Approach:** Optional file upload

#### File 8: [admin/update_product.php](../../admin/update_product.php)

**Line to replace:** ~21

```
Line 21: if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
```

**Pattern:** Already has some validation, wrapped in if
**Approach:** Replace if condition with handler

#### File 9: [admin/update_site_settings.php](../../admin/update_site_settings.php)

**Line to replace:** ~30

```
Line 30: if (move_uploaded_file($file['tmp_name'], $targetPath)) {
```

**Pattern:** Multiple files (carousel1, 2, 3 + about_image1, 2, 3)
**Approach:** Loop through all files with handler

#### File 10: [admin/update_client.php](../../admin/update_client.php)

**Line to replace:** ~15

```
Line 15: move_uploaded_file($_FILES['client_picture']['tmp_name'], "./dist/img/" . $client_picture);
```

**Pattern:** Profile picture update
**Approach:** Single optional file

#### File 11: [admin/update_admin.php](../../admin/update_admin.php)

**Lines to replace:** ~16, ~23

```
Line 16: move_uploaded_file($_FILES['client_picture']['tmp_name'], "./dist/img/" . $client_picture);
Line 23: move_uploaded_file($_FILES['client_id_picture']['tmp_name'], "./dist/img/" . $client_id_picture);
```

**Pattern:** Two files (picture + ID)
**Approach:** Create handler once, use twice

---

## COPY-PASTE READY CODE BLOCKS

### Block A: For Single Required File

```php
<?php
include('./config/config.php');
require_once('./inc/FileUploadHandler.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... other code ...

    // Upload handling
    if (!isset($_FILES['fieldname']) || $_FILES['fieldname']['error'] === UPLOAD_ERR_NO_FILE) {
        echo "<script>alert('File is required.'); window.location.href='this_page.php';</script>";
        exit;
    }

    $uploadHandler = new FileUploadHandler('dist/img/');
    $result = $uploadHandler->upload($_FILES['fieldname']);

    if (!$result['success']) {
        echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href='this_page.php';</script>";
        exit;
    }

    $filename = $result['filename'];

    // Now use $filename in your database insert
    // ... database code ...
}
```

### Block B: For Single Optional File

```php
<?php
include('./config/config.php');
require_once('./inc/FileUploadHandler.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... other code ...
    $filename = null;  // Default to null

    // Upload handling (optional)
    if (isset($_FILES['fieldname']) && $_FILES['fieldname']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadHandler = new FileUploadHandler('dist/img/');
        $result = $uploadHandler->upload($_FILES['fieldname']);

        if (!$result['success']) {
            echo json_encode(['success' => false, 'message' => $result['message']]);
            exit;
        }

        $filename = $result['filename'];
    }

    // Now use $filename in your database (handle null if not uploaded)
    // ... database code ...
}
```

### Block C: For Multiple Files

```php
<?php
include('./config/config.php');
require_once('./inc/FileUploadHandler.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... other code ...
    $uploadHandler = new FileUploadHandler('dist/img/');

    $file1 = null;
    $file2 = null;

    // File 1
    if (isset($_FILES['file1']) && $_FILES['file1']['error'] !== UPLOAD_ERR_NO_FILE) {
        $result = $uploadHandler->upload($_FILES['file1']);
        if (!$result['success']) {
            echo json_encode(['success' => false, 'message' => $result['message']]);
            exit;
        }
        $file1 = $result['filename'];
    }

    // File 2
    if (isset($_FILES['file2']) && $_FILES['file2']['error'] !== UPLOAD_ERR_NO_FILE) {
        $result = $uploadHandler->upload($_FILES['file2']);
        if (!$result['success']) {
            echo json_encode(['success' => false, 'message' => $result['message']]);
            exit;
        }
        $file2 = $result['filename'];
    }

    // Now use $file1 and $file2 in database
    // ... database code ...
}
```

### Block D: For JSON Response (AJAX)

```php
<?php
require('../config.php');
require_once('../inc/FileUploadHandler.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_FILES['fieldname'])) {
        echo json_encode(['success' => false, 'message' => 'No file provided']);
        exit;
    }

    $uploadHandler = new FileUploadHandler('dist/img/');
    $result = $uploadHandler->upload($_FILES['fieldname']);

    if (!$result['success']) {
        echo json_encode(['success' => false, 'message' => $result['message']]);
        exit;
    }

    $filename = $result['filename'];

    // ... database code ...

    echo json_encode(['success' => true, 'filename' => $filename]);
}
```

---

## TESTING EACH FILE

After updating each file, test with:

### Test 1: Valid Upload

1. Open the form
2. Select a real JPG/PNG file (< 2MB)
3. Submit form
4. **Expected:** File uploaded successfully, entry shows in database

### Test 2: Invalid Extension

1. Create a text file: `test.txt`
2. Rename to: `test.jpg` (but keep text content)
3. Try to upload
4. **Expected:** Error message: "File content does not match allowed file types"

### Test 3: Oversized File

1. Create/find a large image (> 2MB)
2. Try to upload
3. **Expected:** Error message: "File size must not exceed 2.00MB"

### Test 4: No File Selected

1. Leave file field empty
2. Submit form
3. **Expected:** Error message: "File is required" or form behavior depends on if optional

### Test 5: Executable File

1. Create a simple PHP file: `<?php echo "test"; ?>`
2. Name it: `shell.php`
3. Try to upload (don't rename)
4. **Expected:** Error message: "File extension not allowed" or "File content does not match"

---

## SUMMARY TABLE

| File                     | Pattern         | Complexity | Est. Time | Status  |
| ------------------------ | --------------- | ---------- | --------- | ------- |
| register.php             | Single required | Low        | 2 min     | ✅ DONE |
| walkin_book.php          | Dual optional   | Medium     | 3 min     | ✅ DONE |
| add_product.php          | Single required | Low        | 2 min     | ✅ DONE |
| profile_edit.php         | Dual optional   | Medium     | 3 min     | ⏳ TODO |
| profile.php              | Single optional | Low        | 2 min     | ⏳ TODO |
| room_book.php            | Single required | Low        | 2 min     | ⏳ TODO |
| room_add.php             | Single required | Low        | 2 min     | ⏳ TODO |
| room_update.php          | Single optional | Low        | 2 min     | ⏳ TODO |
| services_add.php         | Single required | Low        | 2 min     | ⏳ TODO |
| services_update.php      | Single optional | Low        | 2 min     | ⏳ TODO |
| update_product.php       | Single optional | Low        | 2 min     | ⏳ TODO |
| update_site_settings.php | Multiple        | High       | 5 min     | ⏳ TODO |
| update_client.php        | Single optional | Low        | 2 min     | ⏳ TODO |
| update_admin.php         | Dual optional   | Medium     | 3 min     | ⏳ TODO |

**Total time to complete all: ~40 minutes**

---

## VERIFICATION CHECKLIST

After completing all files:

- [ ] No `move_uploaded_file()` calls without handler
- [ ] All files include FileUploadHandler.php
- [ ] All test uploads work with valid files
- [ ] All test uploads rejected with invalid files
- [ ] Error messages display properly
- [ ] Database stores secure filenames
- [ ] Files display correctly when retrieved
- [ ] No broken functionality

---

## DEPLOYMENT STEPS

1. **Test locally** - Update all 10+ files and test thoroughly
2. **Backup database** - In case you need to rollback
3. **Deploy code** - Update all PHP files to production
4. **Verify upload dirs** - Check dist/img/ has .htaccess
5. **Test production** - Run same tests on live site
6. **Monitor** - Check logs for upload errors

---

## TROUBLESHOOTING

### Issue: "FileUploadHandler not found"

**Fix:** Check include path is correct

```php
// Adjust based on current file location
require_once('../admin/inc/FileUploadHandler.php');  // From client/ go up then into admin
require_once('./inc/FileUploadHandler.php');         // From admin/ same level
```

### Issue: Uploads still fail

**Fix:** Verify permissions

```bash
# SSH to server and check
ls -la upload_directory/
# Should show: drwxr-xr-x (755)
```

### Issue: Some files can't upload but others can

**Fix:** Check database field size

```sql
SELECT column_name, column_type FROM information_schema.columns
WHERE table_name = 'your_table' AND column_name LIKE '%image%';

-- If VARCHAR(100) or less, need to increase:
ALTER TABLE table_name MODIFY column_name VARCHAR(150);
```

---

## SECURITY VALIDATION

Once all files are updated:

```
✅ MIME type validation: Prevents executable uploads
✅ Extension whitelist: Allows only safe images
✅ Size limits: Prevents DoS attacks
✅ Secure filenames: Protects privacy
✅ Script blocking: .htaccess prevents execution
✅ Permission control: 755/644 prevents tampering
✅ Path traversal prevention: basename() strips paths

Result: 100% safer file upload system!
```
