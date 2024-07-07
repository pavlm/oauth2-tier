<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'OAuth2 tier' ?></title>
    <style>
    <?php require '_css.php'; ?>
    </style>
</head>
<body>
    <div class="box">
        <header>
            <h1><?= $title ?? 'OAuth2 tier' ?></h1>
        </header>
        <main>
            <ul>
                <li>Try again later.</li>
                <li>Check if you visited the correct URL.</li>
                <li>Report this issue if you think this is a mistake.</li>
            </ul>
            <button type="button" onclick="window.location.reload()">Retry</button>
        </main>
    </div>
</body>
</html>
