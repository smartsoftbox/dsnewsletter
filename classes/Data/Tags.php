<?php
/**
 * 2016 Smart Soft.
 *
 * @author    Marcin Kubiak
 * @copyright Smart Soft
 * @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

const SUBSCRIBE = 'subscribe';
const UNSUBSCRIBE = 'unsubscribe';
const VIEW_ONLINE = 'view_online';

const NEWP = 'new';
const FEATURED = 'featured';
const ID = 'id';

class Tags
{
    public static $tags_label = array(
        '{shop_name}',
        '{shop_logo}',
        '{shop_url}',
        '{first_name}',
        '{last_name}',
        '{date1}',
        '{date2}',
        '{date3}',
        '{date4}',
        '{date5}',
        '{subscribe}',
        '{unsubscribe}',
        '{charset}',
        '{track}',
    );
    private $id_lang;
    private $first_name;
    private $last_name;
    private $id_newsletter;
    private $id_customer;
    private $id_subscribe;
    private $tags_product_new;
    private $tags_product_featured;
    private $tags_product_id;
    private $tags;
    private $id_stats;
    private $context;
    private $token;

    public function __construct(
        $content,
        $id_lang,
        $first_name,
        $last_name,
        $id_newsletter,
        $id_customer,
        $id_subscribe,
        $id_stats = false,
        $tag_click = false,
        $tag_track = false
    ) {
        $this->tags = $this->getTagsFromContent($content, $tag_click, $tag_track);
        $this->id_lang = $id_lang;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->id_newsletter = $id_newsletter;
        $this->id_customer = $id_customer;
        $this->id_stats = $id_stats;
        $this->id_subscribe = $id_subscribe;
        $this->context = Context::getContext();
        $this->token = $this->getNewsletterToken();
    }

    public static function getTagsLabelsWithoutTrack()
    {
        $tags = self::$tags_label;
        if (($key = array_search(TAG_TRACK, $tags)) !== false) {
            unset($tags[$key]);
        }
        return $tags;
    }

    /**
     * @return string
     */
    public function getModuleBaseUrl()
    {
        return Tools::getHttpHost(true) . __PS_BASE_URI__ . "modules/dsnewsletter/";
    }

    public function getNewsletterControllerLink()
    {
        return $this->context->link->getModuleLink(
            'dsnewsletter',
            'newsletter',
            array( 'token' => $this->token )
        );
    }

    /**
     * @return string
     */
    public function getNewsletterToken()
    {
        $token_name = 'dsnewsletter/token/' . implode(
            '/',
            array($this->id_subscribe, $this->id_customer, $this->id_lang, $this->id_stats)
        );

        return Tools::substr(Tools::encrypt($token_name), 0, 10);
    }

    public static function addWrapperTagToLinks($content)
    {
        $doc = new DOMDocument;
        $doc->encoding = 'utf-8';
        $doc->loadHTML( utf8_decode( $content ) ); // important!

        foreach ($doc->getElementsByTagName('a') as $link) {
            $link->setAttribute('href', TAG_CLICK . $link->getAttribute('href'));
        }

        return urldecode($doc->saveHTML( $doc->documentElement ));
    }

    public static function addWrapperTagToPlainTextLinks($content)
    {
        return str_replace(
            ['http://','https://'],
            [TAG_CLICK . 'http://', TAG_CLICK . 'https://'],
            $content
        );
    }

    /**
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public function getAllWithValue()
    {
        $tags_with_value = array(); // return value tags
        // take number of product tags new, featured, id
        $this->tags_product_new = $this->getProductsNbByType(NEWP, $this->tags);
        $this->tags_product_featured = $this->getProductsNbByType(FEATURED, $this->tags);
        $this->tags_product_id = $this->getProductsNbByType(ID, $this->tags);
        // get featured products with tag name as key
        $featured_products_with_nb_key = $this->getFeaturedProductsWithTagNameAsKey();
        // get new products with tag name as key
        $new_products_with_nb_key = $this->getNewProductsWithTagNameAsKey();

        foreach ($this->tags as $tag) {
            $copy_tag = $tag;
            $copy_tag = $this->removeBrackets($copy_tag); // remove brackets

            if ($this->isStartWithString('product_', $copy_tag)) {
                $copy_tag = explode('_', $copy_tag);
                $function = 'get' . ucfirst($copy_tag[0])  . ucfirst($copy_tag[1]) . ucfirst($copy_tag[3]);

                $product = null;
                if ($copy_tag[1] === NEWP) {
                    $product = $new_products_with_nb_key[$copy_tag[2]];
                } else if ($copy_tag[1] === FEATURED) {
                    $product = $featured_products_with_nb_key[$copy_tag[2]];
                } else if ($copy_tag[1] === ID) {
                    $product = (array)new Product($copy_tag[2]);
                }

                $limit = (isset($copy_tag[4]) ? $copy_tag[4] : false); // check if limit is set
                $tags_with_value[$tag] = $this->$function($product, $limit);
            } else {
                $function = 'get' . $this->convertUnderscoreToCapital($copy_tag);
                $tags_with_value[$tag] = $this->$function();
            }
        }
        return $tags_with_value;
    }

    public function getProductsNbByType($start, $tags)
    {
        $start = '{product_' . $start;
        $nb = array();
        foreach ($tags as $tag) {
            if ($this->isStartWithString($start, $tag)) {
                $tag = explode('_', $tag);
                if (isset($tag[2])) {
                    $nb[] = (int)$tag[2];
                }
            }
        }

        return array_unique($nb);
    }

    public function isStartWithString($start, $string)
    {
        return (strpos($string, $start) === 0);
    }

    public function convertUnderscoreToCapital($string)
    {
        return implode('', array_map('ucfirst', explode('_', $string)));
    }

    public function limitStringAndAddDots($string, $limit)
    {
        return $limit && strlen($string) > $limit ? substr($string, 0, $limit) . "..." : $string;
    }

    public function getProductNewName($product, $limit)
    {
        return $this->limitStringAndAddDots($product['name'], $limit);
    }

    public function getProductNewDesc($product, $limit)
    {
        return $this->limitStringAndAddDots($product['description'], $limit);
    }

    public function getProductNewImage($product, $limit = 0)
    {
        return $this->getProductImageLink($product['id_product']);
    }

    public function getProductNewLink($product, $limit = 0)
    {
        return Context::getContext()->link->getproductlink(new Product($product['id_product']));
    }

    public function getProductFeaturedName($product, $limit = 0)
    {
        return $this->limitStringAndAddDots($product['name'], $limit);
    }

    public function getProductFeaturedDesc($product, $limit = 0)
    {
        return $this->limitStringAndAddDots($product['description'], $limit);
    }

    public function getProductFeaturedImage($product, $limit = 0)
    {
        return $this->getProductImageLink($product['id_product']);
    }

    public function getProductFeaturedLink($product, $limit = 0)
    {
        return Context::getContext()->link->getproductlink(new Product($product['id_product']));
    }

    public function getProductIdName($product, $limit = 0)
    {
        return $this->limitStringAndAddDots($product['name'], $limit);
    }

    public function getProductIdDesc($product, $limit = 0)
    {
        return $this->limitStringAndAddDots($product['description'], $limit);
    }

    public function getProductIdLink($product, $limit = 0)
    {
        return $this->context->link->getproductlink(new Product($product['id_product']));
    }

    public function getProductIdImageLink($product, $limit = 0)
    {
        return $this->getProductImageLink($product['id_product']);
    }

    public function getShopName()
    {
        return Tools::safeOutput(
            Configuration::get('PS_SHOP_NAME', null, null, $this->id_lang)
        );
    }

    public function getShopUrl()
    {
        return Tools::getHttpHost(true) . __PS_BASE_URI__;
    }

    public function getShopLogo()
    {
        return $this->getShopUrl() . 'img/logo.jpg';
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function getDate1()
    {
        return date("d-m-y", time());     // 07-02-08
    }

    public function getDate2()
    {
        return date("D d/n/Y", time());     // Thu 7/2/2008
    }

    public function getDate3()
    {
        return date("d M y", time());     //07 Feb 08
    }

    public function getDate4()
    {
        return date("l jS of F", time());     // Thursday 7th of February
    }

    public function getDate5()
    {
        return date("l jS of F g:i A.", time());     // Thursday 7th of February 4:45 PM.
    }

    public function getSubscribe()
    {
        return $this->getLink(SUBSCRIBE);
    }

    public function getUnsubscribe()
    {
        return $this->getLink(UNSUBSCRIBE);
    }

    public function getViewOnline()
    {
        return $this->getLink(VIEW_ONLINE);
    }

    public function getCharset()
    {
        return (Configuration::get('DSNEWSLETTER_MAIL_ENCODE') ? 'UTF-8' : 'ISO-8859-2 ');
    }

    public function getTrack()
    {
        return '<img src="' . $this->getModuleBaseUrl() . 'mails/track/track.gif?idst=' .
            Dsnewsletter::encryptText($this->id_stats) . '" />';
    }

    public function getClickWrapper()
    {
        return $this->getLink('open') . '&href=';
    }

    public function getLink($action)
    {
        $data = array(
            'ids' => $this->id_subscribe,
            'idc' => $this->id_customer,
            'idn' => $this->id_newsletter,
            'idl' => $this->id_lang,
            'idst' => $this->id_stats,
            'action' => $action,
        );
        //encrypt ids and action
        $data_encrypt = array_map('Dsnewsletter::encryptText', $data);

        return $this->getNewsletterControllerLink() . '&' . http_build_query($data_encrypt);
    }

    /**
     * @param $id_product
     * @return string
     */
    private function getProductImageLink($id_product)
    {
        $id_image = product::getcover($id_product);
        if(!$id_image) {
            return '';
        }
        $image = new Image($id_image['id_image']);
        return Tools::gethttphost(true) . __PS_BASE_URI__ . _THEME_PROD_DIR_ . $image->getexistingimgpath() . ".jpg";
    }

    public function getTagsFromContent($content, $tag_click = false, $tag_track = false)
    {
        if(!$content) { return null; }
        $correct_tags = array();
        $patterns = $this->getProductAllPatterns();
        $tags = $this->getTags($content);

        foreach ($tags[0] as $tag) {
            if(in_array($tag, self::$tags_label)) {
                $correct_tags[] = $tag;
            }
            if($tag_click && $tag === TAG_CLICK || $tag_track && $tag === TAG_TRACK) {
                $correct_tags[] = $tag;
            }
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $tag)) {
                    $correct_tags[] = $tag;
                }
            }
        }
        return array_unique($correct_tags);
    }

    /**
     * @param $type
     * @param $field
     * @param $limit
     * @return string
     */
    public function getProductPattern($type, $field, $limit = null)
    {
        return '{product_' . $type . '_[0-99]_' . $field . ($limit ? '_[0-99]' : '') . '}';
    }

    /**
     * get New Products with tag name as key
     * @return array
     */
    private function getNewProductsWithTagNameAsKey()
    {
        $new_products_with_nb_key = null;
        if ($this->tags_product_new) {
            $new_products = Product::getNewProducts($this->id_lang, 0, count($this->tags_product_new));
            // check if enough products if not take random
            $this->getRandomProductsIfNeeded($new_products, $this->tags_product_new);
            $i = 0;
            foreach ($this->tags_product_new as $tag_product_new) {
                //tag name as key for new products
                $new_products_with_nb_key[$tag_product_new] = $new_products[$i];
                $i++;
            }
        }
        return $new_products_with_nb_key;
    }

    /**
     * get New Products with tag name as key
     */
    private function getRandomProductsIfNeeded(&$products, $tags)
    {
        if(is_array($products) && count($products) === count($tags)) {
            return;
        }

        $number = count($tags) - (is_array($products) ? count($products) : 0);
        $category = new Category(Context::getContext()->shop->getCategory(), (int)$this->id_lang);

        $additional_products = $category->getProducts(
            $this->id_lang,
            0,
            $number,
            null,
            null,
            false,
            true,
            true,
            $number
        );

        if(!$products) {
            $products = $additional_products;
        } else {
            foreach ($additional_products as $additional_product) {
                $products[] = $additional_product;
            }
        }
    }

    /**
     * @return array
     * @throws PrestaShopDatabaseException
     */
    private function getFeaturedProductsWithTagNameAsKey()
    {
        $featured_products_with_nb_key = null;
        if ($this->tags_product_featured) {
            $category = new Category((int)Configuration::get('HOME_FEATURED_CAT'), (int)$this->id_lang);
            $featured_products = $category->getProducts(
                $this->id_lang,
                0,
                count($this->tags_product_featured),
                null,
                null,
                false,
                true,
                true,
                count($this->tags_product_featured)
            );
            // check if there is enough featured if not take rando
            $this->getRandomProductsIfNeeded($featured_products, $this->tags_product_featured);
            $i = 0;
            foreach ($this->tags_product_featured as $tag_product_featured) {
                $featured_products_with_nb_key[$tag_product_featured] = $featured_products[$i];
                $i++;
            }
        }
        return $featured_products_with_nb_key;
    }

    /**
     * @param $copy_tag
     * @return array|string|string[]
     */
    public function removeBrackets($copy_tag)
    {
        $copy_tag = str_replace('{', '', $copy_tag);
        return str_replace('}', '', $copy_tag);
    }

    /**
     * @return array
     */
    private function getProductAllPatterns()
    {
        $product_types = array(NEWP, FEATURED, ID);
        $patterns = array();

        foreach ($product_types as $product_type) {
            $patterns[] = $this->getProductPattern($product_type, 'name');
            $patterns[] = $this->getProductPattern($product_type, 'name', true);
            $patterns[] = $this->getProductPattern($product_type, 'desc');
            $patterns[] = $this->getProductPattern($product_type, 'desc', true);
            $patterns[] = $this->getProductPattern($product_type, 'link');
            $patterns[] = $this->getProductPattern($product_type, 'image');
        }
        return $patterns;
    }

    /**
     * @param $content
     * @return array
     */
    public function getTags($content)
    {
        $tags = array();
        preg_match_all('/{(.*?)}/', $content, $tags);
        return $tags;
    }
}
