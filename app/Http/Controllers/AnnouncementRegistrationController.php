<?php

namespace App\Http\Controllers;

use App\Announcements\AnnouncementRegistrar;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AnnouncementRegistrationController extends Controller
{
    public function __construct(private readonly AnnouncementRegistrar $registrar) {}

    public function create(Request $request): View|Response
    {
        $denied = $this->denied($request);

        if ($denied instanceof Response) {
            return $denied;
        }

        return view('announcements.register');
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $denied = $this->denied($request);

        if ($denied instanceof Response) {
            return $denied;
        }

        $title = $this->text($request, 'title');
        $summary = $this->text($request, 'summary');
        $body = $this->text($request, 'body');
        $result = $this->registrar->register($title, $summary, $body);

        if ($result->isErr()) {
            $fields = $result->error();

            return redirect()
                ->route('announcements.register.create')
                ->withInput([
                    'title' => $title,
                    'summary' => $summary,
                    'body' => $body,
                ])
                ->withErrors($this->messages(is_array($fields) ? $fields : []));
        }

        return redirect()
            ->route('announcements.register.created')
            ->with('registeredTitle', $result->get()->title);
    }

    public function created(Request $request): View|Response
    {
        $denied = $this->denied($request);

        if ($denied instanceof Response) {
            return $denied;
        }

        $title = $request->session()->get('registeredTitle');

        return view('announcements.registered', [
            'title' => is_string($title) ? $title : '',
        ]);
    }

    private function denied(Request $request): ?Response
    {
        $user = $request->user();

        if (! $user instanceof User || $user->admin !== true) {
            return response('', 404);
        }

        return null;
    }

    private function text(Request $request, string $key): string
    {
        $value = $request->input($key);

        return is_string($value) ? $value : '';
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, string>
     */
    private function messages(array $fields): array
    {
        $messages = [
            'title' => '題名がありません',
            'summary' => '要約がありません',
            'body' => '本文がありません',
        ];

        $errors = [];

        foreach ($fields as $field) {
            if (isset($messages[$field])) {
                $errors[$field] = $messages[$field];
            }
        }

        return $errors;
    }
}
