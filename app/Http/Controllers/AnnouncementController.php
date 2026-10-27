<?php

namespace App\Http\Controllers;

use App\Announcements\AnnouncementCatalog;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(private readonly AnnouncementCatalog $announcements) {}

    public function show(int $announcement): View
    {
        $visible = $this->announcements->findVisible($announcement, now());

        if ($visible->isErr()) {
            abort(404);
        }

        return view('announcements.show', [
            'announcement' => $visible->get(),
        ]);
    }
}
