<?php
use App\Handlers\FileBrowser;

/** @var FileBrowser $browser */
$text = htmlspecialchars(...);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>browser</title>
    <style>
    <?php require '_css.php'; ?>
    </style>
</head>
<body>

    <div class='d-flex' style='height: 100%'>
        <div class='d-flex flex-column' style='width: 35%; padding: 1em;'>
            <h1>Index of <?= $browser->targetDir ?></h1>
            <hr style='margin: 1px 0;'>
            <?php if ($browser->dirError): ?>
            <div class="error"><?= $text($browser->dirError->getMessage()) ?></div>
            <?php else: ?>
            <table style="width: 100%;">
            	<?php foreach ($browser->dirContent as /** @var SplFileInfo $file */ $file): ?>
                <tr>
                  <td>
                  	<a href="${fileHref(f)}" class="file"><?= $file->isDir() ? $text($file->getFilename()) : $text($file->getFilename()) ?></a>
                  </td>
                  <td>
                  <?= get_class($file) ?>
                  </td>
                  <td>
                  </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
        </div>
        <div class='d-flex flex-column' style='width: 65%; padding: 1em'>
        	<?php if ($browser->targetFile): ?>
            <h1><?= $text($browser->targetFile) ?></h1>
            <hr style='margin: 1px 0 1em;'>
            <?php if ($browser->fileError): ?>
            <div class="error"><?= $text($browser->fileError->getMessage()) ?></div>
            <?php else: ?>
            <textarea style="width: 100%; flex-grow: 1" rows=30>...</textarea>
            <?php endif; ?>
            <?php endif; ?>
        </div>
<!--         <div class='alert' ${!data.error ? 'style="display: none;"' : ''}>${data.error ? escapeHtml(data.error) : ''}</div>-->
    </div>
    
    <pre>
    <?= json_encode($browser, JSON_PRETTY_PRINT); ?>
    </pre>

</body>
</html>
