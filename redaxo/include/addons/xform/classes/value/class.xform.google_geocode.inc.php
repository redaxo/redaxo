<?php

class rex_xform_google_geocode extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{	
		
		$label_lng = $this->elements[2];
		$label_lng_id = 0;

		$label_lat = $this->elements[3];
    $label_lat_id = "";

		$google_key = "xxx";	
		if($this->elements[4] != "")
			$google_key = $this->elements[4];

		$address = explode(",",$this->elements[5]);
		
		if (!isset($this->elements[7]) || $this->elements[7]=="") $this->elements[7] = '400';
		if ($this->elements[7] != "")
			$mapwidth = $this->elements[7];

		if (!isset($this->elements[8]) || $this->elements[8]=="") $this->elements[8] = '200';
		if ($this->elements[8] != "")			
			$mapheight = $this->elements[8];

		if (!isset($this->elements[9]) || $this->elements[9]=="") $this->elements[9] = '1';
		if ($this->elements[9] != "")			
			$mapresize = $this->elements[9];
		
		$label = "";
    if(isset($this->elements[6]))
			$label = $this->elements[6];
			
		$label_ids = array();

		foreach($this->obj as $o)
		{
			if($o->getName() == $label_lng)
				$label_lng_id = $o->getId();
			if($o->getName() == $label_lat)
				$label_lat_id = $o->getId();
			if(in_array($o->getName(),$address))
				$label_ids[] = $o->getId();
		}

		// rex_com::debug($this->obj);
	
		if ($this->value == "" && !$send)
			if (isset($this->elements[4])) 
				$this->value = $this->elements[4];

		$wc = "";
		if (isset($warning["el_" . $this->getId()])) 
			$wc = $warning["el_" . $this->getId()];


		$vv = "";
		foreach($label_ids as $k => $v)
		{
			$vv .= 'geofield'.($k+1).': "el_'.$v.'", ';
		}

		
		
		//global $REX; // Für resize.gif notwendig!
		if (file_exists('../files/addons/xform/resize.gif'))
		{
			$REX['HTDOCS_PATH'] = '../';
		}
		else{
			$REX['HTDOCS_PATH'] = './';
		}

