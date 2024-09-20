<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the home page.
     *
     * @return \Illuminate\View\View
     */
    public function home()
    {
        /* Set the title for the home page */
        $data = ['title' => 'Networked'];

        /* Render the 'home' view with the provided data */
        return view('front.home', $data);
    }

    /**
     * Display the about page.
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        /* Set the title for the about page */
        $data = ['title' => 'About Us - Networked'];

        /* Render the 'about' view with the provided data */
        return view('front.about', $data);
    }

    /**
     * Display the pricing page.
     *
     * @return \Illuminate\View\View
     */
    public function pricing()
    {
        /* Set the title for the pricing page */
        $data = ['title' => 'Pricing - Networked'];

        /* Render the 'pricing' view with the provided data */
        return view('front.pricing', $data);
    }

    /**
     * Display the FAQ page.
     *
     * @return \Illuminate\View\View
     */
    public function faq()
    {
        /* Define the data to be passed to the FAQ view */
        $data = ['title' => 'FAQs - Networked'];

        /* Render the 'faq' view with the title data */
        return view('front.faq', $data);
    }
}
