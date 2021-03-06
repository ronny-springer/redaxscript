/**
 * @tableofcontents
 *
 * 1. dawanda
 */

/* @section 1. dawanda */

r.module.dawanda =
{
	startup: true,
	options:
	{
		key: '380d7924396f5596116f3d8815c97dfd8c975582',
		url: 'dawanda.com/api/v1',
		language: 'en'
	},
	routes:
	{
		'getCategoriesForShop': 'shops/{id}/shop_categories.js',
		'getCategoryDetails': 'categories/{id}.js',
		'getChildrenOfCategory': 'categories/{id}/children.js',
		'getColors': 'colors.js',
		'getPinboardDetails': 'pinboards/{id}.js',
		'getProductDetails': 'products/{id}.js',
		'getProductsForCategory': 'categories/{id}/products.js',
		'getProductsForColor': 'colors/{id}/products.js',
		'getProductsForPinboard': 'pinboards/{id}/products.js',
		'getProductsForShop': 'shops/{id}/products.js',
		'getProductsForShopCategory': 'shop_categories/{id}/products.js',
		'getShopCategoryDetails': 'shop_categories/{id}.js',
		'getShopDetails': 'shops/{id}.js',
		'getTopCategories': 'categories/top.js',
		'getUserDetails': 'users/{id}.js',
		'getUserPinboards': 'users/{id}/pinboards.js',
		'searchProductForColor': 'colors/{id}/products/search.js',
		'searchProducts': 'products/search.js',
		'searchShops': 'shops/search.js',
		'searchUsers': 'users/search.js'
	}
};