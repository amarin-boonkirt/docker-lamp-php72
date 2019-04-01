<?php 


echo '<table><tr><td>System Require Name</td><td>STATUS</td></tr>';

$status = 'OK';
if (version_compare(PHP_VERSION, '7.1.3') < 0) {
    $status = 'FAIL: System required PHP Version > = 7.1.3';    
}
$status .= " ( Current PHP version ".PHP_VERSION.' )';
printf('<tr><td>PHP version</td><td>%s</td></tr>',$status);

$php_extensions = [];
$php_extensions['mysqlnd'] = [];
$php_extensions['pdo'] = [];
$php_extensions['gd'] = [];
$php_extensions['curl'] = [];
$php_extensions['iconv'] = [];
$php_extensions['mbstring'] = [];
$php_extensions['fileinfo'] = [];
$php_extensions['exif'] = [];
$php_extensions['zip'] = [];
$php_extensions['json'] = [];

foreach($php_extensions as $extension_name => $value) {
    $status = 'OK';
    if (!extension_loaded($extension_name)) {
        $status =  'FAIL: Can not load PHP Extension ('.$extension_name.')';
    }
    printf('<tr><td>PHP require extension %s:</td><td>%s</td></tr>',$extension_name,$status);
}

$status = 'OK';
if (ini_get('allow_url_fopen') != 1) {
    $status =  'FAIL: php.ini, Must set allow_url_fopen=ON';
}
printf('<tr><td>PHP allow_url_fopen:</td><td>%s</td></tr>',$status);

$status = 'OK';
$current_memory_limit = '';
if(preg_match('/([0-9]+)/',ini_get('memory_limit'),$match)){
    $current_memory_limit = $match[0];
    if($match[0] < 64) {        
        $status =  'FAIL: php.ini, Set Memory limit at least 64M.';
        $status .="PHP memory_limit false ".$match[0];
    }
}
$status .= ' ( Current memory_limit=' . $current_memory_limit . ' )';
printf('<tr><td>PHP memory_limit at least 64M:</td><td>%s</td></tr>',$status);


$status = 'OK';
if(! function_exists('posix_getpwuid')){
    $status =  'Can not load PHP Function (posix_getpwuid)';
}
printf('<tr><td>PHP require function posix_getpwuid:</td><td>%s</td></tr>',$status);

echo '</table>';

phpinfo();

?>