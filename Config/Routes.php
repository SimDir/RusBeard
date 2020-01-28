<?php defined('ROOT') OR die('No direct script access.');

return array(
    
    
//    '\b([-_a-zA-Z0-9]+).css\b' => 'lessons/style/$1',
//    '([-_a-zA-Z0-9]+).js' => 'lessons/script/$1',
//    '\bimages\b/([-_a-zA-Z0-9]+).png' => 'lessons/images/$1',
//    '\blessons\b/([-_a-zA-Z0-9]+).htm' => 'lessons/page/$1',
//    'product/([0-9]+)' => 'main/product/0/$1',
//    'category/([0-9]+)' => 'main/category/$1',
//    'category/([0-9]+)/product/([0-9]+)' => 'main/product/$1/$2',
//    
//    'im' => 'user/index/',
//    'cart/addAjax/([0-9]+)' => 'main/addAjax/$1',
//    'cart/delete/([0-9]+)' => 'main/delete/$1',
//    'cart/checkout' => 'main/checkout',

//    '\binclude\b/([-_a-zA-Z0-9]+)/([-_a-zA-Z0-9]+).html' => 'index/pagef/$1/$2',
    '^error/([0-9]+)' => 'index/eror/$1',
//    'index.php' => 'main/redirect',
//    '\bpage\b' => 'index/eror/404',
//    '\bpage\b/([0-9]+)' => 'index/page/$1',
//    '\beditor\b/([-_a-zA-Z0-9]+)' => 'page/edit/$1',
//    '\beditorsave\b/([-_a-zA-Z0-9]+)' => 'page/save/$1',
    '^page/([-_a-zA-Z0-9]+)/([-_a-zA-Z0-9]+).html' => 'page/index/$1/$2',
    '^page/([-_a-zA-Z0-9]+.html)' => 'page/page/$1',
    
    '^id([0-9]+)' => 'user/index/$1',

//    '\bmoney\b/' => 'site/money/',
//    'logout' => 'user/logout'
    
//    'font' => 'res/font/',
//    'font/([-_a-z0-9]+)' => 'res/font/$1',
//    '\bcss\b' => 'res/css/',
    '\bcss\b/([-_a-z0-9]+)' => 'res/css/$1',
//    '\bjs\b' => 'res/js/',
    '\bjs\b/([-_a-z0-9]+)' => 'res/js/$1',
//    '\bimg\b' => 'res/img/',
//    '\bimg\b/([-_a-z0-9]+)' => 'res/img/$1'
);
