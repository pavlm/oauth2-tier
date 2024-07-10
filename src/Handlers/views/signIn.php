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
      form {
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
        <form action="/oauth2/start" method="post">
        	<p>Login with these providers</p>
        	<?php foreach ($providers as $provider): ?>
            <button type="submit" style="margin-bottom: 0em" name="provider" value="<?= $provider->getInternalName() ?>"><?= $provider->getName() ?></button>
            <?php endforeach; ?>
        </form>
    </div>
</body>
</html>
