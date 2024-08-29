<?php
use App\Handlers\FileBrowser;

/** @var FileBrowser $browser */
/** @var string $pathPrefix */
$text = htmlspecialchars(...);
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <title><?= $text($browser->targetDir?->url ?: '') ?> index</title>
    <meta name="generator" content="oauth2-tier">
    <style>
    <?php require '_css.php'; ?>
    .t-files {
        margin-left: -2em
    }
    .td-link {
        position: relative;
        padding-left: 2em;
    }
    .hover-link {
        visibility: hidden;
    }
    td:hover .hover-link {
        visibility: visible;
    }
    .f-link {
        position: absolute;
        left: 0.5em;
        text-decoration: none;
    }
    </style>
</head>
<body class="h-100">

    <div class='d-flex' style='height: 100%'>
        <div class='d-flex flex-column' style='width: 35%; padding: 1em 1em 1em 2em;'>
        
            <h1>Index of 
              <?php if ($browser->targetDir): ?>
              <?php foreach ($browser->targetDir->getSegmentUrls() as $i => $segUrl): ?>
                <?= $i > 1 ? '/' : '' ?> <a href="<?= $pathPrefix . $segUrl->url ?>"><?= $segUrl->getUrlBasename() ?: '/' ?></a>
              <?php endforeach; ?>
              <?php endif; ?>
            </h1>
            <hr style='margin: 1px 0;'>
            
            <div class="mb-3" style="flex-grow: 1">
            <?php if ($browser->dirError): ?>
            <div class="error"><?= $text($browser->dirError->getMessage()) ?></div>
            <div class="error">Try to browse <a href="/">/</a></div>
            <?php else: ?>
              <table class="t-files" style="width: 100%">
                <?php $dirUrl = $browser->targetDir->url == '/' ? '' : $browser->targetDir->url ?>
            	<?php foreach ($browser->dirContent as /** @var SplFileInfo $file */ $file): ?>
                <tr>
                  <td class="td-link">
                    <?php if ($file->isFile()): ?>
                    <a class="hover-link f-link" href="<?= $pathPrefix . $dirUrl . '/' . $file->getFilename() ?>">🔗</a>
                    <?php endif; ?>
                  	<a href="<?= $pathPrefix . $dirUrl . '/' . ($file->getFilename() . ($file->isFile() ? '.' . $browser->virtualExtension : '')) ?>" class="file">
                  	<?= $file->isDir() ? '[' . $text($file->getFilename()) . ']' : $text($file->getFilename()) ?>
                  	</a>
                  </td>
                  <td>
                    <?= gmdate('Y-m-d H:i:s', $file->getMTime()) ?>
                  </td>
                  <td>
                    <?= $file->getSize() ?>
                  </td>
                </tr>
                <?php endforeach; ?>
          	  </table>
            <?php endif; ?>
            </div>
            <div class="mb-3">
              <a href="<?= $pathPrefix ?>/oauth2/sign_in" title="user info">👤</a>
            </div>
        </div>
        
        <div class='d-flex flex-column' style='width: 65%; padding: 1em'>
        	<?php if ($browser->targetFile): ?>
            <h1><?= $text($browser->targetFile->getBasename()) ?></h1>
            <hr style='margin: 1px 0 1em;'>
            <?php if ($browser->fileError): ?>
            <div class="error"><?= $text($browser->fileError->getMessage()) ?></div>
            <?php else: ?>
            <textarea style="width: 100%; flex-grow: 1" rows=30><?= $text($browser->fileContent) ?></textarea>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
