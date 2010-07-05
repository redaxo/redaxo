<?php

class rex_xform_article extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{
		$artikel = new rex_article;
		$artikel->setArticleId($this->elements[1]);
		$form_output[] = '<div class="article">' . $artikel->getArticle() . '</div>';
	}
	
	function getDescription()
	{
		return "article -> Beispiel: article|article_id";
	}

}

?>