<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="pp-description" content="<?php echo htmlspecialchars($metadata['description']); ?>">
    <title><?php echo htmlspecialchars($metadata['title']); ?></title>
    <link rel="icon" href="<?php echo $baseUrl; ?>favicon.ico" type="image/x-icon">
    <link href="<?php echo $baseUrl; ?>css/styles.css" rel="stylesheet">
    <link href="<?php echo $baseUrl; ?>css/index.css" rel="stylesheet">
    <script src="<?php echo $baseUrl; ?>js/index.js"></script>
    <style>
        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <?php echo $content; ?>
</body>

</html>