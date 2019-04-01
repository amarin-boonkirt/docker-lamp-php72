<?php

//phpinfo();
//exit;

/**
 * Laravel - A PHP Framework For Web Artisans.
 *
 * @author   Taylor Otwell <taylor@laravel.com>
 */
define('LARAVEL_START', microtime(true));

if(file_exists('../storage/devmode')){
    ini_set('error_reports',"E_ALL");
    ini_set('display_errors',"On");
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/

$domainName = getDomainName();
$mainHome = getMainHome();

#TODO virtualhost homepath (find /home/amarin)
//require __DIR__.'/../vendor/autoload.php';
set_include_path('.:..:/opt/cpanel/ea-php71/root/usr/share/pear');

$domainAppPath = $mainHome . '/rvsitebuildercms/' . $domainName;

if(file_exists($domainAppPath . '/vendor/autoload.php'))
{

    // Should load as a first autoload to allow package to have its own vendor package
    // but different version than the framework vendor
    require $domainAppPath . '/vendor/autoload.php';
    
//  ไม่น่าจะใช้แล้ว
//
//     $homePath = dirname($_SERVER["DOCUMENT_ROOT"] . '../');
//     $custom_vendor_path = $homePath.'/storage/user_vendor';
//     #	echo "\$custom_vendor_path = $custom_vendor_path ===";
//     if(file_exists_include_path('rvsitebuildercms/storage/user_vendor/user_autoload.php'))
//     {
//         require 'rvsitebuildercms/storage/user_vendor/user_autoload.php';
//         $customLoader = new CustomVendorAutoload();
//         $loader = $customLoader->getLoader($custom_vendor_path,$loader);
//     }

    //change path app_path/rvsitebuildercms/storage/packages to app_path/rvsitebuildercms/packages
    $packagesPath = $domainAppPath . '/packages';    
    $vendor_names = scandir($packagesPath);
    foreach($vendor_names as $vendor_name){
        if($vendor_name === '.' || $vendor_name === '..') {continue;}
        $package_names = scandir($packagesPath . '/' . $vendor_name);
        foreach($package_names as $package_name){
            if($package_name === '.' || $package_name === '..') {continue;}
            $auto_load_file = $packagesPath . '/' . $vendor_name . '/' . $package_name . '/vendor/autoload.php';
            if(is_file($auto_load_file)){
                require $auto_load_file;
            }                         
        }
    }    
    
} else {
    echo '<h1>Not found RVSiteBuilder CMS App in ' . get_include_path() . '<h1>';
    echo '<pre>
Please contact your provider with info:
1. RVSiteBuilder CMS App have not install.
2. Not found vendor path.
3. Your PHP version not compatible, Our system require PHP 7.1+
</pre>';
    exit;
}


/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

//$app = require_once __DIR__.'/../bootstrap/app.php';
$app = require_once $domainAppPath . '/bootstrap/app.php';


/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);

function file_exists_include_path($include_file){
    $included_path_lists = explode(':',get_include_path());
    $found = 0;
    foreach ($included_path_lists as $include_path) {
        if(file_exists($include_path . '/' . $include_file)){
            $found = 1;
            break;
        }
    }
    return $found;
}

function getMainHome() {
    $mainHome = '';
    if(php_sapi_name() === 'cli'){
        if (posix_getuid() != 0){
            $testPathInput = dirname(__FILE__);
        }else{
            return $mainHome;
        }
    }else{
        $testPathInput = $_SERVER['DOCUMENT_ROOT'];
    }
    
    // case 1: devmmode
    if(file_exists($testPathInput .'/../storage/devmode')){
        return dirname($testPathInput . '../');
    }
    
    // case 2: have posix_getpwuid get uid by owner dir
    if(function_exists('posix_getpwuid')){
        $stat = stat($testPathInput);
        $uid = $stat['uid'];
        $userinfo = posix_getpwuid($uid);
        if(is_dir($userinfo['dir'])){
            return $userinfo['dir'];
        }
    }
    
    // case 3: cpanel have rvsitebuildercms dir in home
    $paths = preg_split("/\//", $testPathInput);
    $loopDim = count($paths);    
    for($i=0; $i < $loopDim; $i++) {
        $testPath = join('/', $paths);
        if(is_dir($testPath . '/rvsitebuildercms'))
        {
            $mainHome = $testPath;
            break;
        }
        array_pop($paths);
    }
    if($mainHome != ''){
        return $mainHome;
    }
    
    // case 4: other ../
    $mainHome = realpath($testPathInput . '/../');
    
    return $mainHome;
}

function getDomainName() {
    $domainName = '';
    
    //case: devmmode
    if(file_exists(__DIR__ .'/../storage/devmode')){
        return $domainName;
    }
    
    if(php_sapi_name() === 'cli'){
        if (posix_getuid() != 0){
            $paths = preg_split("/\//", __DIR__);
            $domainName = array_pop($paths);
        }        
    }else{
        $parts = parse_url($_SERVER['HTTP_HOST']);
        if(isset($parts['path'])){
            $domainName = $parts['path'];
        }
        if(isset($parts['host'])){
            $domainName = $parts['host'];
        }
    }
    
    return $domainName;
}

function getFrameworkVendorPath($filePath = ''){
    $vendorDir = realpath(dirname($filePath) . '/../../../../../') . '/vendor';
    return $vendorDir;
}

function getPackageBaseDir($filePath = ''){
    $baseDir = realpath(dirname($filePath) . '/../../');
    return $baseDir;
}

