<?php
//Home
$app->get('/', 'HomeController:index')->setName('getHome')->add(new \App\Middlewares\InstallMiddleware($container));

//View Image
$app->get('/i[/{key}]', 'HomeController:viewimg')->setName('getViewImage')->add(new \App\Middlewares\InstallMiddleware($container));


//Post for report
$app->get('/report[/{key}]', 'HomeController:getReport')->setName('getReport')->add(new \App\Middlewares\InstallMiddleware($container));
$app->post('/report', 'HomeController:postReport')->setName('postReport');

//Post for upload
$app->post('/upload', 'UploadController:index');

//Auth
$app->group('/auth', function() {
	//Login
	$this->get('/login', 'AuthController:getLogin')->setName('getLogin');
	$this->post('/login', 'AuthController:postLogin')->setName('postLogin');
	//Recover
	$this->get('/recover', 'AuthController:getRecover')->setName('getRecover');
	$this->post('/recover', 'AuthController:postRecover')->setName('postRecover');
	//Register
	$this->get('/register', 'AuthController:getRegister')->setName('getRegister');
	$this->post('/register', 'AuthController:postRegister')->setName('postRegister');
	//TOS
	$this->get('/tos', 'AuthController:tos')->setName('tos');
	//GetKey
	$this->get('/key[/{key}]', 'AuthController:getChange')->setName('getChange');

})->add(new \App\Middlewares\AuthMiddleware($container))->add(new \App\Middlewares\InstallMiddleware($container));

//Dashboard
$app->group('/user', function() {
	//User Index
	$this->get('/images', 'DashController:index')->setName('getAccount');

	//User Settings
	$this->get('/settings', 'DashController:getSettings')->setName('getAccountSettings');

	//Logout
	$this->get('/logout', 'AuthController:logout')->setName('getLogout');

	//Settings Update 
	$this->post('/update', 'DashController:postUpdate')->setName('postUpdate');

	//Delete
	$this->get('/delete[/{key}]', 'DashController:getDelete')->setName('getDelete');

})->add(new \App\Middlewares\UserMiddleware($container))->add(new \App\Middlewares\InstallMiddleware($container));

//Install
$app->get('/install', 'InstallController:index');
$app->group('/install', function() {
	//Index Install
	$this->get('/welcome', 'InstallController:welcome')->setName('getInstall');

	//Install Database
	$this->get('/step/1', 'InstallController:getStep1')->setName('getStep1');

	//Add Admin Account
	$this->get('/step/2', 'InstallController:getStep2')->setName('getStep2');
	$this->post('/step/2', 'InstallController:postStep2')->setName('postStep2');

	//Set the website Settings
	$this->get('/step/3', 'InstallController:getStep3')->setName('getStep3');
	$this->post('/step/3', 'InstallController:postStep3')->setName('postStep3');

	//Finish
	$this->get('/finish', 'InstallController:finish')->setName('finish');
});



//Admin
$app->group('/admin', function() {
	//Admin Home page
	$this->get('/index', 'AdminController:index')->setName('AdminHome');
	$this->post('/search', 'AdminController:postSearch')->setName('postSearch');

	//Edit user
	$this->get('/user[/{key}]', 'AdminController:getEdit')->setName('getAdminEdit');
	$this->post('/edit', 'AdminController:postEdit')->setName('postAdminEdit');

	//Ban & Unban user
	$this->get('/ban', 'AdminController:banUser')->setName('getBan');
	$this->get('/ban/{key}', 'AdminController:banUser');

	$this->get('/unban', 'AdminController:unBanUser')->setName('getUnban');
	$this->get('/unban/{key}', 'AdminController:unBanUser');

	//SEO
	$this->get('/seo', 'AdminController:getSEO')->setName('getAdminSeo');
	$this->post('/seo', 'AdminController:postSEO')->setName('postSEO');

	//Upload Settings
	$this->get('/upload', 'AdminController:getUpload')->setName('getAdminUpload');
	$this->post('/upload', 'AdminController:postUpload')->setName('postAdminUpload');

	//Reports
	$this->get('/reports[/{id}]', 'AdminController:getReport')->setName('getAdminReport');

	//Close Repost
	$this->get('/close[/{id}]', 'AdminController:getClose')->setName('getReportClose');


})->add(new \App\Middlewares\InstallMiddleware($container))->add(new \App\Middlewares\AdminMiddleware($container));