<?php
use Kelunik\OAuth\Provider;

/** @var Provider[] $providers */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'OAuth2 tier' ?></title>
    <style>
    <?php require '_css.php'; ?>
      main {
        display: flex;
        flex-direction: column;
        padding: 0 1em;
      }
    </style>
</head>
<body>
    <div class="box">
        <header>
            <h1><?= $title ?? 'OAuth2 tier' ?></h1>
        </header>
        <main>
        	<p>Login with these providers</p>
        	<?php foreach ($providers as $provider): ?>
            <button type="button" onclick="window.location.reload()" style="margin-bottom: 0em"><?= $provider->getName() ?></button>
            <?php endforeach; ?>
        </main>
    </div>
</body>
</html>
