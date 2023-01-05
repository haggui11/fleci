<?php return array (
  'name' => 'PRS142',
  'display_name' => 'PRS142',
  'version' => '1.0.0',
  'author' => 
  array (
    'name' => 'TemplateTrip',
    'email' => 'pub@prestashop.com',
    'url' => 'https://addons.prestashop.com/en/130_templatetrip',
  ),
  'meta' => 
  array (
    'compatibility' => 
    array (
      'from' => '1.7.0.0',
      'to' => NULL,
    ),
    'available_layouts' => 
    array (
      'layout-full-width' => 
      array (
        'name' => 'Full Width',
        'description' => 'No side columns, ideal for distraction-free pages such as product pages.',
      ),
      'layout-both-columns' => 
      array (
        'name' => 'Three Columns',
        'description' => 'One large central column and 2 side columns.',
      ),
      'layout-left-column' => 
      array (
        'name' => 'Two Columns, small left column',
        'description' => 'Two columns with a small left column',
      ),
      'layout-right-column' => 
      array (
        'name' => 'Two Columns, small right column',
        'description' => 'Two columns with a small right column',
      ),
    ),
  ),
  'assets' => 
  array (
    'css' => 
    array (
      'all' => 
      array (
        0 => 
        array (
          'id' => 'lightbox-style',
          'path' => 'assets/css/lightbox.css',
          'priority' => 190,
        ),
        1 => 
        array (
          'id' => 'owl-style',
          'path' => 'assets/css/owl.carousel.min.css',
          'priority' => 340,
        ),
        2 => 
        array (
          'id' => 'owl-theme-default-style',
          'path' => 'assets/css/owl.theme.default.min.css',
          'priority' => 350,
        ),
      ),
    ),
    'js' => 
    array (
      'all' => 
      array (
        0 => 
        array (
          'id' => 'owl-lib',
          'path' => 'assets/js/owl.carousel.min.js',
          'priority' => 310,
        ),
        1 => 
        array (
          'id' => 'lightbox-lib',
          'path' => 'assets/js/lightbox-2.6.min.js',
          'priority' => 320,
        ),
      ),
    ),
  ),
  'global_settings' => 
  array (
    'configuration' => 
    array (
      'PS_IMAGE_QUALITY' => 'png',
    ),
    'modules' => 
    array (
      'to_enable' => 
      array (
        0 => 'ps_contactinfo',
        1 => 'ps_linklist',
        2 => 'ttbestsellers',
        3 => 'ttfeaturedproducts',
        4 => 'ttnewproducts',
        5 => 'ttproductimagehover',
        6 => 'ttbrandlogo',
        7 => 'ttspecials',
        8 => 'ttcmsheader',
        9 => 'ttcmsbanner',
        10 => 'ttcategoryslider',
        11 => 'ttcmstestimonial',
        12 => 'ttcmsbottombanner',
        13 => 'ttadvertising',
        14 => 'ttcmspaymentlogo',
        15 => 'tawkto',
        16 => 'ttcompare',
        17 => 'ttproductwishlist',
        18 => 'ttmegamenu',
        19 => 'ttproductcomments',
        20 => 'smartblog',
        21 => 'smartbloghomelatestnews',
        22 => 'ttproductcountdown',
      ),
      'to_disable' => 
      array (
        0 => 'ps_customtext',
        1 => 'ps_banner',
      ),
    ),
    'hooks' => 
    array (
      'custom_hooks' => 
      array (
        0 => 
        array (
          'name' => 'displayHomeTab',
          'title' => 'Hometab content',
          'description' => 'Add a widget area above the footer',
        ),
        1 => 
        array (
          'name' => 'displayTopColumn',
          'title' => 'TopColumn content',
          'description' => 'Add a widget area above the content',
        ),
      ),
      'modules_to_hook' => 
      array (
        'displayNav1' => 
        array (
          0 => 'ttcmsheader',
          1 => 'ps_currencyselector',
          2 => 'ps_languageselector',
          3 => 'ps_customersignin',
        ),
        'displayNav2' => 
        array (
          0 => 'ps_shoppingcart',
          1 => 'ps_contactinfo',
        ),
        'displayTop' => 
        array (
          0 => 'ttmegamenu',
          1 => 'ps_searchbar',
        ),
        'displayTopColumn' => 
        array (
          0 => 'ps_imageslider',
          1 => 'ttcmsbanner',
        ),
        'displayHomeTab' => 
        array (
          0 => 'ttfeaturedproducts',
          1 => 'ttnewproducts',
          2 => 'ttbestsellers',
        ),
        'displayHome' => 
        array (
          0 => 'ttcmsbottombanner',
          1 => 'ttspecials',
          2 => 'ttcmstestimonial',
          3 => 'ttcategoryslider',
          4 => 'smartbloghomelatestnews',
          5 => 'ttbrandlogo',
        ),
        'displayFooter' => 
        array (
          0 => 'ps_contactinfo',
          1 => 'ps_customeraccountlinks',
          2 => 'ps_linklist',
          3 => 'ttcmspaymentlogo',
        ),
        'displayFooterBefore' => 
        array (
          0 => 'ps_emailsubscription',
          1 => 'ps_socialfollow',
        ),
        'displayLeftColumn' => 
        array (
          0 => 'ps_categorytree',
          1 => 'ps_facetedsearch',
          2 => 'ttadvertising',
          3 => 'ps_newproducts',
          4 => 'ttspecials',
        ),
        'displaySearch' => 
        array (
          0 => 'ps_searchbar',
        ),
        'displayProductAdditionalInfo' => 
        array (
          0 => 'ps_sharebuttons',
          1 => 'ttproductcomments',
        ),
        'displayFooterProduct' => 
        array (
          0 => 'ttproductcomments',
        ),
        'displayProductListReviews' => 
        array (
          0 => 'ttproductcomments',
        ),
        'displayReassurance' => 
        array (
          0 => 'blockreassurance',
        ),
        'displayOrderConfirmation2' => 
        array (
          0 => 'ps_featuredproducts',
        ),
        'displayCrossSellingShoppingCart' => 
        array (
          0 => 'ps_featuredproducts',
        ),
      ),
    ),
    'image_types' => 
    array (
      'cart_default' => 
      array (
        'width' => 125,
        'height' => 125,
        'scope' => 
        array (
          0 => 'products',
        ),
      ),
      'small_default' => 
      array (
        'width' => 80,
        'height' => 80,
        'scope' => 
        array (
          0 => 'products',
          1 => 'categories',
          2 => 'manufacturers',
          3 => 'suppliers',
        ),
      ),
      'medium_default' => 
      array (
        'width' => 452,
        'height' => 452,
        'scope' => 
        array (
          0 => 'products',
          1 => 'manufacturers',
          2 => 'suppliers',
        ),
      ),
      'home_default' => 
      array (
        'width' => 270,
        'height' => 270,
        'scope' => 
        array (
          0 => 'products',
        ),
      ),
      'large_default' => 
      array (
        'width' => 800,
        'height' => 800,
        'scope' => 
        array (
          0 => 'products',
          1 => 'manufacturers',
          2 => 'suppliers',
        ),
      ),
      'category_default' => 
      array (
        'width' => 1015,
        'height' => 200,
        'scope' => 
        array (
          0 => 'categories',
        ),
      ),
      'stores_default' => 
      array (
        'width' => 170,
        'height' => 115,
        'scope' => 
        array (
          0 => 'stores',
        ),
      ),
    ),
  ),
  'theme_settings' => 
  array (
    'default_layout' => 'layout-full-width',
    'layouts' => 
    array (
      'category' => 'layout-left-column',
      'best-sales' => 'layout-left-column',
      'new-products' => 'layout-left-column',
      'prices-drop' => 'layout-left-column',
      'contact' => 'layout-left-column',
      'search' => 'layout-left-column',
      'manufacturer' => 'layout-left-column',
      'module-smartblog-details' => 'layout-left-column',
      'module-smartblog-category' => 'layout-left-column',
      'pagenotfound' => 'layout-left-column',
    ),
  ),
  'dependencies' => 
  array (
    'modules' => 
    array (
      0 => 'ttbestsellers',
      1 => 'ttfeaturedproducts',
      2 => 'ttnewproducts',
      3 => 'ttspecials',
      4 => 'ttproductimagehover',
      5 => 'ttbrandlogo',
      6 => 'tawkto',
      7 => 'ttcmsheader',
      8 => 'ttcmsbanner',
      9 => 'ttcategoryslider',
      10 => 'ttcmstestimonial',
      11 => 'ttadvertising',
      12 => 'ttcmspaymentlogo',
      13 => 'smartblog',
      14 => 'smartbloghomelatestnews',
      15 => 'ttcompare',
      16 => 'ttmegamenu',
      17 => 'ttproductcountdown',
      18 => 'ttproductwishlist',
      19 => 'ttproductcomments',
      20 => 'ttcmsbottombanner',
    ),
  ),
);
