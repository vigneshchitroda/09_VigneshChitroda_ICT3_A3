<?php
// task4_list_files/index.php - List files and directories in PHP
$rootDir = realpath(dirname(__DIR__)); // Root of php_assignment

// Determine current directory to list
$requestedDir = isset($_GET['dir']) ? $_GET['dir'] : '';
$targetDir = realpath($rootDir . '/' . $requestedDir);

// Security check: ensure target is within root directory
if ($targetDir === false || !str_starts_with($targetDir, $rootDir)) {
    $targetDir = $rootDir;
    $requestedDir = '';
}

// Function to format file size
function formatBytes($bytes, $precision = 2) {
    if ($bytes <= 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Function to get file icon
function getFileIcon($filename, $isDir) {
    if ($isDir) return '📁';
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'php': return '🐘';
        case 'js': return '📜';
        case 'css': return '🎨';
        case 'xml': return '📄';
        case 'json': return '📋';
        case 'png':
        case 'jpg':
        case 'jpeg':
        case 'gif':
        case 'webp': return '🖼️';
        case 'sql': return '🗄️';
        case 'md':
        case 'txt': return '📝';
        case 'html': return '🌐';
        default: return '📄';
    }
}

// Handle file preview
$previewFile = '';
$previewContent = '';
if (isset($_GET['preview'])) {
    $previewPath = realpath($rootDir . '/' . $_GET['preview']);
    if ($previewPath && str_starts_with($previewPath, $rootDir) && is_file($previewPath)) {
        $previewFile = basename($previewPath);
        $previewContent = file_get_contents($previewPath);
        // Truncate if too long
        if (strlen($previewContent) > 20000) {
            $previewContent = substr($previewContent, 0, 20000) . "\n... [Preview Truncated]";
        }
    }
}

// Scan directory
$rawItems = @scandir($targetDir) ?: [];
$folders = [];
$files = [];

foreach ($rawItems as $item) {
    if ($item === '.') continue;
    $fullPath = $targetDir . DIRECTORY_SEPARATOR . $item;
    $isDir = is_dir($fullPath);

    $relative = str_replace($rootDir, '', $fullPath);
    $relative = ltrim(str_replace('\\', '/', $relative), '/');

    $entry = [
        'name' => $item,
        'relative' => $relative,
        'is_dir' => $isDir,
        'size' => $isDir ? '-' : formatBytes(filesize($fullPath)),
        'mtime' => date('Y-m-d H:i:s', filemtime($fullPath)),
        'icon' => getFileIcon($item, $isDir)
    ];

    if ($item === '..') {
        if ($targetDir !== $rootDir) {
            $parentDir = dirname($targetDir);
            $parentRel = str_replace($rootDir, '', $parentDir);
            $parentRel = ltrim(str_replace('\\', '/', $parentRel), '/');
            $entry['relative'] = $parentRel;
            array_unshift($folders, $entry);
        }
    } elseif ($isDir) {
        $folders[] = $entry;
    } else {
        $files[] = $entry;
    }
}

$allItems = array_merge($folders, $files);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task 4: Directory File Lister - PHP Practical</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="../index.php">📁 Task 4: File Lister</a>
        <ul class="navbar-nav">
            <li><a class="nav-link" href="../index.php">🏠 Assignment Portal</a></li>
            <li><a class="nav-link" href="../task3_db_to_xml/index.php">&larr; Task 3</a></li>
            <li><a class="nav-link" href="../task5_php_call_express/index.php">Task 5 &rarr;</a></li>
        </ul>
    </div>
</nav>

<div class="container" style="padding-top: 30px;">
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--dark);">Task 4: List Files in a Directory</h1>
        <p style="color: var(--text-muted);">
            Interactive file system explorer in PHP demonstrating <code>scandir()</code>, <code>is_dir()</code>, <code>filesize()</code>, and <code>filemtime()</code>.
        </p>
    </div>

    <!-- Breadcrumb path bar -->
    <div class="card" style="margin-bottom: 20px; padding: 14px 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="font-family: monospace; font-size: 0.95rem;">
                📍 <strong>Path:</strong> 
                <a href="index.php" style="font-weight: 700;">[root]</a> / 
                <?= htmlspecialchars($requestedDir ?: '.') ?>
            </div>
            <div>
                <span class="badge badge-primary"><?= count($folders) ?> folders</span>
                <span class="badge badge-success"><?= count($files) ?> files</span>
            </div>
        </div>
    </div>

    <?php if ($previewFile): ?>
        <div class="card" style="margin-bottom: 24px; border-left: 4px solid var(--primary);">
            <div class="card-header">
                <h3 class="card-title">👁️ File Preview: <?= htmlspecialchars($previewFile) ?></h3>
                <a href="index.php?dir=<?= urlencode($requestedDir) ?>" class="btn btn-secondary btn-sm">Close Preview</a>
            </div>
            <pre style="background: #0f172a; color: #f8fafc; padding: 16px; border-radius: 6px; overflow-x: auto; max-height: 350px; font-size: 0.88rem; line-height: 1.5;"><?= htmlspecialchars($previewContent) ?></pre>
        </div>
    <?php endif; ?>

    <!-- Files Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;"></th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Last Modified</th>
                        <th style="width: 130px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allItems)): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 20px;">Directory is empty.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allItems as $item): ?>
                            <tr>
                                <td style="font-size: 1.3rem;"><?= $item['icon'] ?></td>
                                <td>
                                    <?php if ($item['is_dir']): ?>
                                        <a href="index.php?dir=<?= urlencode($item['relative']) ?>" style="font-weight: 700; color: var(--dark);">
                                            <?= htmlspecialchars($item['name']) ?>/
                                        </a>
                                    <?php else: ?>
                                        <span style="font-weight: 600; color: #334155;">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $item['is_dir'] ? 'badge-primary' : 'badge-secondary' ?>">
                                        <?= $item['is_dir'] ? 'Folder' : 'File' ?>
                                    </span>
                                </td>
                                <td><?= $item['size'] ?></td>
                                <td style="color: var(--text-muted); font-size: 0.88rem;"><?= $item['mtime'] ?></td>
                                <td>
                                    <?php if (!$item['is_dir']): ?>
                                        <a href="index.php?dir=<?= urlencode($requestedDir) ?>&preview=<?= urlencode($item['relative']) ?>" class="btn btn-secondary btn-sm">
                                            Preview
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?dir=<?= urlencode($item['relative']) ?>" class="btn btn-primary btn-sm">
                                            Open &rarr;
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer>
    <div class="container">
        <p>&copy; <?= date('Y') ?> PHP Practical Assignment - Task 4: Directory File Lister</p>
    </div>
</footer>
</body>
</html>
