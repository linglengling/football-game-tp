<?php
class WM_Translator {

    public $language   = 'en';
    private $vi_text       = array();
    private $pt_br_text       = array();

    public function __construct(){
        $this->language = apply_filters( 'wpml_current_language', null );
        $this->vi_text = json_decode(file_get_contents(plugin_dir_path( __FILE__ ).'/json/vi.json',true));
        $this->pt_br_text = json_decode(file_get_contents(plugin_dir_path( __FILE__ ).'/json/pt-br.json',true));      
    }

    public function translate($str){

        if(strtolower($this->language) == "en"){
            return $str;
        }elseif(strtolower($this->language) == "vi"){
            return $this->vi_text->$str;
        }elseif(strtolower($this->language) == "pt-br"){
            return $this->pt_br_text->$str;
        }else{

        }
    }

    // private function findString($str) {
    //     if (array_key_exists($str, $this->lang[$this->language])) {
    //         echo $this->lang[$this->language][$str];
    //         return;
    //     }
    //     echo $str;
    // }

    // private function splitStrings($str) {
    //     return explode('=',trim($str));
    // }

    // public function __($str) {  
    //     if (!array_key_exists($this->language, $this->lang)) {
    //         if (file_exists($this->language.'.txt')) {
    //             $strings = array_map(array($this,'splitStrings'),file($this->language.'.txt'));
    //             foreach ($strings as $k => $v) {
    //                 $this->lang[$this->language][$v[0]] = $v[1];
    //             }
    //             return $this->findString($str);
    //         }
    //         else {
    //             echo $str;
    //         }
    //     }
    //     else {
    //         return $this->findString($str);
    //     }
    // }
}
?>