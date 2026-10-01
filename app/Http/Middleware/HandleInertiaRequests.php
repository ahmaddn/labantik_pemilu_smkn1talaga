<?php

namespace App\Http\Middleware;

use App\Models\AppSettingEvote;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $appName = AppSettingEvote::getValue('app_name', 'E-Voting SMKN 1 Talaga');

        return [
            ...parent::share($request),
            'name' => $appName,
            'auth' => [
                'user' => $request->user(),
            ],
            'appSettings' => [
                'app_name' => $appName,
                'app_description' => AppSettingEvote::getValue('app_description', 'Sistem Pemilihan Umum E-Voting SMKN 1 Talaga'),
                'active_academic_year' => AppSettingEvote::getValue('active_academic_year', '2025/2026'),
                'app_logo' => AppSettingEvote::getValue('app_logo'),
                'app_favicon' => AppSettingEvote::getValue('app_favicon'),
            ],
        ];
    }
}
