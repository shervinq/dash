<?php
namespace addons\content_cp\posts\edit;

class view extends \addons\content_cp\posts\main\view
{
	public function config()
	{
		parent::config();

		$id = \lib\utility::get('id');

		$detail = \lib\app\posts::get($id);
		if(!$detail)
		{
			\lib\error::access(T_("Invalid id"));
		}

		$this->data->dataRaw = $detail;
		$this->data->cat_list = \lib\app\term::cat_list();



		$this->data->page['title'] = T_("Edit post");
		$this->data->page['desc']  = T_("You can change everything, change url and add gallery or some other change");

		$this->data->page['badge']['link'] = \lib\url::this(). $this->data->moduleType;
		$this->data->page['badge']['text'] = T_('Back to list of posts');

		$myType = \lib\utility::get('type');
		if($myType)
		{
			switch ($myType)
			{
				case 'page':
					$this->data->page['title'] = T_('Edit page');
					$this->data->page['badge']['text'] = T_('Back to list of pages');
					break;
			}
		}

	}
}
?>