<?php
require_once 'vendor/autoload.php';
require_once 'parse-bib.php';

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

class BibSite {
    public $bibfile;
    private $password;
    public $template_dir;
    public $root_url;
    public $twig;
    public $db;

    function BibSite($config) {
        $this->template_dir = $config->template_dir;
        $this->password = $config->password;
        $this->bibfile = $config->bibfile;
        $this->root_url = $config->root_url;
        $twig_loader = new Twig_Loader_Filesystem($this->template_dir);
        $this->twig = new Twig_Environment($twig_loader,array(
        ));
        $this->twig->addGlobal('root',$this->root_url."/");
        $this->twig->addGlobal('logged_in',$_SESSION['logged_in']);
        $this->twig->addFunction(new Twig_SimpleFunction('reverse',"reverse"));

        $source = file_get_contents($this->bibfile);
        $this->db = new BibDatabase($source);

        $this->router = new AltoRouter();
        $this->router->setBasePath($this->root_url);
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
}

$config = json_decode(file_get_contents("config.json"));
$BIB = new BibSite($config);
