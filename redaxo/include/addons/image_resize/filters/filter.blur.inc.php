<?php

// Übernommen von cerdmann.com
// Unsharp mask algorithm by Torstein Hønsi 2003 (thoensi_at_netcom_dot_no)
// Christoph Erdmann: changed it a little, cause i could not reproduce the darker blurred image, now it is up to 15% faster with same results

function image_resize_blur(&$img, $amount = 80, $radius = 8, $threshold = 3)
{
	
////////////////////////////////////////////////////////////////////////////////////////////////  
////  
////                  Unsharp Mask for PHP - version 2.1.1  
////  
////    Unsharp mask algorithm by Torstein Hønsi 2003-07.  
////             thoensi_at_netcom_dot_no.  
////               Please leave this notice.  
////  
///////////////////////////////////////////////////////////////////////////////////////////////  
	
	// Attempt to calibrate the parameters to Photoshop:
	if ($amount > 500) $amount = 500;
	$amount = $amount * 0.016;
	if ($radius > 50) $radius = 50;
	$radius = $radius * 2;
	if ($threshold > 255) $threshold = 255;
	$radius = abs(round($radius)); // Only integers make sense.
	if ($radius == 0)
	{
    return $img;
	}

	$w = imagesx($img);
	$h = imagesy($img);
	$imgCanvas = $img;
	$imgCanvas2 = $img;
	$imgBlur = imagecreatetruecolor($w, $h);

	// Gaussian blur matrix:
	//  1 2 1
	//  2 4 2
	//  1 2 1

  // Move copies of the image around one pixel at the time and merge them with weight
  // according to the matrix. The same matrix is simply repeated for higher radii.
  for ($i = 0; $i < $radius; $i++)
  {
    imagecopy($imgBlur, $imgCanvas, 0, 0, 1, 1, $w -1, $h -1); // up left
    imagecopymerge($imgBlur, $imgCanvas, 1, 1, 0, 0, $w, $h, 50); // down right
    imagecopymerge($imgBlur, $imgCanvas, 0, 1, 1, 0, $w -1, $h, 33.33333); // down left
    imagecopymerge($imgBlur, $imgCanvas, 1, 0, 0, 1, $w, $h -1, 25); // up right
    imagecopymerge($imgBlur, $imgCanvas, 0, 0, 1, 0, $w -1, $h, 33.33333); // left
    imagecopymerge($imgBlur, $imgCanvas, 1, 0, 0, 0, $w, $h, 25); // right
    imagecopymerge($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h -1, 20); // up
    imagecopymerge($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 16.666667); // down
    imagecopymerge($imgBlur, $imgCanvas, 0, 0, 0, 0, $w, $h, 50); // center
  }
  $imgCanvas = $imgBlur;

  // Calculate the difference between the blurred pixels and the original
  // and set the pixels
  for ($x = 0; $x < $w; $x++)
  { // each row
    for ($y = 0; $y < $h; $y++)
    { // each pixel
      $rgbOrig = ImageColorAt($imgCanvas2, $x, $y);
      $rOrig = (($rgbOrig >> 16) & 0xFF);
      $gOrig = (($rgbOrig >> 8) & 0xFF);
      $bOrig = ($rgbOrig & 0xFF);
      $rgbBlur = ImageColorAt($imgCanvas, $x, $y);
      $rBlur = (($rgbBlur >> 16) & 0xFF);
      $gBlur = (($rgbBlur >> 8) & 0xFF);
      $bBlur = ($rgbBlur & 0xFF);

      // When the masked pixels differ less from the original
      // than the threshold specifies, they are set to their original value.
      $rNew = (abs($rOrig - $rBlur) >= $threshold) ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig)) : $rOrig;
      $gNew = (abs($gOrig - $gBlur) >= $threshold) ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig)) : $gOrig;
      $bNew = (abs($bOrig - $bBlur) >= $threshold) ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig)) : $bOrig;

      if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew))
      {
        $pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);
        ImageSetPixel($img, $x, $y, $pixCol);
      }
    }
  }
  $img = $imgBlur;
}