<?php

/**
 * CAPTCHA X
 * 
 * This is simple class for CAPTCHA (Completely Automated Public Turing to tell 
 * Computers from Humans Apart)
 * 
 * It's small, compact and scalable. Tuning of the parameters is done completely
 * via the captcha_x.ini file, which contains also the deeper documentation on 
 * their meaning.
 * 
 * 
 * INSTALLATION
 * 
 * Place the files anywhere you wish, preferrably in separate folder in some 
 * directory from where you include scripts. ( This folder would be referred as
 * $APP_ROOT.)
 * 
 * Set up the fonts (captcha_x.ini). You can use system fonts ( default) or local 
 * fonts. If so, you should create the $APP_ROOT/fonts folder and place the fonts
 * there. Edit the 'fonts' key accordingly and use only the names without the ttf
 * extension. For more info discuss the PHP manual on imagettftext() function.
 * 
 * If you use some kind of protection of the include folder ( eg. .htaccess), you 
 * should move the server.php somewhere from where you normally link your images
 * and set up properly the inclusion of the captcha_x class.
 * 
 * Tune the parameters of the CAPTCHA image according to your needs.
 * 
 * 
 * IMPORTANT NOTE
 * 
 * The image server MUST be in the same domain as the target of the web-form
 * ( eg. the fully qualified domain names of their url MUST be identical). 
 * In other case the session based validation will fail.
 * 
 * 
 * THIS SOFTWARE IS SPELLWARE
 * 
 * Its author wrote it in the hope, that COMPUTERS OF THOSE FUCKING BASTARDS
 * SPAMMING GROUPS WITH THEIR BULLSHIT WILL CRASH, BURN AND BLOW UP !!!      
 * 
 *  
 * @package CAPTCHA X
 * @license GPL   
 */
class captcha_x {
    
    /**
     * Parsed captcha_x.ini
     */         
    var $INI;
    
    /**
     * Resulting image
     */         
    var $image;
    
    /**
     * Array of letters    
     */    
    var $letters;

    /**
     * @access public
     * @return void     
     * @constructor         
     */    
    function captcha_x () {
        $this->INI = parse_ini_file ( dirname ( __FILE__) . '/captcha_x.ini', true);
    }
    
/* ========== PUBLIC METHODS ========== */    
    
    /**
     * Generates the image and puts md5 hash into the session
     * 
     * @access public       
     * @return void            
     */    
    function handle_request () {
        extract ( $this->INI);
        
        $this->letters = $this->_get_random_letters ();
        $this->_put_md5_into_session ();
        
        $this->image = $this->_create_img_base ();
        $this->_add_dust_and_scratches ( $bg_color_2);
        $this->_print_letters ();
        $this->_add_dust_and_scratches ( $bg_color_1);
        
        header("Content-type: image/jpeg");
        imagejpeg ( $this->image);
        imagedestroy ( $this->image);
    }
    
    /**
     * Compares the user's input with the hash stored in the session
     * 
     * @param string $user_string     
     * @access public
     * @return boolean                   
     */    
    function validate ( $user_string) {
        extract ( $this->INI);

        if(!isset($_SESSION)) session_start ();
        
        if ( ! $case_sensitive) {
            $user_string = strtolower ( $user_string);
        }
        
        $md5 = md5 ( $user_string);
        if ( isset($_SESSION['captcha']) && $md5 == $_SESSION['captcha']) {
            return true;
        }
    }
    
/* ========== PRIVATE METHODS =========== */
    
    /**
     * Generate some noise
     * 
     * @param string $color -- rgb color representation, see captcha_x.ini
     * @access private
     * @return void                        
     */    
    function _add_dust_and_scratches ( $color) {
        extract ( $this->INI);
        
        $max_x = $width - 1;
        $max_y = $height - 1;
        
        $color = $this->_split ( $color);
        
        $color = imagecolorallocate ( $this->image, $color[0], $color[1], $color[2]);
        
        for ( $i = 0; $i < $noise; ++$i) {
            
            if ( rand ( 1, 100) > $dust_vs_scratches) {
                imageline ( $this->image, rand ( 0, $max_x), rand ( 0, $max_y), rand ( 0, $max_x), rand ( 0, $max_y), $color);
            } else {
                imagesetpixel ( $this->image, rand ( 0, $max_x), rand ( 0, $max_y), $color);
            }
        }
    }
    
