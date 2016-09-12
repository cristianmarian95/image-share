<?php
// Initialize the Container
$container = $app->getContainer();

// DB
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['database']);
$capsule->setAsGlobal();
$capsule->bootEloquent();


// Upload
$container['upload'] = function () {
    return new \App\Helpers\Upload();
};
// Session
$container['session'] = function (){
    return new \App\Helpers\Session();
};

// Flash Messages
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

// Validation 
$container['v'] = function () {
    return new \Violin\Violin;
};

// PHP Mailer
$container['mail'] = function (){
    return new \PHPMailer;
};

// Database
$container['db'] = function ($c) use ($capsule){
    return $capsule;
};

// Twig View
$container['view'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    $view = new \Slim\Views\Twig($settings['template_path']);
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new \Slim\Views\TwigExtension($c['router'], $basePath));
    $view->getEnvironment()->addGlobal('url', $c['request']->getUri()->getBaseUrl());
    if($c['session']->get('admin')){
        if($c['db']->schema()->hasTable('users')){
            $view->getEnvironment()->addGlobal('admin', $c['session']->get('admin'));
            $account = $c['db']->table('users')->where('id', '=', $c['session']->get('admin'))->first();
            $view->getEnvironment()->addGlobal('account', $account);
        }
    }
    if($c['session']->get('uid')){
        if($c['db']->schema()->hasTable('users')){
            $view->getEnvironment()->addGlobal('user', $c['session']->get('uid'));
            $account = $c['db']->table('users')->where('id', '=', $c['session']->get('uid'))->first();
            $view->getEnvironment()->addGlobal('account', $account);
        }
    }
    if($c['db']->schema()->hasTable('settings')){
        $s = $c['db']->table('settings')->where('id', '=', '1')->first();
        $view->getEnvironment()->addGlobal('s', $s);
    }
    $view->getEnvironment()->addGlobal('flash', $c['flash']);
    return $view;
};

// Monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new \Monolog\Logger($settings['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], \Monolog\Logger::DEBUG));
    return $logger;
};

// Controllers
$container['HomeController'] = function ($c) {
    return new \App\Controllers\HomeController($c);
};
$container['DashController'] = function ($c) {
    return new \App\Controllers\DashController($c);
};
$container['AuthController'] = function ($c) {
    return new \App\Controllers\AuthController($c);
};
$container['UploadController'] = function ($c) {
    return new \App\Controllers\UploadController($c);
};
$container['AdminController'] = function ($c) {
    return new \App\Controllers\AdminController($c);
};
$container['InstallController'] = function ($c) {
    return new \App\Controllers\InstallController($c);
};