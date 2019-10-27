<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Abstracts\AbstractController;

/**
 * IndexController Class responsible for all actions related to index page
 * @package App\Http\Controllers\Web
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class IndexController extends AbstractController
{
    /**
     * Index page action
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function indexAction()
    {
        return view("front.index");
    }

    /**
     * Display login page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function displayLoginPage()
    {
        return view("front.login");
    }
}