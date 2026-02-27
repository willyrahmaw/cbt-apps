<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $academicYear = Setting::getAcademicYear();
        return view('admin.settings.index', compact('academicYear'));
    }

    public function updateAcademicYear(Request $request)
    {
        $request->validate([
            'academic_year' => 'required|string|regex:/^\d{4}\/\d{4}$/',
        ], [
            'academic_year.regex' => 'Format tahun ajaran harus YYYY/YYYY (misal: 2025/2026)',
        ]);

        Setting::setAcademicYear($request->academic_year);

        return back()->with('success', 'Tahun ajaran berhasil diubah.');
    }

    public function website()
    {
        $siteName = Setting::get('site_name', 'CBT App');
        $siteDescription = Setting::get('site_description', 'Computer Based Test');
        $siteFooter = Setting::get('site_footer', '');
        $siteLogo = Setting::get('site_logo', '');
        $siteFavicon = Setting::get('site_favicon', '');
        $siteTimezone = Setting::get('site_timezone', 'Asia/Jakarta');
        return view('admin.settings.website', compact('siteName', 'siteDescription', 'siteFooter', 'siteLogo', 'siteFavicon', 'siteTimezone'));
    }

    public function updateWebsite(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:100',
            'site_description' => 'nullable|string|max:255',
            'site_footer' => 'nullable|string|max:500',
            'site_timezone' => 'required|string|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura',
            'site_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:1024',
            'site_favicon' => 'nullable|file|mimes:ico,png|max:512',
        ]);

        Setting::set('site_name', $request->site_name);
        Setting::set('site_description', $request->site_description ?? '');
        Setting::set('site_footer', $request->site_footer ?? '');
        Setting::set('site_timezone', $request->site_timezone);

        if ($request->hasFile('site_favicon')) {
            $favicon = $request->file('site_favicon')->store('settings', 'public');
            $old = Setting::get('site_favicon');
            if ($old) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($old);
            }
            Setting::set('site_favicon', $favicon);
        } elseif ($request->boolean('remove_favicon')) {
            $old = Setting::get('site_favicon');
            if ($old) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($old);
            }
            Setting::set('site_favicon', '');
        }

        if ($request->hasFile('site_logo')) {
            $logo = $request->file('site_logo')->store('settings', 'public');
            $old = Setting::get('site_logo');
            if ($old) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($old);
            }
            Setting::set('site_logo', $logo);
        } elseif ($request->boolean('remove_logo')) {
            $old = Setting::get('site_logo');
            if ($old) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($old);
            }
            Setting::set('site_logo', '');
        }

        return back()->with('success', 'Pengaturan website berhasil disimpan.');
    }
}
