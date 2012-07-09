(function() {
var MEAN = 0, STDDEV = 1, COUNT = 2;
var FOOTER_HEIGHT = 20, LEFT_EDGE = 70;

function formatMoney( money ) {
	function f( n ) { return Math.floor( n * 10 ) / 10; }
	if ( money <= 0 )
		return '';
	if ( money < 100 )
		return f( money ) + ' copper';
	if ( money < 10000 )
		return f( money / 100 ) + ' silver';
	if ( money < 10000000 )
		return f( money / 10000 ) + ' gold';
	return f( money / 10000000 ) + 'K gold';
}
window['formatMoney'] = formatMoney;

function drawSpline( canvas, ctx, data, visScale, scale, stddevFactor, startFromRight, moveFirst ) {
	var xStep = (canvas.width - LEFT_EDGE) / ( data.length - 1 );
	var x = LEFT_EDGE;
	if ( startFromRight ) {
		xStep = -xStep;
		x = canvas.width;
	}
	function d( n ) { return visScale * ( scale - data[n][MEAN] + data[n][STDDEV] * stddevFactor ); }
	var y = d( 0 );
	var prevX, prevY;

	if ( moveFirst )
		ctx.moveTo( x, y );
	else
		ctx.lineTo( x, y );
	for ( var i = 1; i < data.length - 1; i++ ) {
		prevX = x;
		prevY = y;
		
		x += xStep;
		y = d( i );
		
		var nextX = x + xStep;
		var nextY = d( i + 1 );
		
		var slope1 = ( prevY - y ) / ( prevX - x );
		var slope2 = ( y - nextY ) / ( x - nextX );
		
		ctx.bezierCurveTo( prevX + xStep / 4, prevY - xStep / 4 * slope1, x - xStep / 4, y + xStep / 4 * slope2, x, y );
	}
	ctx.bezierCurveTo( x + xStep / 4, y - xStep / 4 * slope2, nextX - xStep / 4, nextY, nextX, nextY );
}

function showGraph( canvas, data ) {
	var ctx = canvas.getContext( '2d' );
	var max = 0, count = 0;
	for ( var t in data ) {
		max = Math.max( data[t][MEAN] + data[t][STDDEV] * 2, max );
		count++;
	}
	var logMax = Math.ceil( Math.log( max ) );

	var scale = Math.pow( Math.E, logMax );
	var visScale = ( canvas.height - FOOTER_HEIGHT ) / scale;
	for ( var y = 20; y < canvas.height - FOOTER_HEIGHT; y += 50 ) {
		ctx.fillText( formatMoney( Math.round( ( canvas.height - y - FOOTER_HEIGHT ) / visScale ) ), 0, y );
	}

	ctx.beginPath();
	ctx.moveTo( LEFT_EDGE, 0 );
	ctx.lineTo( LEFT_EDGE, canvas.height - FOOTER_HEIGHT );
	ctx.moveTo( 0, canvas.height - FOOTER_HEIGHT );
	ctx.lineTo( canvas.width, canvas.height - FOOTER_HEIGHT );
	ctx.stroke();

	var values = [];
	var i = 0;
	for ( var t in data ) {
		ctx.fillText( t, LEFT_EDGE + ( 0.5 + i++ ) * ( canvas.width - LEFT_EDGE ) / count, canvas.height - FOOTER_HEIGHT / 2 );
		values.push( data[t] );
	}

	ctx.lineWidth = 3;
	ctx.beginPath();
	drawSpline( canvas, ctx, values, visScale, scale, 0, false, true );
	ctx.stroke();

	ctx.fillStyle = 'rgba( 100, 100, 100, 0.2 )';
	ctx.beginPath();
	drawSpline( canvas, ctx, values, visScale, scale, 1, false, true );
	drawSpline( canvas, ctx, values.reverse(), visScale, scale, -1, true, false );
	ctx.closePath();
	ctx.fill();
}
window['showGraph'] = showGraph;
})()