<?php

function image_resize_sharpen(&$img, $amount = 80, $radius = 0.5, $threshold = 3)    { 

////////////////////////////////////////////////////////////////////////////////////////////////  
////  
////                  Unsharp Mask for PHP - version 2.1.1  
////  
////    Unsharp mask algorithm by Torstein Hønsi 2003-07.  
////             thoensi_at_netcom_dot_no.  
////               Please leave this notice.  
////  
///////////////////////////////////////////////////////////////////////////////////////////////  

    // $img is an image that is already created within php using 
    // imgcreatetruecolor. No url! $img must be a truecolor image. 

    // Attempt to calibrate the parameters to Photoshop: 
    if ($amount > 500)    $amount = 500; 
    $amount = $amount * 0.016; 
    if ($radius > 50)    $radius = 50; 
    $radius = $radius * 2; 
    if ($threshold > 255)    $threshold = 255; 
     
    $radius = abs(round($radius));     // Only integers make sense. 
    if ($radius == 0) { 
        return $img; imagedestroy($img); break;        } 
    $w = imagesx($img); $h = imagesy($img); 
    $imgCanvas = imagecreatetruecolor($w, $h); 
    $imgBlur = imagecreatetruecolor($w, $h); 
     

    // Gaussian blur matrix: 
    //                         
    //    1    2    1         
    //    2    4    2         
    //    1    2    1         
    //                         
    ////////////////////////////////////////////////// 
         

    if (function_exists('imageconvolution')) { // PHP >= 5.1  
            $matrix = array(  
            array( 1, 2, 1 ),  
            array( 2, 4, 2 ),  
            array( 1, 2, 1 )  
        );  
        imagecopy ($imgBlur, $img, 0, 0, 0, 0, $w, $h); 
        imageconvolution($imgBlur, $matrix, 16, 0);  
    }  
    else {  

    // Move copies of the image around one pixel at the time and merge them with weight 
    // according to the matrix. The same matrix is simply repeated for higher radii. 
        for ($i = 0; $i < $radius; $i++)    { 
            imagecopy ($imgBlur, $img, 0, 0, 1, 0, $w - 1, $h); // left 
            imagecopymerge ($imgBlur, $img, 1, 0, 0, 0, $w, $h, 50); // right 
            imagecopymerge ($imgBlur, $img, 0, 0, 0, 0, $w, $h, 50); // center 
            imagecopy ($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h); 

            imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 ); // up 
            imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25); // down 
        } 
    } 

    if($threshold>0){ 
        // Calculate the difference between the blurred pixels and the original 
        // and set the pixels 
        for ($x = 0; $x < $w-1; $x++)    { // each row
            for ($y = 0; $y < $h; $y++)    { // each pixel 
                     
                $rgbOrig = ImageColorAt($img, $x, $y); 
                $rOrig = (($rgbOrig >> 16) & 0xFF); 
                $gOrig = (($rgbOrig >> 8) & 0xFF); 
                $bOrig = ($rgbOrig & 0xFF); 
                 
                $rgbBlur = ImageColorAt($imgBlur, $x, $y); 
                 
                $rBlur = (($rgbBlur >> 16) & 0xFF); 
                $gBlur = (($rgbBlur >> 8) & 0xFF); 
                $bBlur = ($rgbBlur & 0xFF); 
                 
                // When the masked pixels differ less from the original 
                // than the threshold specifies, they are set to their original value. 
                $rNew = (abs($rOrig - $rBlur) >= $threshold)  
                    ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))  
                    : $rOrig; 
                $gNew = (abs($gOrig - $gBlur) >= $threshold)  
                    ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))  
                    : $gOrig; 
                $bNew = (abs($bOrig - $bBlur) >= $threshold)  
                    ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))  
                    : $bOrig; 
                 
                 
                             
                if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) { 
                        $pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew); 
                        ImageSetPixel($img, $x, $y, $pixCol); 
                    } 
            } 
        } 
    } 
    else{ 
        for ($x = 0; $x < $w; $x++)    { // each row 
            for ($y = 0; $y < $h; $y++)    { // each pixel 
                $rgbOrig = ImageColorAt($img, $x, $y); 
                $rOrig = (($rgbOrig >> 16) & 0xFF); 
                $gOrig = (($rgbOrig >> 8) & 0xFF); 
                $bOrig = ($rgbOrig & 0xFF); 
                 
                $rgbBlur = ImageColorAt($imgBlur, $x, $y); 
                 
                $rBlur = (($rgbBlur >> 16) & 0xFF); 
                $gBlur = (($rgbBlur >> 8) & 0xFF); 
                $bBlur = ($rgbBlur & 0xFF); 
                 
                $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig; 
                    if($rNew>255){$rNew=255;} 
                    elseif($rNew<0){$rNew=0;} 
                $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig; 
                    if($gNew>255){$gNew=255;} 
                    elseif($gNew<0){$gNew=0;} 
                $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig; 
                    if($bNew>255){$bNew=255;} 
                    elseif($bNew<0){$bNew=0;} 
                $rgbNew = ($rNew << 16) + ($gNew <<8) + $bNew; 
                    ImageSetPixel($img, $x, $y, $rgbNew); 
            } 
        } 
    } 
    imagedestroy($imgCanvas); 
    imagedestroy($imgBlur); 
    
}