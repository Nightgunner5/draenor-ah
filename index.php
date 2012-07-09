<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Draenor Auction House</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Le styles -->
<link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="style.min.css" rel="stylesheet">
<link href="/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">

<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js">
</script>
<![endif]-->
</head>
<body>
<div class="container-fluid">
<?php

require_once 'init.php';

?>
<div class="hero-unit">
<div class="row-fluid center">
<?php foreach ( array( 'alliance', 'neutral', 'horde' ) as $faction ) { ?>

<div class="span4">
<h1><?php echo number_format( $db->{'auctions_' . $faction}->count() ); ?></h1>
<p><?php echo ucfirst( $faction ); ?> auctions indexed</p>
</div>

<?php } ?>
</div>
<br>
<pre id="ticker"></pre>
<br>
<h1 class="center"><?php echo time() % 86400 < 7200 ? 'Yesterday' : 'Today'; ?>&rsquo;s most listed items</h1>
<div class="row-fluid">
<?php foreach ( array( 'alliance', 'neutral', 'horde' ) as $faction ) { ?>

<div class="span4">
<ul class="top-auctions">
<?php

$faction_to_badge = array(
	'alliance' => 'info',
	'neutral' => 'success',
	'horde' => 'important'
);

$i = 0;
$today = strtotime( 'today', time() - 7200 );

foreach ( $db->{'daily_' . $faction}->find( array( 'value.date' => new MongoDate( $today ) ) )->sort( array( 'value.count' => -1 ) )->limit( 10 ) as $most_listed ) {
	$item = get_item( $most_listed['value']['item'] );
	$count = $most_listed['value']['count'];
	switch ( $i++ ) {
		case 0:
			$class = ' class="first"';
			$image_size = 56;
			break;
		case 1:
			$class = ' class="second"';
			$image_size = 36;
			break;
		default:
			$class = '';
			$image_size = 18;
	}
	echo '<li', $class, '>', PHP_EOL,
		'<a href="http://eu.battle.net/wow/en/item/', $item['id'], '">',
		'<span class="badge badge-', $faction_to_badge[$faction], '">', number_format( $count ), '</span>', PHP_EOL,
		'<img class="thumbnail" width="', $image_size, '" height="', $image_size, '" src="/wow/icons/', $image_size, '/', $item['icon'], '.jpg">', PHP_EOL,
		$item['name'], '</a>', PHP_EOL,
	     '</li>', PHP_EOL;
}

?>
</ul>
</div>

<?php } ?>
</div>
</div>

</div>

<!-- Le javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script async src="/bootstrap/js/bootstrap.min.js"></script>
<script async src="ticker.min.js"></script>

<script async>
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-2731135-17']);
_gaq.push(['_trackPageview']);

(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
</body>
</html>
