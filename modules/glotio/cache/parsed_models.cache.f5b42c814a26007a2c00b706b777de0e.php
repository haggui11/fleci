<?php

class GlotioBlockreassuranceReassuranceactivitypsreassuranceModel extends ObjectModel
{
    const TYPE_LINK_NONE = 0;
    const TYPE_LINK_CMS_PAGE = 1;
    const TYPE_LINK_URL = 2;
    public $id = null;
    public $icon = null;
    public $custom_icon = null;
    public $title = null;
    public $description = null;
    public $status = null;
    public $position = null;
    public $id_shop = null;
    public $type_link = null;
    public $link = null;
    public $id_cms = null;
    public $date_add = null;
    public $date_upd = null;
    public static $definition = ['table' => 'psreassurance', 'primary' => 'id_psreassurance', 'multilang' => true, 'multilang_shop' => true, 'fields' => ['icon' => ['type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isCleanHtml', 'size' => 255], 'custom_icon' => ['type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isCleanHtml', 'size' => 255], 'title' => ['type' => self::TYPE_STRING, 'shop' => true, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 255], 'description' => ['type' => self::TYPE_HTML, 'shop' => true, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 2000], 'status' => ['type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool', 'required' => true], 'position' => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isunsignedInt', 'required' => false], 'type_link' => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isunsignedInt', 'required' => false], 'id_cms' => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isunsignedInt', 'required' => false], 'link' => ['type' => self::TYPE_STRING, 'shop' => true, 'lang' => true, 'validate' => 'isUrl', 'required' => false, 'size' => 255], 'date_add' => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'], 'date_upd' => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate']]];
    public static $originalClassname = 'ReassuranceActivity';
}

class GlotioProductcommentsProductcommentcriterionproductCommentCriterionModel extends ObjectModel
{
    const NAME_MAX_LENGTH = 64;
    public $id = null;
    public $id_product_comment_criterion_type = null;
    public $name = null;
    public $active = true;
    public static $definition = ['table' => 'product_comment_criterion', 'primary' => 'id_product_comment_criterion', 'multilang' => true, 'fields' => [
        'id_product_comment_criterion_type' => ['type' => self::TYPE_INT],
        'active' => ['type' => self::TYPE_BOOL],
        // Lang fields
        'name' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => self::NAME_MAX_LENGTH],
    ]];
    public static $originalClassname = 'ProductCommentCriterion';
}

class GlotioPsImagesliderPsHomeslidehomesliderSlidesModel extends ObjectModel
{
    public $title = null;
    public $description = null;
    public $url = null;
    public $legend = null;
    public $image = null;
    public $active = null;
    public $position = null;
    public $id_shop = null;
    public static $definition = ['table' => 'homeslider_slides', 'primary' => 'id_homeslider_slides', 'multilang' => true, 'fields' => [
        'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
        'position' => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
        // Lang fields
        'description' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 4000],
        'title' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 255],
        'legend' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 255],
        'url' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isUrl', 'required' => true, 'size' => 255],
        'image' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 255],
    ]];
    public static $originalClassname = 'Ps_HomeSlide';
}

class GlotioPsLinklistLinkblocklinkBlockModel extends ObjectModel
{
    public $id_link_block = null;
    public $name = null;
    public $id_hook = null;
    public $position = null;
    public $content = null;
    public $custom_content = null;
    public static $definition = ['table' => 'link_block', 'primary' => 'id_link_block', 'multilang' => true, 'fields' => ['name' => ['type' => self::TYPE_STRING, 'lang' => true, 'required' => true, 'size' => 40], 'id_hook' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true], 'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true], 'content' => ['type' => self::TYPE_STRING, 'validate' => 'isJson'], 'custom_content' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isJson']]];
    public static $originalClassname = 'LinkBlock';
}

class GlotioPsgdprGdprconsentpsgdprConsentModel extends ObjectModel
{
    public $id_module = null;
    public $active = null;
    public $error = null;
    public $error_message = null;
    public $message = null;
    public $date_add = null;
    public $date_upd = null;
    public static $definition = ['table' => 'psgdpr_consent', 'primary' => 'id_gdpr_consent', 'multilang' => true, 'multilang_shop' => true, 'fields' => [
        // Config fields
        'id_module' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true],
        'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
        'error' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => false],
        'error_message' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'required' => false],
        // Lang fields
        'message' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 4000],
        'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
    ]];
    public static $originalClassname = 'GDPRConsent';
}

