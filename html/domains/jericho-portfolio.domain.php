<?php

use eru123\router\Router;

$portfolio = new Router();
$portfolio->get('/', function () {
    Router::status_page(200, 'Welcome!', 'Hi there! This is jericho-portfolio, a portfolio website for Jericho. <br /> Currently, this website is under construction, please come back later.');
});

return $portfolio;