		// Script nur beim ersten mal ausgeben
		if (!defined('REX_XFORM_GOOGLE_GEOCODE_JSCRIPT')) {
			define('REX_XFORM_GOOGLE_GEOCODE_JSCRIPT', true);
			$form_output[] = '
			
	<script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;key='.$google_key.'" type="text/javascript"></script>		
	
	<script type="text/javascript">
	//<![CDATA[

	var rexGmap_GeoCoder = function(loadoptions)
	{	

		var rexGmap_GeoCoder_defaults = {
			id: 1, // Id für Map-Canvas
			lat: "", // Feld für lat
			lng: "", // Feld für lng
			geofield1: "", // Feld für Geo-Ermittlung
			geofield2: "", // Feld für Geo-Ermittlung
			geofield3: "", // Feld für Geo-Ermittlung
			htdocspath: "./", // Pfad zu HTDOCS
			rex_zoom: 10, // Default Zoom bei vorhandenen Geo-Daten
			rex_zoom_get: 15, // Default Zoom nach Ermittlung der Geo-Daten
			rex_zoom_err: 5, // Default Zoom bei nicht vorhandenen Geo-Daten
			rex_default_lat: 51, // Default-Position bei nicht vorhandenen Geo-Daten
			rex_default_lng: 10, // Default-Position bei nicht vorhandenen Geo-Daten		
			min_width: 300, // minimale Map-Breite
			min_height: 150, // minimale Map-Höhe
			resizable_map: true // Map resizeable
		};	
		var options = jQuery.extend(rexGmap_GeoCoder_defaults, loadoptions);

		var map = null;
		var geocoder = null;
		var marker = null;

		var mapcontainer = null;
		var resizeButton = null;
		var resizable = false;
		var mouseX, mouseY, diffX, diffY;

		function ResizeControl(){};
		ResizeControl.prototype = new GControl();
		ResizeControl.prototype.initialize = function() 
		{
			resizeButton = document.createElement("div");
			resizeButton.style.width = "20px";
			resizeButton.style.height = "20px";
			resizeButton.style.backgroundImage = "url(\'"+options.htdocspath+"files/addons/xform/resize.gif\')";
			resizeButton.style.cursor = "se-resize";

			mapcontainer = map.getContainer();
			mapcontainer.appendChild(resizeButton);

			var terms = mapcontainer.childNodes[2];
			terms.style.marginRight = "20px";

			jQuery(resizeButton).mousedown(function(){ resizable = true; map.hideControls(); resizeButton.style.visibility = "visible"; });
			jQuery("body").mouseup(function(){ resizable = false; map.checkResize(); map.showControls(); });
			jQuery("body").mousemove(function(e){ watchMouse(e); });

			return resizeButton;
		}
		ResizeControl.prototype.getDefaultPosition = function()
		{
			return new GControlPosition(G_ANCHOR_BOTTOM_RIGHT, new GSize(0,0));
		}

		function watchMouse(e) 
		{
			// Include possible scroll values
			var sx = window.scrollX || document.documentElement.scrollLeft || 0;
			var sy = window.scrollY || document.documentElement.scrollTop || 0;

			if(!e) e = window.event; // IEs event definition
			mouseX = e.clientX + sx;
			mouseY = e.clientY + sy;

			// Direction of mouse movement * deltaX: -1 for left, 1 for right * deltaY: -1 for up, 1 for down
			var deltaX = mouseX - diffX;
			var deltaY = mouseY - diffY;
			// Store difference in global variables
			diffX = mouseX;
			diffY = mouseY;

			if (resizable) 
			{ 
				changeMapSize(deltaX, deltaY);
			}

			return false;
		}

		function changeMapSize(dx, dy) 
		{
			var width = parseInt(mapcontainer.style.width);
			var height =  parseInt(mapcontainer.style.height);
			if ((width + dx) < options.min_width) { width = options.min_width; dx = 0; }
			if ((height + dy) < options.min_height) { height = options.min_height; dy = 0; }
			mapcontainer.style.width = (width + dx) + "px";
			mapcontainer.style.height= (height + dy) + "px";
		}

		function createMarker(point) 
		{
			var marker = new GMarker(point, {draggable:true, bouncy:true});

			GEvent.addListener(marker, "drag", function(){
				point = marker.getPoint();
				jQuery("#"+options.lat)[0].value = point.lat();
				jQuery("#"+options.lng)[0].value = point.lng();
			});
			GEvent.addListener(marker, "dragend", function(){
				point = marker.getPoint();
				jQuery("#"+options.lat)[0].value = point.lat();
				jQuery("#"+options.lng)[0].value = point.lng();
				map.panTo(point, true);
			});

			return marker;
		}

		function getGGeo(noalert)
		{
			var address = "";

			if (options.geofield1)
				address += jQuery("#"+options.geofield1)[0].value+", ";
			if (options.geofield2)
				address += jQuery("#"+options.geofield2)[0].value+", ";
			if (options.geofield3)
				address += jQuery("#"+options.geofield3)[0].value+", ";

			if (geocoder) 
			{
				geocoder.getLatLng(address, function(point){
					if (!point) 
					{
						if (!noalert) { alert(address + " nicht gefunden!"); }
						return false
					} 
					else 
					{
						map.savePosition();
						jQuery("#"+options.lat)[0].value = point.lat();
						jQuery("#"+options.lng)[0].value = point.lng();
						if (map.getZoom() < options.rex_zoom_get) { map.setZoom(options.rex_zoom_get); }
						if (!marker) 
						{
							marker = createMarker(point);
							map.addOverlay(marker);
						}
						marker.setPoint(point);
						map.panTo(point, true);
						return true;
					}
				});
			}
			else
			{
				return false;
			}
		}

		jQuery(function($){

			if (GBrowserIsCompatible()) 
			{
				geocoder = new GClientGeocoder();
				var create_marker = true;

				var rex_lat = jQuery("#"+options.lat)[0].value;
				var rex_lng = jQuery("#"+options.lng)[0].value;

				if ((rex_lat == "" || rex_lat == 0) && (rex_lng == "" || rex_lng == 0)) 
				{
					if (!getGGeo(true))
					{
						options.rex_zoom = options.rex_zoom_err;
						rex_lat = options.rex_default_lat;
						rex_lng = options.rex_default_lng;		
						create_marker = false;
					}				
				}

				var point = new GLatLng(rex_lat, rex_lng);
				map = new GMap2(jQuery("#map_canvas"+options.id)[0], {draggableCursor:"default"});
				map.setCenter(point, options.rex_zoom);
				map.setUIToDefault();

				if (options.resizable_map)
				{
					map.addControl(new ResizeControl());
				}
				
				if (create_marker) 
				{
					marker = createMarker(point);
					map.addOverlay(marker);
				}

				GEvent.addListener(map, "click", function(overlay, point){
					if (point) 
					{
						map.savePosition();
						jQuery("#"+options.lat)[0].value = point.lat();
						jQuery("#"+options.lng)[0].value = point.lng();
						if (!marker) 
						{
							marker = createMarker(point);
							map.addOverlay(marker);
						}
						marker.setPoint(point);
						map.panTo(point, true);
					}
				});

				jQuery("#rex_getGGeo"+options.id).click(function(){
					getGGeo();
					return false;
				});
				jQuery("#rex_deleteGGeo"+options.id).click(function(){
					jQuery("#"+options.lat)[0].value = "0";
					jQuery("#"+options.lng)[0].value = "0";
					if (marker) 
					{
						map.removeOverlay(marker);
						marker = null;
					}
					return false;
				});
			}
			
		});	

	};

	//]]>
	</script>
			';
		}

