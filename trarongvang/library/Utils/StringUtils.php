<?php

class Utils_StringUtils
{
    public static function subText($message, $maxlength = 0)
    {
		$res = $message;
		if(strlen($message) > $maxlength){
			$res = mb_substr($message, 0, $maxlength - 3, 'UTF-8') . "...";
		}
		return $res;
	}
    
    public static function getUrlFromPost($post, $language = 'vi')
    {
		$res = $post['is_front_page'] ? '/' : ($post[(($language == 'vi' || $language == null)? '' : $language . '_') . 'seo_url'] ? ('/' . $post[(($language == 'vi' || $language == null)? '' : $language . '_') . 'seo_url']) : ('/post/index/id/' . $post['id']));
		return $res;
	}
    
    public static function getTextFromPost($post, $field, $language = 'vi')
    {
		return $post[(($language == 'vi' || $language == null)? '' : $language . '_') . $field];   
	}
}

