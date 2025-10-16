<?php
session_start();

if (isset($_SESSION["email"])) {
    $email = $_SESSION["email"];
} else {
    echo "<script>window.location.href='../';</script>";
    exit;
}

include("../connections.php");

// Fetch user info (reuse your existing approach)
$query_info = mysqli_query($connections, "SELECT * FROM tbl_user WHERE email='" . mysqli_real_escape_string($connections, $email) . "'");
$my_info = mysqli_fetch_assoc($query_info);
$account_type = $my_info["account_type"] ?? '';
$img = $my_info["img"] ?? '';

include("nav.php");

// Server-side configuration
const MAX_FILES = 5;                // Max files per upload request
const MAX_BYTES_PER_FILE = 5 * 1024 * 1024; // 5 MB per file
$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
];

// Prepare data structures for messages
$errors = [];
$successes = [];

// Ensure base upload directory exists
$baseDir = __DIR__ . '/post_photos';
if (!is_dir($baseDir)) {
    @mkdir($baseDir, 0755, true);
}

// Use a stable per-user folder derived from email (hash avoids special chars)
$userKey = sha1(strtolower(trim($email)));
$userDir = $baseDir . '/' . $userKey;
if (!is_dir($userDir)) {
    @mkdir($userDir, 0755, true);
}

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnUploadPhotos'])) {
    if (!isset($_FILES['post_photos'])) {
        $errors[] = "No files received.";
    } else {
        // Normalize files array and filter out empty slots
        $names = $_FILES['post_photos']['name'];
        $types = $_FILES['post_photos']['type'];
        $tmpns = $_FILES['post_photos']['tmp_name'];
        $errs  = $_FILES['post_photos']['error'];
        $sizes = $_FILES['post_photos']['size'];

        $fileCount = 0;
        foreach ($names as $n) {
            if ($n !== null && $n !== '') $fileCount++;
        }

        if ($fileCount === 0) {
            $errors[] = "Please select up to 5 images to upload.";
        } elseif ($fileCount > MAX_FILES) {
            $errors[] = "You can upload a maximum of " . MAX_FILES . " images at once.";
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            for ($i = 0, $len = count($names); $i < $len; $i++) {
                if (empty($names[$i])) {
                    continue; // skip blank inputs
                }

                $origName = $names[$i];
                $tmpPath  = $tmpns[$i];
                $errCode  = $errs[$i];
                $size     = $sizes[$i];

                if ($errCode !== UPLOAD_ERR_OK) {
                    $errors[] = htmlspecialchars($origName) . ": Upload error code $errCode.";
                    continue;
                }

                if (!is_uploaded_file($tmpPath)) {
                    $errors[] = htmlspecialchars($origName) . ": Invalid upload source.";
                    continue;
                }

                if ($size > MAX_BYTES_PER_FILE) {
                    $errors[] = htmlspecialchars($origName) . ": File too large (max " . (int)(MAX_BYTES_PER_FILE / (1024*1024)) . " MB).";
                    continue;
                }

                $mime = finfo_file($finfo, $tmpPath);
                if (!isset($allowedMimes[$mime])) {
                    $errors[] = htmlspecialchars($origName) . ": Invalid file type ($mime). Only JPG, PNG, GIF are allowed.";
                    continue;
                }

                $ext = $allowedMimes[$mime];
                $unique = date('Ymd_His') . '_' . bin2hex(random_bytes(4));
                $safeFilename = $unique . '.' . $ext;

                $targetPath = $userDir . '/' . $safeFilename;
                $relativePath = 'post_photos/' . $userKey . '/' . $safeFilename; // store this in DB for use in <img src>

                if (!@move_uploaded_file($tmpPath, $targetPath)) {
                    $errors[] = htmlspecialchars($origName) . ": Failed to move uploaded file.";
                    continue;
                }

                // Insert record into tbl_user_photos
                $stmt = mysqli_prepare($connections, "INSERT INTO tbl_user_photos (email, path, created_at) VALUES (?, ?, NOW())");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'ss', $email, $relativePath);
                    if (mysqli_stmt_execute($stmt)) {
                        $successes[] = htmlspecialchars($origName) . " uploaded.";
                    } else {
                        $errors[] = htmlspecialchars($origName) . ": Saved file but failed to record in database.";
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $errors[] = htmlspecialchars($origName) . ": Saved file but failed to prepare DB insert.";
                }
            }
            finfo_close($finfo);
        }
    }
}

