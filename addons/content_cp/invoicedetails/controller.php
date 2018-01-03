<?php
namespace addons\content_cp\invoicedetails;


class controller extends \addons\content_cp\main\controller

{
	public $fields =
	[
		'id',
		'invoice_id',
		'title',
		'price',
		'count',
		'total',
		'discount',
		'status',
		'createdate',
		'datemodified',
		'desc',
		'meta',
		'sort',
		'order',
		'search',
	];

	public function ready()
	{

		\lib\permission::access('cp:transaction:invoicedetails', 'block');

		$property                 = [];
		foreach ($this->fields as $key => $value)
		{
			$property[$value] = ["/.*/", true, $value];
		}

		$this->get(false, "list")->ALL(['property' => $property]);

	}
}
?>