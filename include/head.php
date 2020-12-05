<?php
$pt[] = 'Blake/An Illustrated Quarterly';
if($pt_post != '') {
	$pt[] = $pt_post;
}
?>
	<head>
                <title><?php echo implode(' | ', $pt); ?></title>
                <meta http-equiv="content-type" content="text/html;charset=utf-8" />
                <link rel="shortcut icon" href="<?php echo $root; ?>img/general/favicon.ico" type="image/x-icon">
				<link rel="icon" href="<?php echo $root; ?>img/general/favicon.ico" type="image/x-icon">
				<link href="https://fonts.googleapis.com/css?family=UnifrakturMaguntia" rel="stylesheet">
                <?php echoCSS('style.css'); ?>
                <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js" type="text/javascript"></script>
                <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.min.js" type="text/javascript"></script>
                <script src="<?php echo $root; ?>js/expand.js"></script>
                <script src="<?php echo $root; ?>js/bq.js"></script>
                <link rel="stylesheet" media="screen" href="<?php echo $root; ?>js/fancybox/jquery.fancybox-1.3.4.css"></link>
                <script type="text/javascript" src="<?php echo $root; ?>js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
                <?php
                if(basename($_SERVER['PHP_SELF']) == 'hdoc.php') {
					echo '<style>'.$styles.'</style>';
					echo $scripts;
                }
                ?>
				<?php include_once($root."include/analyticstracking.php") ?>
    </head>
