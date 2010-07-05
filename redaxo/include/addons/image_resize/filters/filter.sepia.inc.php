<?php

function image_resize_sepia(&$src_im, $quality = 60 )
{
	$src_x = ceil( imagesx( $src_im ) );
	$src_y = ceil( imagesy( $src_im ) );
	$dst_x = $src_x;
	$dst_y = $src_y;
	$dst_im = ImageCreateTrueColor( $dst_x, $dst_y );

	// Benötigt PHP > 4.3.2
	if(function_exists('imageantialias'))
	imageantialias( $dst_im, TRUE );

	ImageCopyResampled( $dst_im, $src_im, 0, 0, 0, 0, $dst_x, $dst_y, $src_x, $src_y );
	// Change style of image pixelwise
	for( $y = 0; $y < $dst_y; $y++ )
	{
		for( $x = 0; $x < $dst_x; $x++ )
		{
			$col = ImageColorAt( $dst_im, $x, $y );
			$r = ($col & 0xFF0000) >> 16;
			$g = ($col & 0x00FF00) >> 8;
			$b = $col & 0x0000FF;
			$grey = (min($r,$g,$b) + max($r,$g,$b)) / 2;
			// Boost  colors
			$boost = 1.2;
			$boostborder = 250;
			for( $i = 0; $i < 25; $i++ )
			{
				if( $grey > $boostborder )
				{
					$grey = $grey * $boost;
					break;
				}
				$boost -= .01;
				$boostborder -= 10;
			}
			// Set sepia palette
			$r = $grey * 1.01;
			$g = $grey * 0.98;
			$b = $grey * 0.90;
			// Correct max values
			$r = min($r, 255);
			$g = min($g, 255);
			$b = min($b, 255);
			$col = ImageColorAllocate( $dst_im, $r, $g, $b );
			ImageSetPixel( $dst_im, $x, $y, $col );
		}
	}
	$src_im = $dst_im;
}