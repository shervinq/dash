<?php
namespace dash\db;


/** transactions managing **/
class transactions
{

	use \dash\db\transactions\set;
	use \dash\db\transactions\budget;
	use \dash\db\transactions\total_paid;


	/**
	 * insert new record of transactions
	 *
	 * @param      <type>  $_arg   The argument
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	private static function insert()
	{
		\dash\db\config::public_insert('transactions', ...func_get_args());
		return \dash\db::insert_id();
	}


	public static function get_count()
	{
		return \dash\db\config::public_get_count('transactions', ...func_get_args());
	}

	/**
	 * update transactions
	 *
	 * @param      <type>  $_args  The arguments
	 * @param      <type>  $_id    The identifier
	 */
	public static function update()
	{
		return \dash\db\config::public_update('transactions', ...func_get_args());
	}


	/**
	 * get the transaction record
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function get()
	{
		$result = \dash\db\config::public_get('transactions', ...func_get_args());
		$result = self::ready($result);
		return $result;
	}


	/**
	 * Searches for the first match.
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function search($_string = null, $_options = [])
	{
		if(!is_array($_options))
		{
			$_options = [];
		}

		$en_number = \dash\utility\convert::to_en_number($_string);

		$default_option =
		[
			'search_field' =>
			"
				(
					users.mobile LIKE '%__string__%' OR
					users.email LIKE '%__string__%' OR
					transactions.plus LIKE '%$en_number%' OR
					transactions.minus LIKE '%$en_number%' OR
					transactions.title LIKE '%__string__%'
				)

			",
			'public_show_field' =>
				"
					transactions.*,
					users.mobile      AS `mobile`,
					users.displayname AS `displayname`
				",
			'master_join'         => " LEFT JOIN users ON users.id = transactions.user_id ",
		];

		$_options = array_merge($default_option, $_options);

		$result = \dash\db\config::public_search('transactions', $_string, $_options);
		$result = self::ready($result, true);
		return $result;
	}

	/**
	 * change some field to show
	 *
	 * @param      <type>  $_result  The result
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function ready($_result, $_multi_record = false)
	{
		if($_result && is_array($_result))
		{
			if($_multi_record)
			{
				foreach ($_result as $key => $value)
				{
					if(isset($value['unit_id']))
					{
						$_result[$key]['unit'] = \dash\app\units::get($value['unit_id'], true);
					}

					if(isset($value['code']))
					{
						$_result[$key]['code'] = self::get_caller($value['code']);
					}
				}

			}
			else
			{
				if(isset($_result['unit_id']))
				{
					$_result['unit'] = \dash\app\units::get($_result['unit_id'], true);
				}

				if(isset($_result['code']))
				{
					$_result['code'] = self::get_caller($_result['code']);
				}
			}
		}
		return $_result;
	}
}
?>
