<?php
require_once 'vendor/autoload.php';
require_once 'parse-bib.php';
use Cocur\Slugify\Slugify;

header('Content-Type: text/html; charset=utf-8');

if (get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_REQUEST);
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}

function get($array,$key,$default) {
    return array_key_exists($key,$array) ? $array[$key] : $default;
}

global $BIB;

session_start();

function redirect($url) {
    header("Location: $url");
    die();
}
function reverse($name,$args=array()) {
    global $BIB;
    return $BIB->router->generate($name,$args);
}

function render($template,$args=array()) {
    global $BIB;
    echo $BIB->twig->render($template,$args);
}

function respond_404() {
    header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
    render('404.html');
}

function get_entry($entry_key) {
    global $BIB;
    $entry = $BIB->db->records[$entry_key];
    if(!$entry) {
        respond_404();
        die();
    }
    return $entry;
}

function url_origin( $s, $use_forwarded_host = false )
{
    $ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
    $sp       = strtolower( $s['SERVER_PROTOCOL'] );
    $protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
    $port     = $s['SERVER_PORT'];
    $port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
    $host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
    $host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

function request_url( $s, $use_forwarded_host = false )
{
    return url_origin( $s, $use_forwarded_host ) . $s['REQUEST_URI'];
}

class Form {
    function __construct($fields,$defaults=array()) {
        $this->fields = $fields;
        $this->data = $defaults;
        foreach($this->fields as $field=>$options) {
            if(isset($options['multi'])) {
                if($_SERVER['REQUEST_METHOD']=='GET') {
                    $values = get($defaults,$field,array());
                } else {
                    $values = array();
                    foreach($_POST as $name=>$value) {
                        if(preg_match('/^'.$field.'-(?P<n>\d+)-(?P<field>.*)$/',$name,$matches)) {
                            $n = $matches['n'];
                            $subfield = $matches['field'];
                            if(!isset($values[$n])) {
                                $values[$n] = array();
                            }
                            $values[$n][$subfield] = $value;
                        }
                    }
                }
                $this->data[$field] = $values;
            } else {
                if(isset($_POST[$field])) {
                    $this->data[$field] = $_POST[$field];
                }
            }
        }
    }

    function clean() {
        $this->cleaned_data = array();
        foreach($this->fields as $field=>$options) {
            $value = isset($this->data[$field]) ? $this->data[$field] : null;
            if(isset($options['clean']) && is_callable($options['clean'])) {
                $value = call_user_func_array($options['clean'],array($value));
            }
            $this->cleaned_data[$field] = $value;
        }
    }
}

class BibSite {
    public $bibfile;
    private $password;
    public $template_dir;
    public $root_url;
    public $twig;
    public $db;

    public $type_options = array(
        'article' => 'Article',
        'book' => 'Book',
        'chapter' => 'Chapter',
        'online' => 'Web page',
        'artwork' => 'Artwork',
        'audio' => 'Audio',
        'review' => 'Review',
        'image' => 'Image',
        'video' => 'Video',
        'software' => 'Software',
        'patent' => 'Patent',
        'misc' => 'Miscellaneous'
    );

    function __construct($config) {
        $this->config = $config;
        $this->site_host = $config->site_host;
        $this->site_title = $config->site_title;
        $this->site_description = $config->site_description;
        $this->language = $config->language;
        $this->template_dir = $config->template_dir;
        $this->password = $config->password;
        $this->bibfile = $config->bibfile;
        $this->root_url = $config->root_url;
        $twig_loader = new Twig_Loader_Filesystem($this->template_dir);
        $this->twig = new Twig_Environment($twig_loader,array(
        ));
        $this->twig->addGlobal('settings',$this);
        $this->twig->addGlobal('root',$this->root_url);
        $this->twig->addGlobal('logged_in',array_key_exists('logged_in',$_SESSION) ? $_SESSION['logged_in'] : false);
        $this->twig->addFunction(new Twig_SimpleFunction('reverse',"reverse"));

        $source = file_get_contents($this->bibfile);
        $this->db = new BibDatabase($source);

        $this->make_collections();

        $this->router = new AltoRouter();
        $this->router->setBasePath($this->root_url);
        $this->router->addMatchTypes(array(
            'key'=>'[^{}\s%#/]+'
        ));
    }

    function make_collections() {
        $this->collections = array();
        foreach($this->db->records as $entry) {
            $ecs = $this->entry_collections($entry);
            foreach($ecs as $collection) {
                $collection->entries[] = $entry;
            }
        }
        ksort($this->collections);
    }

    function entry_collections($entry) {
        $ecs = array_map(
            function($c) {
                return trim($c);
            },
            explode(",",get($entry->fields,'collections',''))
        );
        $ecs = array_filter($ecs,function($x){return $x!='';});
        $ecs = array_map(function($collection) {
            $slugify = new Slugify();
            $slug = $slugify->slugify($collection);
            if(!isset($this->collections[$slug])) {
                $this->collections[$slug] = new Collection($collection,$slug);
            }
            return $this->collections[$slug];
        },$ecs);
        return $ecs;
    }

    function authenticate($password) {
        $hash = crypt($password,'bibbo');
        if($hash==$this->password) {
            $_SESSION['logged_in'] = true;
            return true;
        } else {
            return false;
        }
    }

    function logged_in() {
        return $_SESSION['logged_in'] === true;
    }

    function save_database() {
        $now = date('Y-m-d-H-i-s');
        copy($this->bibfile,"backups/{$now}-{$this->bibfile}");
        file_put_contents($this->bibfile,$this->db->as_bib());
     }

    function entry_json($entry) {
        $collections = $this->entry_collections($entry);
        $collections_data = array();
        foreach($collections as $collection) {
            $collections_data[] = $collection->name;
        }
        $data = $entry->as_json();
        $data = array_merge($data,[
            'pdf' => $entry->pdf(),
            'nicetype' => $this->type_options[$entry->type],
            'collections' => $collections_data,
            'url' => $entry->urls[0],
            'view' => url_origin($_SERVER).reverse('view_entry',array('key'=>$entry->key))
        ]);
        return $data;
    }
}

class Collection {
    function __construct($name,$slug) {
        $this->name = $name;
        $this->slug = $slug;
        $this->entries = array();
    }
}

$config = json_decode(file_get_contents("config.json"));
$BIB = new BibSite($config);
