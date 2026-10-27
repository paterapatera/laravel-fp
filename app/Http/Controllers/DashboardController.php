<?php

namespace App\Http\Controllers;

use App\Announcements\AnnouncementCatalog;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly AnnouncementCatalog $announcements) {}

    public function index(): View
    {
        return view('dashboard.index', [
            'announcements' => $this->announcements->visible(now())->get(),
        ]);
    }
}