class GlotioSmartblogBlogcategorysmartBlogCategoryModel extends ObjectModel
{
    public $id_smart_blog_category = null;
    public $id_parent = null;
    public $position = null;
    public $desc_limit = null;
    public $active = 1;
    public $created = null;
    public $modified = null;
    public $meta_title = null;
    public $meta_keyword = null;
    public $meta_description = null;
    public $description = null;
    public $link_rewrite = null;
    public static $definition = array('table' => 'smart_blog_category', 'primary' => 'id_smart_blog_category', 'multilang' => true, 'fields' => array('id_parent' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'), 'position' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'), 'desc_limit' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'), 'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'), 'created' => array('type' => self::TYPE_DATE, 'validate' => 'isString'), 'modified' => array('type' => self::TYPE_DATE, 'validate' => 'isString'), 'meta_title' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => true), 'meta_keyword' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'lang' => true), 'meta_description' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'), 'description' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'), 'link_rewrite' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => true)));
    public static $originalClassname = 'BlogCategory';
}

class GlotioSmartblogSmartblogpostsmartBlogPostModel extends ObjectModel
{
    public $id_smart_blog_post = null;
    public $id_author = null;
    public $id_category = null;
    public $position = 0;
    public $active = 1;
    public $available = null;
    public $created = null;
    public $modified = null;
    public $short_description = null;
    public $viewed = null;
    public $comment_status = 1;
    public $post_type = null;
    public $meta_title = null;
    public $meta_keyword = null;
    public $meta_description = null;
    public $image = null;
    public $content = null;
    public $link_rewrite = null;
    public $is_featured = null;
    public static $definition = array('table' => 'smart_blog_post', 'primary' => 'id_smart_blog_post', 'multishop' => true, 'multilang' => true, 'fields' => array('active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'), 'position' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'), 'id_category' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'), 'id_author' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'), 'available' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'), 'created' => array('type' => self::TYPE_DATE, 'validate' => 'isString'), 'modified' => array('type' => self::TYPE_DATE, 'validate' => 'isString'), 'viewed' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'), 'is_featured' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'), 'comment_status' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'), 'post_type' => array('type' => self::TYPE_STRING, 'validate' => 'isString'), 'image' => array('type' => self::TYPE_STRING, 'validate' => 'isString'), 'meta_title' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'lang' => true, 'required' => true), 'meta_keyword' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'), 'meta_description' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'), 'short_description' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => true), 'content' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'required' => true), 'link_rewrite' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => false)));
    public static $originalClassname = 'SmartBlogPost';
}

class GlotioTtcmsbannerTtcmsbannerinfottcmsbannerinfoModel extends ObjectModel
{
    public $id_ttcmsbannerinfo = null;
    public $text = null;
    public static $definition = ['table' => 'ttcmsbannerinfo', 'primary' => 'id_ttcmsbannerinfo', 'multilang' => true, 'multilang_shop' => true, 'fields' => [
        'id_ttcmsbannerinfo' => ['type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'],
        // Lang fields
        'text' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true],
    ]];
    public static $originalClassname = 'TtCmsBannerInfo';
}

class GlotioTtcmsbottombannerTtcmsbottombannerinfottcmsbottombannerinfoModel extends ObjectModel
{
    public $id_ttcmsbottombannerinfo = null;
    public $text = null;
    public static $definition = ['table' => 'ttcmsbottombannerinfo', 'primary' => 'id_ttcmsbottombannerinfo', 'multilang' => true, 'multilang_shop' => true, 'fields' => [
        'id_ttcmsbottombannerinfo' => ['type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'],
        // Lang fields
        'text' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true],
    ]];
    public static $originalClassname = 'TtCmsBottombannerInfo';
}

class GlotioTtcmsheaderTtcmsheaderinfottcmsheaderinfoModel extends ObjectModel
{
    public $id_ttcmsheaderinfo = null;
    public $text = null;
    public static $definition = ['table' => 'ttcmsheaderinfo', 'primary' => 'id_ttcmsheaderinfo', 'multilang' => true, 'multilang_shop' => true, 'fields' => [
        'id_ttcmsheaderinfo' => ['type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'],
        // Lang fields
        'text' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true],
    ]];
    public static $originalClassname = 'TtCmsHeaderInfo';
}

