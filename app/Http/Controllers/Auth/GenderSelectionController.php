<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GenderSelectionController extends Controller
{
    /**
     * Display the gender selection view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/GenderSelection');
    }

    /**
     * Handle the gender selection submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'gender' => 'required|string|in:male,female,non-binary,other',
        ]);

        $request->user()->update([
            'gender' => $request->gender,
        ]);

        return redirect()->route('dashboard');
    }
}
