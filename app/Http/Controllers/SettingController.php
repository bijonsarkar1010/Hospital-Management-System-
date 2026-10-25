<?php

namespace App\Http\Controllers;

use App\Models\ClinicSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Show clinic settings.
     */
    public function index()
    {
        $settings = [
            'clinic_name' => ClinicSetting::get('clinic_name', 'CarePoint Outpatient Medical Clinic'),
            'clinic_tagline' => ClinicSetting::get('clinic_tagline', 'Advanced Family & Specialty Healthcare'),
            'clinic_phone' => ClinicSetting::get('clinic_phone', '+1 (555) 234-5678'),
            'clinic_email' => ClinicSetting::get('clinic_email', 'reception@carepointclinic.com'),
            'clinic_address' => ClinicSetting::get('clinic_address', '742 Evergreen Healthcare Blvd, Suite 300'),
            'clinic_open_time' => ClinicSetting::get('clinic_open_time', '08:00'),
            'clinic_close_time' => ClinicSetting::get('clinic_close_time', '20:00'),
            'booking_horizon_days' => ClinicSetting::get('booking_horizon_days', '30'),
            'default_consultation_duration' => ClinicSetting::get('default_consultation_duration', '30'),
        ];

        return view('settings.index', compact('settings'));
    }

    /**
     * Update clinic settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'clinic_name' => ['required', 'string', 'max:150'],
            'clinic_tagline' => ['nullable', 'string', 'max:250'],
            'clinic_phone' => ['required', 'string', 'max:50'],
            'clinic_email' => ['required', 'email', 'max:150'],
            'clinic_address' => ['required', 'string', 'max:300'],
            'clinic_open_time' => ['required', 'date_format:H:i'],
            'clinic_close_time' => ['required', 'date_format:H:i', 'after:clinic_open_time'],
            'booking_horizon_days' => ['required', 'integer', 'min:1', 'max:90'],
            'default_consultation_duration' => ['required', 'integer', 'min:5', 'max:120'],
        ]);

        foreach ($validated as $key => $value) {
            ClinicSetting::set($key, (string)$value);
        }

        return redirect()->route('settings.index')
            ->with('success', 'Clinic settings updated successfully.');
    }
}