class GlotioTtcmspaymentlogoTtcmspaymentlogoinfottcmspaymentlogoinfoModel extends ObjectModel
{
    public $id_ttcmspaymentlogoinfo = null;
    public $text = null;
    public static $definition = ['table' => 'ttcmspaymentlogoinfo', 'primary' => 'id_ttcmspaymentlogoinfo', 'multilang' => true, 'multilang_shop' => true, 'fields' => [
        'id_ttcmspaymentlogoinfo' => ['type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'],
        // Lang fields
        'text' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true],
    ]];
    public static $originalClassname = 'TtCmsPaymentlogoInfo';
}

class GlotioTtcmstestimonialTtcmstestimonialinfottcmstestimonialinfoModel extends ObjectModel
{
    public $id_ttcmstestimonialinfo = null;
    public $text = null;
    public static $definition = ['table' => 'ttcmstestimonialinfo', 'primary' => 'id_ttcmstestimonialinfo', 'multilang' => true, 'multilang_shop' => true, 'fields' => [
        'id_ttcmstestimonialinfo' => ['type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'],
        // Lang fields
        'text' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true],
    ]];
    public static $originalClassname = 'TtCmsTestimonialInfo';
}

class GlotioTtmegamenuTtmegamenuclassttmegamenuModel extends ObjectModel
{
    public $type_link = null;
    public $dropdown = null;
    public $type_icon = null;
    public $icon = null;
    public $class = null;
    public $align_sub = null;
    public $width_sub = null;
    public $title = null;
    public $link = null;
    public $subtitle = null;
    public $position = null;
    public $active = null;
    public static $definition = array('table' => 'ttmegamenu', 'primary' => 'id_ttmegamenu', 'multilang' => true, 'multilang_shop' => true, 'fields' => array('type_link' => array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isunsignedInt', 'required' => true, 'size' => 255), 'dropdown' => array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isunsignedInt', 'required' => true), 'type_icon' => array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isunsignedInt', 'required' => true), 'icon' => array('type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isCleanHtml'), 'align_sub' => array('type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isCleanHtml'), 'width_sub' => array('type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isCleanHtml'), 'class' => array('type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isCleanHtml'), 'title' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'), 'link' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'), 'subtitle' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'), 'position' => array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isunsignedInt', 'required' => true), 'active' => array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool', 'required' => true)));
    public static $originalClassname = 'TtMegamenuClass';
}

class GlotioTtmegamenuTtmegamenuitemclassttmegamenuItemModel extends ObjectModel
{
    public $id_column = null;
    public $type_link = null;
    public $type_item = null;
    public $id_product = null;
    public $title = null;
    public $link = null;
    public $text = null;
    public $position = null;
    public $active = null;
    public $temp_url = '{tt_menu_h_url}';
    public static $definition = array('table' => 'ttmegamenu_item', 'primary' => 'id_item', 'multilang' => true, 'multilang_shop' => true, 'fields' => array('id_column' => array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isunsignedInt', 'required' => true), 'type_link' => array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isunsignedInt', 'required' => true, 'size' => 255), 'type_item' => array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isunsignedInt', 'required' => true, 'size' => 255), 'id_product' => array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isunsignedInt', 'size' => 255), 'title' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'), 'link' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'), 'text' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'), 'position' => array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isunsignedInt', 'required' => true), 'active' => array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool', 'required' => true)));
    public static $originalClassname = 'TtMegamenuItemClass';
}

class GlotioTtproductcommentsTtproductcommentcriterionttproductCommentCriterionModel extends ObjectModel
{
    public $id = null;
    public $id_product_comment_criterion_type = null;
    public $name = null;
    public $active = true;
    public static $definition = array('table' => 'ttproduct_comment_criterion', 'primary' => 'id_product_comment_criterion', 'multilang' => true, 'fields' => array('id_product_comment_criterion_type' => array('type' => self::TYPE_INT), 'active' => array('type' => self::TYPE_BOOL), 'name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 128)));
    public static $originalClassname = 'TtProductCommentCriterion';
}

class GlotioTtproductcountdownTtpcfttpcfModel extends ObjectModel
{
    public $from = null;
    public $to = null;
    public $active = null;
    public $name = null;
    public $from_tz = null;
    public $to_tz = null;
    public $to_time = null;
    public $to_date = null;
    public static $definition = array('table' => 'ttpcf', 'primary' => 'id_ttpcf', 'multilang' => true, 'fields' => array(
        // Classic fields
        'from' => array('type' => self::TYPE_DATE, 'validate' => 'isPhpDateFormat'),
        'to' => array('type' => self::TYPE_DATE, 'validate' => 'isPhpDateFormat'),
        // Lang fields
        'name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'),
    ));
    public static $originalClassname = 'TTPCF';
}

