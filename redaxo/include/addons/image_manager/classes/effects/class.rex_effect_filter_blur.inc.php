<?php

// Übernommen von cerdmann.com
// Unsharp mask algorithm by Torstein Hønsi 2003 (thoensi_at_netcom_dot_no)
// Christoph Erdmann: changed it a little, cause i could not reproduce the darker blurred image, now it is up to 15% faster with same results

////////////////////////////////////////////////////////////////////////////////////////////////  
////  
////                  Unsharp Mask for PHP - version 2.1.1  
////  
////    Unsharp mask algorithm by Torstein Hønsi 2003-07.  
////             thoensi_at_netcom_dot_no.  
////               Please leave this notice.  
////  
///////////////////////////////////////////////////////////////////////////////////////////////  


class rex_effect_filter_blur extends rex_effect_abstract{

	function execute()
	{
		// Attempt to calibrate the parameters to Photoshop:
		if ($this->params["amount"] > 500) 
			$this->params["amount"] = 500;
		$this->params["amount"] = $this->params["amount"] * 0.016;
		if ($this->params["radius"] > 50) 
			$this->params["radius"] = 50;
		$this->params["radius"] = $this->params["radius"] * 2;
		if ($this->params["threshold"] > 255) $this->params["threshold"] = 255;
		$this->params["radius"] = abs(round($this->params["radius"])); // Only integers make sense.
		if ($this->params["radius"] == 0)
		{
	    return;
		}
	
		$gdimage =& $this->image->getImage();
		$w = $this->image->getWidth();
		$h = $this->image->getHeight();
		
		$imgCanvas = $gdimage;
		$imgCanvas2 = $gdimage;
		$imgBlur = imagecreatetruecolor($w, $h);
	
		// Gaussian blur matrix:
		//  1 2 1
		//  2 4 2
		//  1 2 1
	
	  // Move copies of the image around one pixel at the time and merge them with weight
	  // according to the matrix. The same matrix is simply repeated for higher radii.
	  for ($i = 0; $i < $this->params["radius"]; $i++)
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
	      $rNew = (abs($rOrig - $rBlur) >= $this->params["threshold"]) ? max(0, min(255, ($this->params["amount"] * ($rOrig - $rBlur)) + $rOrig)) : $rOrig;
	      $gNew = (abs($gOrig - $gBlur) >= $this->params["threshold"]) ? max(0, min(255, ($this->params["amount"] * ($gOrig - $gBlur)) + $gOrig)) : $gOrig;
	      $bNew = (abs($bOrig - $bBlur) >= $this->params["threshold"]) ? max(0, min(255, ($this->params["amount"] * ($bOrig - $bBlur)) + $bOrig)) : $bOrig;
	
	      if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew))
	      {
	        $pixCol = ImageColorAllocate($gdimage, $rNew, $gNew, $bNew);
	        ImageSetPixel($gdimage, $x, $y, $pixCol);
	      }
	    }
	  }
	  $gdimage = $imgBlur;
	}
	
	
	function getParams()
	{
		global $REX,$I18N;

		return array(
			array(
				'label' => $I18N->msg('imanager_effect_blur_amount'),
				'name' => 'amount',
				'type'	=> 'int',
				'default' => '80'
			),
			array(
				'label' => $I18N->msg('imanager_effect_blur_radius'),
				'name' => 'radius',
				'type'	=> 'int',
				'default' => '8'
			),
			array(
				'label' => $I18N->msg('imanager_effect_blur_threshold'),
				'name' => 'threshold',
				'type'	=> 'int',
				'default' => '3'
			)
		);
		
	}

}