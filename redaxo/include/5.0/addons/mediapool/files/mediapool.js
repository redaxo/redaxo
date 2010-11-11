/*
 * jQuery Images Ondemand v0.2
 *
 * This script load images on scroll event.
 * You only have to add 'img-ondemand' class and chage put src content on longdesc propertie on img tags
 * ej:
 *    original img    <img src='my_pic.jpg' />
 *    new img         <img class='img-ondemand' longdesc='my_pic.jpg' src='' />
 *
 * Copyright (c) 2009 Martin Borthiry : martin.borthiry@gmail.com 
 * Dual licensed under the MIT and GPL licenses.
 *
 * Date: 2009-09-21
 * Revision: 2
 */

(function($){  

    // global variables    
    var $w = $(window);
    var imgToLoad = 1;
    var className = 'img-ondemand';
    // offset of bottom to load images, on px
    var offset = 50;


    function imgOndemand(){
        if (imgToLoad){
             //calc current scroll position
            var scrollPos = $w.height() +$w.scrollTop();
            
            // get imgs not loaded
            $('img.'+className).each(function(){
                var $img = $(this);
                // filter imgs over scroll limit             
                if($img.offset().top < scrollPos+offset){
                    $img.attr('src',$img.attr('longdesc')).removeClass(className);
                }
            });
            // flag on bottom
            imgToLoad = $('img.'+className).length;     
               
        }  

    }

    // load on start all imgs over scroll limit   
    $(function(){
        imgOndemand();          
    });

    //bind scroll event (if you need you can add window resize event)
    $w.scroll(function(){ 
        imgOndemand();
    });
          

})(jQuery);