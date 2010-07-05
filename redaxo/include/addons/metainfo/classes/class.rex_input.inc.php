<?php 

/*abstract*/ class rex_input
{
  var $value;
  var $attributes;
  
  /*public*/ function rex_input()
  {
    $this->value = '';
    $this->attributes = array();
  }
  
  /**
   * Setzt den Value des Input-Feldes
   */
  /*public*/ function setValue($value)
  {
    $this->value = $value;
  }

  /**
   * Gibt den Wert des Input-Feldes zurueck
   */
  /*public*/ function getValue()
  {
    return $this->value;
  }
  
  /**
   * Setzt ein HTML-Attribut des Input-Feldes
   */
  /*public*/ function setAttribute($name, $value)
  {
    if($name == 'value')
    {
      $this->value = $value;
    }
    else
    {
      $this->attributes[$name] = $value;
    }
  }

  /**
   * Gibt den Wert des Attributes $name zurueck falls vorhanden, sonst $default
   */
  /*public*/ function getAttribute($name, $default = null)
  {
    if($name == 'value')
    {
      return $this->getValue();
    }
    elseif(isset($this->attributes[$name]))
    {
      return $this->attributes[$name];
    }

    return $default;
  }

  /**
   * Prueft ob das Input-Feld ein Attribute $name besitzt
   */
  /*public*/ function hasAttribute($name)
  {
    return isset($this->attributes[$name]);
  }

  /**
   * Fuegt dem Input-Feld die Attribute $attributes hinzu 
   */
  /*public*/ function addAttributes($attributes)
  {
    foreach($attributes as $name => $value)
    {
      $this->setAttribute($name, $value);
    }
  }
  
  /**
   * Setzt die Attribute des Input-Feldes auf $attributes.
   * Alle vorher vorhanden Attribute werden geloescht/ueberschrieben.
   */
  /*public*/ function setAttributes($attributes)
  {
    $this->attributes = array();
    
    foreach($attributes as $name => $value)
    {
      $this->setAttribute($name, $value);
    }
  }

  /**
   * Gibt alle Attribute in Form eines Array zurueck
   */
  /*public*/ function getAttributes()
  {
    return $this->attributes;
  }
  
  /**
   * Gibt alle Attribute in String-Form zurueck
   */
  /*public*/ function getAttributeString()
  {
    $attr = '';
    foreach($this->attributes as $attributeName => $attributeValue)
    {
      $attr .= ' '. $attributeName .'="'. $attributeValue .'"';
    }
    return $attr;
  }
  
  /**
   * Gibt die HTML-Representation des Input-Feldes zurueck.
   * Diese beeinhaltet alle Attribute und den Wert des Feldes.
   */
  /*abstract*/ function getHtml()
  {
    // nichts tun
  }
  
  /**
   * Factory-Methode um rex_input_*-Elemente anhand des Types $inputType zu erstellen
   */
  /*public static*/ function factory($inputType)
  {
    
    switch($inputType)
    {
      case 'text': 
      case 'textarea': 
      case 'select': 
      case 'categoryselect': 
      case 'mediacategoryselect': 
      case 'radio': 
      case 'checkbox': 
      case 'date': 
      case 'time': 
      case 'datetime': 
      case 'mediabutton': 
      case 'medialistbutton': 
      case 'linkbutton': 
      case 'linklistbutton': 
      {
        $class = 'rex_input_'. $inputType;
        return new $class(); 
      } 
    }
    return null;
  }
}