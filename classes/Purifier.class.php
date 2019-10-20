<?php

class Purifier {
    
    private $config;
    private $purifier;
    
    public function __construct(){
        
        require_once 'HTMLPurifier/HTMLPurifier.standalone.php';
        $this->config = HTMLPurifier_Config::createDefault();
        
        
    }
    
    public function set_config($type){
        
        switch($type){
            
            case 'mail':
                
                $this->config->set('HTML.AllowedElements', 'a,img,p,b,strong,i,em,u,ol,ul,li,span,div,font,br');
                $this->config->set('HTML.AllowedAttributes', 'a.href,img.src,span.class,span.style,div.align,font.color,font.size');
                
                break;
            case 'text':
            case 'software-text':
                
                $this->config->set('HTML.AllowedElements', 'p,b,strong,i,em,u,ol,ul,li,span,div,font,br');
                $this->config->set('HTML.AllowedAttributes', 'span.class,span.style,div.align,font.color,font.size');
                
                break;
            
            case 'news':
                
                $this->config->set('HTML.AllowedElements', 'p,b,strong,i,em,u,ol,ul,li,span,div,font,br,a');
                $this->config->set('HTML.AllowedAttributes', 'span.class,span.style,div.align,font.color,font.size,a.href');
                
                break;
            case 'clan-desc':
                
                $this->config->set('HTML.AllowedElements', 'img,p,b,strong,i,em,u,ol,ul,li,span,div,font,br,a,hr');
                $this->config->set('HTML.AllowedAttributes', 'img.src,span.class,span.style,div.align,font.color,font.size,a.href,a.title');
                
                break;
            case 'log':
                
                $this->config->set('HTML.AllowedElements', '');
                $this->config->set('HTML.AllowedAttributes', '');
                
                break;
            
        }
                
    }
    
    public function purify($text){
        
        $purifier = new HTMLPurifier($this->config);        
        return $purifier->purify($text);
        
    }
    
}