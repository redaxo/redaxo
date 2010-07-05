<?php

class rex_xform_be_medialist extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{	
		
		global $I18N;
		
		if (!isset($tmp_medialist)) $tmp_medialist = 0;
		$tmp_medialist++;
		
		$ausgabe = '';
		$options = '';
		$medialistarray = explode(",",$this->value);
		if (is_array($medialistarray))
		{
			for($j=0;$j<count($medialistarray);$j++)
			{
				if (current($medialistarray)!="")
					$options .= "<option value='".current($medialistarray)."'>".current($medialistarray)."</option>\n";
				next($medialistarray);
			}
		}
		
		
		$ausgabe .= '
		<div class="rex-widget">
      <div class="rex-widget-medialist">
        <input type="hidden" name="FORM['.$this->params["form_name"].'][el_'.$this->id.']" id="REX_MEDIALIST_'.$tmp_medialist.'" value="'.htmlspecialchars(stripslashes($this->getValue())) . '" />
        <p class="rex-widget-field">
          <select name="MEDIALIST_SELECT['.$tmp_medialist.']" id="REX_MEDIALIST_SELECT_'.$tmp_medialist.'" size="8">
          ' . $options . '
          </select>
        </p>

        <p class="rex-widget-icons rex-widget-2col">
          <span class="rex-widget-column rex-widget-column-first">
            <a href="#" class="rex-icon-file-top" onclick="moveREXMedialist('.$tmp_medialist.',\'top\');return false;" title="'. $I18N->msg('var_medialist_move_top') .'"></a>
            <a href="#" class="rex-icon-file-up" onclick="moveREXMedialist('.$tmp_medialist.',\'up\');return false;" title="'. $I18N->msg('var_medialist_move_up') .'"></a>
            <a href="#" class="rex-icon-file-down" onclick="moveREXMedialist('.$tmp_medialist.',\'down\');return false;" title="'. $I18N->msg('var_medialist_move_down') .'"></a>
            <a href="#" class="rex-icon-file-bottom" onclick="moveREXMedialist('.$tmp_medialist.',\'bottom\');return false;" title="'. $I18N->msg('var_medialist_move_bottom') .'"></a>
          </span>
          <span class="rex-widget-column">
            <a href="#" class="rex-icon-file-open" onclick="openREXMedialist('.$tmp_medialist.');return false;" title="'. $I18N->msg('var_media_open') .'"></a>
            <a href="#" class="rex-icon-file-add" onclick="addREXMedialist('.$tmp_medialist.');return false;" title="'. $I18N->msg('var_media_new') .'"></a>
            <a href="#" class="rex-icon-file-delete" onclick="deleteREXMedialist('.$tmp_medialist.');return false;" title="'. $I18N->msg('var_media_remove') .'"></a>
            <a href="#" class="rex-icon-file-view" onclick="viewREXMedialist('.$tmp_medialist.');return false;" title="'. $I18N->msg('var_media_open') .'"></a>
          </span>
        </p>
        <div class="rex-media-preview"></div>
      </div>
    </div>
    <div class="rex-clearer"></div>
		';
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		

		$wc = "";
		if (isset($warning["el_" . $this->getId()])) $wc = $warning["el_" . $this->getId()];
		
		
		
		$form_output[] = '
			<div class="xform-element formbe_medialist formlabel-'.$this->getName().'">
			
				<label class="text ' . $wc . '" for="el_' . $this->id . '" >' . $this->elements[2] . '</label>
				
				'.$ausgabe.'
				
			</div>';





		$email_elements[$this->elements[1]] = stripslashes($this->value);
		if (!isset($this->elements[3]) || $this->elements[3] != "no_db") $sql_elements[$this->elements[1]] = $this->value;

	}
	
	function getDescription()
	{
		return "be_medialist -> Beispiel: be_medialist|label|Bezeichnung|no_db";
	}
	
  function getDefinitions()
  {
    return array(
            'type' => 'value',
            'name' => 'be_medialist',
            'values' => array(
              array( 'type' => 'name',   'label' => 'Name' ),
              array( 'type' => 'text',   'label' => 'Bezeichnung'),
            ),
            'description' => 'Medialiste, welches Dateien aus dem Medienpool holt',
            'dbtype' => 'text'
      );
  }
	
	
	
}

?>