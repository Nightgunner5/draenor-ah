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

function showGraph( canvas, data ) {
	var MEAN = 0, STDDEV = 1, COUNT = 2;
	var FOOTER_HEIGHT = 20, LEFT_EDGE = 70;
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

	ctx.lineWidth = 3;
	ctx.beginPath();
	var xStep = (canvas.width - LEFT_EDGE) / ( count - 1 );
	var x = LEFT_EDGE;
	for ( var t in data ) {
		if ( x == LEFT_EDGE )
			ctx.moveTo( x, visScale * ( scale - data[t][MEAN] ) );
		else
			ctx.lineTo( x, visScale * ( scale - data[t][MEAN] ) );
		x += xStep;
	}
	ctx.stroke();

	ctx.lineWidth = 1;
	ctx.beginPath();
	for ( var sign = -1; sign <= 1; sign += 2 ) {
	        x = LEFT_EDGE;
        	for ( var t in data ) {
        	        if ( x == LEFT_EDGE )
        	                ctx.moveTo( x, visScale * ( scale - data[t][MEAN] + sign * data[t][STDDEV] ) );
        	        else
        	                ctx.lineTo( x, visScale * ( scale - data[t][MEAN] + sign * data[t][STDDEV] ) );
        	        x += xStep;
        	}
	}
        ctx.stroke();
}
window['showGraph'] = showGraph;

