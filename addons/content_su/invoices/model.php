<?php
namespace addons\content_su\invoices;


class model extends \addons\content_su\main\model
{
	public function invoices_list($_args, $_fields = [])
	{
		$meta   = [];
		$meta['admin'] = true;

		$search = null;
		if(\lib\request::get('search'))
		{
			$search = \lib\request::get('search');
		}

		foreach ($_fields as $key => $value)
		{
			if(isset($_args->get($value)[0]))
			{
				$meta[$value] = $_args->get($value)[0];
			}
		}

		$result = \lib\db\invoices::search($search, $meta);

		return $result;
	}
}
?>
