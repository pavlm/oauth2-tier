<?php
use Kelunik\OAuth\Provider;
use App\OAuth\IdentityData;
use Amp\Http\Server\Request;

/** @var Provider[] $providers */
/** @var IdentityData $user */
/** @var Request $request */
/** @var string $pathPrefix */
?>
<!DOCTYPE html>
<html lang="en" class="d-flex">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'OAuth2 tier (proxy)' ?></title>
    <style>
    <?php require '_css.php'; ?>
      form.start {
        flex-direction: column;
      }
      
      form.logout {
        align-items: center;
        justify-content: space-between;
      }
    </style>
</head>
<body>
	<div style="max-width: 400px;">
        <div class="box">
            <header>
                <h1><?= $title ?? 'OAuth2 tier (proxy)' ?></h1>
            </header>
            <form action="<?= $pathPrefix ?>/oauth2/start" method="post" class="d-flex start">
            	<p>Login with these providers</p>
            	<?php foreach ($providers as $provider): ?>
                <button type="submit" name="provider" value="<?= $provider->getInternalName() ?>" class="mb-3"><?= $provider->getName() ?></button>
                <?php endforeach; ?>
                <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($request->getQueryParameter('redirect_url') ?? '/') ?>" >
            </form>
        </div>
        <?php if ($user): ?>
        <div class="mb-3"></div>
        <div class="box">
            <h1>User info</h1>
            <form action="<?= $pathPrefix ?>/oauth2/sign_out" method="post" class="d-flex logout">
            	<div class="mr-3">
            	    👤 <?= $user->getName() ?: $user->getEmail() ?: $user->getId() ?>
            	</div>
            	<button type="submit">logout</button>
            </form>
        </div>
        <div class="mb-3"></div>
        <div class="box" style="background: none; box-shadow: none; text-align: center;">
        	service root: <a href="<?= $pathPrefix ?: '/' ?>"><?= $pathPrefix ?: '/' ?></a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