		// für jede Map ein neues rexGmap_GeoCoder-Objekt anlegen
		$form_output[] = '
	<script type="text/javascript">
	//<![CDATA[
	var rex_geoOptions'.$this->getId().' = { id: '.$this->getId().', lat: "el_'.$label_lat_id.'", lng: "el_'.$label_lng_id.'", '.$vv.' htdocspath : "'.$REX['HTDOCS_PATH'].'", resizable_map: '.$mapresize.' };
	rex_geocoder'.$this->getId().' = new rexGmap_GeoCoder(rex_geoOptions'.$this->getId().');
	//]]>
	</script>	
		';
		
		
		
		$output = '
			<div class="xform-element form_google_geocode formlabel-'.$this->getName().'">
				<label class="text '.$wc.'" for="el_'.$this->getId().'_lat" >'.$label.'</label>
				<p class="form_google_geocode">';
		if ($vv != "")
			$output .= '<a href="#" id="rex_getGGeo'.$this->getId().'">Geodaten holen</a> | ';	
		$output .= '<a href="#" id="rex_deleteGGeo'.$this->getId().'">Geodaten nullen</a></p>
				<div class="form_google_geocode_map" id="map_canvas'.$this->getId().'" style="width:'.$mapwidth.'px; height:'.$mapheight.'px">Google Map</div>
			</div>';
			
		$form_output[] = $output;
			
		// $email_elements[$this->elements[1]] = stripslashes($this->value);
		// $sql_elements[$label_lat] = $label_lat_value;

	}
	
	function getDescription()
	{
		return "google_geocode -> Beispiel: google_geocode|gcode|pos_lng|pos_lat|googlemapkey|strasse,plz,ort|Google Map|width|height|resizeable(1/0)
		";
	}
	
  function getDefinitions()
  {
    return array(
            'type' => 'value',
            'name' => 'google_geocode',
            'values' => array(
              array( 'type' => 'name',   'label' => 'Name' ),
              array( 'type' => 'getName','label' => '"lng"-name'),
              array( 'type' => 'getName','label' => '"lat"-name'),
              array( 'type' => 'text',    'label' => 'GoogleMapKey'),
              array( 'type' => 'getNames','label' => 'Names Positionsfindung'),
              array( 'type' => 'text',     'label' => 'Bezeichnung'),
              array( 'type' => 'text',     'label' => 'Map-Breite'),
              array( 'type' => 'text',     'label' => 'Map-H&ouml;he'),
              array( 'type' => 'boolean', 'label' => 'Resizeable',         'default' => 1),
            ),
            'description' => 'GoogeMap Positionierung',
            'dbtype' => 'text'
      );
  
  }
	
}

?>