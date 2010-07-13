<?php

class rex_xform_action_abstract
{

	var $obj;

	var $params = array();
	var $elements = array();
	var $action = array();
	var $elements_email = array();
	var $elements_sql = array();

	// $this->objparams["warning"],$this->objparams["warning_messages"]
	function loadParams(&$params, $action, &$elements_email, &$elements_sql)
	{
		$this->params = &$params;
		$this->action = $action;
		$this->elements_email = &$elements_email;
		$this->elements_sql = &$elements_sql;
	}

	function setObjects(&$obj)
	{
		$this->obj = &$obj;
	}

	function execute()
	{
		return FALSE;
	}

	function getDescription()
	{
		return "no description entered";
	}

	function getLongDescription()
	{
		return "Es existiert keine auswührliche Klassenbeschreibung";
	}

	function getDefinitions()
	{
		return array();
	}

}

?>