// Fetch existing photos for this user
$photos = [];
$res = mysqli_query($connections, "SELECT id, path, created_at FROM tbl_user_photos WHERE email='" . mysqli_real_escape_string($connections, $email) . "' ORDER BY created_at DESC, id DESC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $photos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Photos</title>
    <script src="../Admin/js/jQuery.js"></script>
    <style>
        .container { max-width: 900px; margin: 40px auto; padding: 0 16px; }
        .messages { margin-bottom: 16px; }
        .messages .ok { color: #1a7f37; }
        .messages .err { color: #d1242f; }
        .preview-list { display: flex; flex-wrap: wrap; gap: 10px; margin: 10px 0; }
        .preview-item { width: 150px; height: 150px; overflow: hidden; border: 1px solid #ddd; border-radius: 6px; position: relative; background: #fafafa; }
        .preview-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; margin-top: 24px; }
        .grid .card { border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: #fff; }
        .grid .card img { width: 100%; height: 160px; object-fit: cover; display: block; }
        .grid .card .meta { padding: 8px; font-size: 12px; color: #555; }
        .hint { color: #555; font-size: 14px; margin-top: 6px; }
        .btn { background: #2563eb; color: #fff; border: 0; padding: 10px 16px; border-radius: 6px; cursor: pointer; }
        .btn:disabled { background: #9ca3af; cursor: not-allowed; }
        .file-input { margin: 10px 0; }
        h2 { margin: 0; }
        .section { margin-bottom: 32px; }
    </style>
    <script>
        // Preview up to 5 images
        function handleFileSelect(input) {
            const preview = document.getElementById('preview');
            preview.innerHTML = '';
            const files = input.files;
            if (!files || !files.length) return;

            const maxFiles = 5;
            if (files.length > maxFiles) {
                alert("You can select a maximum of " + maxFiles + " images.");
            }

            const toShow = Math.min(files.length, maxFiles);
            for (let i = 0; i < toShow; i++) {
                const file = files[i];
                if (!file.type.startsWith('image/')) continue;

                const url = URL.createObjectURL(file);
                const div = document.createElement('div');
                div.className = 'preview-item';
                const img = document.createElement('img');
                img.src = url;
                img.onload = () => URL.revokeObjectURL(url);
                div.appendChild(img);
                preview.appendChild(div);
            }
        }
    </script>
</head>
<body>
<div class="container">
    <div class="section">
        <h2>Post Photos</h2>
        <p class="hint">Upload up to 5 images at once. Allowed types: JPG, PNG, GIF. Max 5 MB each.</p>
        <div class="messages">
            <?php if (!empty($successes)): ?>
                <div class="ok">
                    <?php foreach ($successes as $m): ?>
                        <div><?php echo $m; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="err">
                    <?php foreach ($errors as $e): ?>
                        <div><?php echo $e; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input
                class="file-input"
                type="file"
                name="post_photos[]"
                id="post_photos"
                accept="image/jpeg,image/png,image/gif"
                multiple
                onchange="handleFileSelect(this)"
            />
            <div id="preview" class="preview-list"></div>
            <button type="submit" name="btnUploadPhotos" class="btn">Upload Photos</button>
        </form>
    </div>

    <div class="section">
        <h3>Your Uploaded Photos</h3>
        <?php if (empty($photos)): ?>
            <p class="hint">You haven’t uploaded any photos yet.</p>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($photos as $p): ?>
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($p['path']); ?>" alt="Photo">
                        <div class="meta">
                            Uploaded: <?php echo htmlspecialchars($p['created_at']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>