    /**
     * Sets the new image and its background
     * 
     * @access private
     * $return resource                   
     */    
    function _create_img_base () {
        extract ( $this->INI);
        
        $bg_color = $this->_split ( $bg_color_1);
        // phpinfo();
        $img = imagecreate ( $width, $height);
        imagecolorallocate ( $img, $bg_color[0], $bg_color[1], $bg_color[2]);
        
        return $img;
    }
    
    /**
     * Gets some letters from the set defined in captcha_x.ini
     * 
     * @access private
     * @return array                   
     */    
    function _get_random_letters () {
        extract ( $this->INI);
        
        $letters = $this->_split ( $letters);
        $letters_max = (count ( $letters) - 1);
        
        for ( $i = 0; $i < $letters_no; ++$i) {
            $letter_index = rand ( 0, $letters_max);
            $rtn_val[] = $letters[$letter_index];
        }
        
        return $rtn_val;
    }
    
    /**
     * Generates the text in the image
     * 
     * This function takes many parameters from the captcha_x.ini file, which
     * define the fonts, their sizes, letter angle and letterr colors                
     *         
     * @access private
     * @return void         
     */    
    function _print_letters () {
        extract ( $this->INI);
        
        // whether use the local fonts or the system fonts
        if ( $use_local_fonts) {
            $font_path = realpath ( dirname ( __FILE__) . '/fonts');
            if ( @putenv ( 'GDFONTPATH=' . $font_path) === false) {
                $no_putenv = true;
            }
        }

        list ( $padding_top, $padding_right, $padding_bottom, $padding_left) = $this->_split ( $padding);
        $box_width       = ( $width - ( $padding_left + $padding_right)) / $letters_no;
        $box_height      = $height - ( $padding_top + $padding_bottom); 
        
        $font_size       = $this->_split ( $font_size);
        $font_size_count = ( count ( $font_size) - 1);
        
        $fonts           = $this->_split ( $fonts);
        $fonts_count     = ( count ( $fonts) - 1);
        
        // f****** safe-mode settings
        if (isset($no_putenv) && $no_putenv) {
            foreach ( $fonts as $k => $v) {
                $a[$k] = "$font_path/$v.ttf";
            }
            $fonts = $a;
            unset ( $a);
        }
        
        // sem pridat podporu pro #xxx a #xxxxxx resolve_color()
        $fg_colors_count = ( count ( $fg_colors) - 1);
        foreach ( $fg_colors as $fg_color) {
            $a[] = $this->_split ( $fg_color);
        }
        $fg_colors = $a;
        unset ( $a);
         
        for ( $i = 0; $i < $letters_no; ++$i) {
            $size_index     = rand ( 0, $font_size_count);
            $size           = $font_size[$size_index];
            
            $angle          = (( rand ( 0, ( $letter_precession * 2)) - $letter_precession) + 360) % 360;
            
            $x              = $padding_left + ( $box_width * $i);
            $y              = $padding_top + $size + ( ( $box_height - $size) / 2);
            
            $color_index    = ( rand ( 0, $fg_colors_count));
            $color          = $fg_colors[$color_index];
            $color          = imagecolorallocate ( $this->image, $color[0], $color[1], $color[2]);
            
            $font_index     = rand ( 0, $fonts_count);
            $font           = $fonts[$font_index];
            
            imagettftext ( $this->image, $size, $angle, $x, $y, $color, $font, $this->letters[$i]);
        }
    }

    /**
     * Puts the md5 hash into session
     * 
     * @access private
     * @return void                   
     */    
    function _put_md5_into_session () {
        extract ( $this->INI);
        if(!isset($_SESSION))
          session_start();
            
        $string              = implode ( '', $this->letters);
        
        if ( ! $case_sensitive) {
            $string = strtolower ( $string);
        }
        
        $md5                 = md5 ( $string);
        $_SESSION['captcha'] = $md5;
    }
    
    /**
     * Splits string into an array by comma and spaces
     * 
     * @access private
     * @param string $s to be split          
     * @return array 
     */
    function _split ( $s) {
        $a = @preg_split ( '/\s?,\s?/', $s, -1, PREG_SPLIT_NO_EMPTY);
        if ( is_array ( $a)) {
            return $a;
        }
    }     
}
?>
