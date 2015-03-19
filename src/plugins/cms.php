<?php

class PS_CLI_Cms extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('cms', 'Manage CMS addOptions');
		$command->addOpt('list-categories', 'List categories', false, 'boolean')
			->addOpt('list-pages', 'List pages', false, 'boolean')
			->addOpt('delete-page', 'Delete page', false, 'integer')
			->addOpt('disable-page', 'Disable a page', false, 'integer')
			->addOpt('enable-page', 'Enable a page', false, 'integer')
			->addOpt('enable-category', 'Enable a category', false, 'integer')
			->addOpt('disable-category', 'Disable a category', false, 'integer')
			->addOpt('create-category', 'Create a category', false, 'boolean')
			->addOpt('delete-category', 'Delete a category', false, 'integer')
			->addOpt('name', 'Name of the category to create', false, 'string')
			->addOpt('parent', 'Id of the parent category', false, 'integer')
			->addOpt('link-rewrite', 'Link rewrite', false, 'string')
			->addOpt('meta_title', 'Meta title', false, 'string')
			->addOpt('meta_description', 'Meta description', false, 'string')
			->addOpt('meta_keywords', 'Meta keywords', false, 'string')
			->addOpt('description', 'Description of the category', false, 'string')
			->addArg('<ID>', 'Category or page ID', false);

		$this->register_command($command);
	}

	public function run() {
		$arguments = PS_CLI_ARGUMENTS::getArgumentsInstance();
		$interface = PS_CLI_INTERFACE::getInterface();

		if($opt = $arguments->getOpt('list-categories', false)) {
			$this->list_categories();
			$status = true;
		}
		elseif($opt = $arguments->getOpt('list-pages', false)) {
			$this->list_pages();
			$status = true;
		}
		elseif($pageId = $arguments->getOpt('delete-page', false)) {
			$status = $this->delete_page($pageId);
		}
		elseif($pageId = $arguments->getOpt('disable-page', false)) {
			$status = $this->disable_page($pageId);
		}
		elseif($pageId = $arguments->getOpt('enable-page', false)) {
			$status = $this->enable_page($pageId);
		}
		elseif($catId = $arguments->getOpt('enable-category', false)) {
			$status = $this->enable_category($catId);
		}
		elseif($catId = $arguments->getOpt('disable-category', false)) {
			$status = $this->disable_category($catId);
		}
		elseif($arguments->getOpt('create-category', false)) {
			$name = $arguments->getOpt('name', false);
			$parent = $arguments->getOpt('parent', false);
			$rewrite = $arguments->getOpt('link-rewrite', false);
			$description = $arguments->getOpt('description', '');
			$meta_title = $arguments->getOpt('meta_title', '');
			$meta_description = $arguments->getOpt('meta_description', '');
			$meta_keywords = $arguments->getOpt('meta_keywords', '');

			$status = $this->create_category(
				$parent,
				$name,
				$rewrite, 
				$description, 
				$meta_title, 
				$meta_description, 
				$meta_keywords);
		}
		elseif($catId = $arguments->getOpt('delete-category', false)) {
			$status = $this->delete_category($catId);
		}
		else {
			//$this->_show_command_usage('cms');
			$interface->exit_program(1);
		}

		if($status === true) {
			$interface->exit_program(0);
		}
		else {
			$interface->exit_program(1);
		}

	}

	// todo: use interface ?
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
				'lang',
				'description',
				'link_rewrite'
				)
			);

		$categories = CMSCategory::getCategories($context->language->id, false);

		//$categories[0][1] cannot be deleted so it's safe to assume it exists
		self::_table_recurse_categories($categories, $categories[0][1], 1, $out);

		$out->display();

		return;
	}

	//returns an array
	public static function get_categories_array() {

		$array = Array();

		$context = Context::getContext();

		$categories = CMSCategory::getCategories($context->language->id, false);
		self::_array_recurse_categories($categories, $categories[0][1], 1, $array);

		return $array;
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
			Language::getIsoById($current['infos']['id_lang']),
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

	private static function _array_recurse_categories($categories, $current, $id_category = 1, &$array) {

		array_push($array, $current['infos']);

		if (isset($categories[$id_category])) {
			foreach (array_keys($categories[$id_category]) as $key) {
				self::_array_recurse_categories($categories, $categories[$id_category][$key], $key, $array);
			}
		}	
	}

	public static function list_pages() {

		$context = Context::getContext();

		$table = new Cli\Table();

		$table->setHeaders( Array(
			'id',
			'category',
			'position',
			'active',
			'indexation',
			'lang',
			'title',
			'rewrite',
			'keywords'
			)
		);

		//$pages = CMS::listCms($context->language->id);
		$pages = CMS::getCMSPages($context->language->id, null, false);

		foreach ($pages as $page) {
			$table->addRow(Array(
				$page['id_cms'],
				$page['id_cms_category'],
				$page['position'],
				$page['active'],
				$page['indexation'],
				Language::getIsoById($page['id_lang']),
				$page['meta_title'],
				$page['link_rewrite'],
				$page['meta_keywords']
				)
			);
		}

		$table->display();

		return true;
	}

	public static function delete_page($pageId) {
		$context = Context::getContext();

		$page = new CMS($pageId, $context->language->id);

		//print_r($page);

		if(Validate::isLoadedObject($page)) {

			$page->context = $context;

			if($page->delete()) {
				echo "page $pageId successfully deleted\n";
				return true;
			}
			else {
				echo "Error, could not delete page $pageId\n";
				return false;
			}
		}
		else {
			echo "Could not load page ID $pageId\n";
			return false;
		}
	}

	public static function disable_page($pageId) {
		$context = Context::getContext();

		$page = new CMS($pageId, $context->language->id);

		if(Validate::isLoadedObject($page)) {

			$page->context = $context;

			if($page->active) {
				$page->active = false;
				if($page->update()) {
					echo "Successfully disabled page '$page->meta_title'\n";
					return true;
				}
				else {
					echo "Error, could not disable page '$page->meta_title'\n";
					return false;
				}
			}
			else {
				echo "Page '$page->meta_title' is already disabled\n";
				return true;
			}
		}
		else {
			echo "Could not find a page with page id $pageId\n";
			return false;
		}
	}

	public static function enable_page($pageId) {
		$context = Context::getContext();

		$page = new CMS($pageId, $context->language->id);

		if(Validate::isLoadedObject($page)) {

			$page->context = $context;

			if(!$page->active) {
				$page->active = true;
				if($page->update()) {
					echo "Successfully enabled page '$page->meta_title'\n";
					return true;
				}
				else {
					echo "Error, could not enable page '$page->meta_title'\n";
					return false;
				}
			}
			else {
				echo "Page '$page->meta_title' is already enabled\n";
				return true;
			}
		}
		else {
			echo "Could not find a page with id $pageId\n";
			return false;
		}
	}

	public static function disable_category($catId) {
		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();
		$category = new CMSCategory($catId);

		if(!Validate::isLoadedObject($category)) {
			echo "Error, could not find a category with id $catId\n";
			return false;
		}

		$categoryName = $category->name[$configuration->lang];
		
		if($category->active) {
			$category->active = false;
			if($category->update()) {
				echo "Successfully deactivated category $categoryName\n";
				return true;
			}
			else {
				echo "Could not disable category $categoryName\n";
				return false;
			}
		}
		else {
			echo "Category $categoryName is already disabled\n";
			return true;
		}
	}

	public static function enable_category($catId) {
		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();
		$category = new CMSCategory($catId);

		if(!Validate::isLoadedObject($category)) {
			echo "Error, could not find a category with id $catId\n";
			return false;
		}

		$categoryName = $category->name[$configuration->lang];
		
		if(!$category->active) {
			$category->active = true;
			if($category->update()) {
				echo "Successfully activated category $categoryName\n";
				return true;
			}
			else {
				echo "Could not enable category $categoryName\n";
				return false;
			}
		}
		else {
			echo "Category $categoryName is already active\n";
			return true;
		}
	}

	public static function delete_category($catId) {
		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();
		$category = new CMSCategory($catId);

		if(!Validate::isLoadedObject($category)) {
			echo "Error, Could not find a category with id $catId\n";
			return false;
		}

		$categoryName = $category->name[$configuration->lang];

		if($catId == 1) {
			echo "Error, you cannot delete the root category !\n";
			return false;
		}

		if($category->delete()) {
			echo "Successfully deleted category $categoryName and its subcategories\n";
			return true;
		}
		else {
			echo "Error, could not delete category $categoryName\n";
			return false;
		}
	}

	public static function create_category($parent, $name, $linkRewrite, $description = '', $meta_title = '', $meta_description = '', $meta_keywords = '') {

		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();

		$category = new CMSCategory();

		if(!Validate::isUnsignedId($parent)) {
			echo "Error, $parent is not a valid category ID\n";
			return false;
		}

		$parentCat = new CMSCategory($parent);
		if(!Validate::isloadedObject($parentCat)) {
			echo "Error: category $parentCat does not exists\n";
			return false;	
		}
		$category->id_parent = $parent;

		if(!Validate::isName($name)) {
			echo "Error, $name is not a valid category name\n";
			return false;
		}
		$category->name = Array($configuration->lang => $name);


		if(!Validate::isLinkRewrite($linkRewrite)) {
			echo "Error, $linkRewrite is not a valid link rewrite\n";
			return false;
		}
		$category->link_rewrite = Array($configuration->lang => $linkRewrite);

		if(!Validate::isCleanHtml($description)) {
			echo "Warning, $description is not a valid category description\n";
			$description = '';
		}
		$category->description = Array($configuration->lang => $description);

		if(!Validate::isGenericName($meta_title)) {
			echo "Warning, $meta_title is not a valid value for meta_title\n";
			$meta_title = '';
		}
		$category->meta_title = Array($configuration->lang => $meta_title);

		if(!Validate::isGenericName($meta_description)) {
			echo "Warning, $meta_description is not a valid value for meta_description\n";
			$meta_description = '';
		}
		$category->meta_description = Array($configuration->lang => $meta_description);

		if(!Validate::isGenericName($meta_keywords)) {
			echo "Warning, $meta_keywords is not a valid value for meta_keywords\n";
			$meta_keywords = '';
		}
		$category->meta_keywords = Array($configuration->lang => $meta_keywords);

		if($category->add()) {
			if($configuration->porcelain) {
				echo $category->id_cms_category;
			}
			else {
				echo "Successfully created category $category->id_cms_category\n";
			}

			return true;
		}
		else {
			echo "Error, could not create category $name\n";
			return false;
		}	
	}
}

PS_CLI_CONFIGURE::register_plugin('PS_CLI_Cms');

?>
