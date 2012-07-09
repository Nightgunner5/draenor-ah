<?php require_once 'init.php'; $itemID = 21877; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Graph for item <?php $item = get_item( $itemID ); echo $item['name']; ?></title>
<script src="graph.min.js"></script>
</head>
<body>
<canvas id="graph001" width="800" height="600"></canvas>
<script>
showGraph( document.querySelector( '#graph001' ), {
<?php

foreach ( $db->daily_horde->find( array( 'value.item' => $itemID ) ) as $daily ) {
	$timestamp = gmdate( 'j M', $daily['value']['date']->sec );
	$count = $daily['value']['count'];
	$mean  = $daily['value']['average'];
	$stddev = $daily['value']['stddev'];
	echo "\t\"", $timestamp, '": [', $mean, ', ', $stddev, ', ', $count, "],\n";
}

?>
} );
</script>
</body>
</html>
