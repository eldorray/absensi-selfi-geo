<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\StudentReferral;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyReferralController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->activeHomeroomAssignment(), 403, 'Anda tidak memiliki penugasan wali kelas aktif.');

        $referrals = StudentReferral::query()
            ->where('created_by', $request->user()->id)
            ->with(['student.schoolClass', 'counselor'])
            ->latest()
            ->paginate(15);

        return view('attendance.kesiswaan.my-referrals', compact('referrals'));
    }
}
