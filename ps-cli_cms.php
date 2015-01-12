<?php

class PS_CLI_CMS {

	public static function list_categories() {
		$context = Context::getContext();

			$out = new Cli\Table();
			$out->setHeaders(Array(
				'id',
				'parent',
				'level_depth',
				'active',
				'created',
				'updated',
				'position',
				'name',
				'description',
				'link_rewrite'
				)
			);

		$categories = CMSCategory::getCategories($context->language->id, false);

		//$categories[0][1] cannot be deleted so safe to assume it exists
		self::_table_recurse_categories($categories, $categories[0][1], 1, $out);

		$out->display();

		return;

	}

	//fills $table with categories infos
	private static function _table_recurse_categories($categories, $current, $id_category = 1, &$table) {

		$table->addRow(Array(
			$current['infos']['id_cms_category'],
			$current['infos']['id_parent'],
			$current['infos']['level_depth'],
			$current['infos']['active'],
			$current['infos']['date_add'],
			$current['infos']['date_upd'],
			$current['infos']['position'],
			$current['infos']['name'],
			$current['infos']['description'],
			$current['infos']['link_rewrite']
			)
		);

		if (isset($categories[$id_category])) {
			foreach (array_keys($categories[$id_category]) as $key) {
				self::_table_recurse_categories($categories, $categories[$id_category][$key], $key, $table);
			}
		}	
	}
}

?>
