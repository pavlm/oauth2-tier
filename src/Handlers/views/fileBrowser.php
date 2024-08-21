<?php
use App\Handlers\FileBrowser;

/** @var FileBrowser $browser */
$text = htmlspecialchars(...);
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <title>browser</title>
    <style>
    <?php require '_css.php'; ?>
    </style>
</head>
<body class="h-100">

    <div class='d-flex' style='height: 100%'>
        <div class='d-flex flex-column' style='width: 35%; padding: 1em;'>
        
            <h1>Index of 
              <?php if ($browser->targetDir): ?>
              <?php foreach ($browser->targetDir->getSegmentUrls() as $i => $segUrl): ?>
                <?= $i > 1 ? '/' : '' ?> <a href="<?= $segUrl->url ?>"><?= $segUrl->getUrlBasename() ?: '/' ?></a>
              <?php endforeach; ?>
              <?php endif; ?>
            </h1>
            <hr style='margin: 1px 0;'>
            
            <?php if ($browser->dirError): ?>
            <div class="error"><?= $text($browser->dirError->getMessage()) ?></div>
            <?php else: ?>
              <table style="width: 100%;">
                <?php $dirUrl = $browser->targetDir->url == '/' ? '' : $browser->targetDir->url ?>
            	<?php foreach ($browser->dirContent as /** @var SplFileInfo $file */ $file): ?>
                <tr>
                  <td>
                  	<a href="<?= $dirUrl . '/' . $file->getFilename() ?>" class="file"><?= $file->isDir() ? '[' . $text($file->getFilename()) . ']' : $text($file->getFilename()) ?></a>
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
