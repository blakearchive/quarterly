<!DOCTYPE html>
<html>
	<?php
	require('include/functions.php');
	
	$pt = array();
	$pt_post = '';
		
	require('include/head.php');
	?>
	<body>
        <div id="outer" class="hdoc-page">
			<?php
			require('include/header.php');
			?>
			<div id="all-content">
				<div id="all-content-inner">
					<?php
					include('include/notfound.php');
					include('include/footer.php');
					?>
				</div> <!-- #all-content-inner -->
			</div> <!-- #all-content -->
		</div> <!-- #outer -->
	</body>
</html>
