<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\View\View;

class NewsletterSubscriberController extends Controller
{
    /**
     * Display a listing of the newsletter subscribers.
     */
    public function index(): View
    {
        $subscribers = NewsletterSubscriber::orderByDesc('subscribed_at')->paginate(15);

        return view('admin.newsletter-subscribers.index', compact('subscribers'));
    }
}
