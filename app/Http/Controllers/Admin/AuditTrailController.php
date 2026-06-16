<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')
            ->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('causer', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($event = $request->input('event')) {
            $query->where('description', $event);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $activities = $query->paginate(20)->withQueryString();

        $activities->getCollection()->transform(function (Activity $activity) {
            $modelName = class_exists($activity->subject_type)
                ? class_basename($activity->subject_type)
                : $activity->subject_type;

            $properties = $activity->properties->toArray();
            $old        = $properties['old'] ?? null;
            $new        = $properties['attributes'] ?? null;

            // Strip sensitive keys
            $sensitiveKeys = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];
            if ($old) {
                $old = array_diff_key($old, array_flip($sensitiveKeys));
            }
            if ($new) {
                $new = array_diff_key($new, array_flip($sensitiveKeys));
            }

            return [
                'id'           => $activity->id,
                'event'        => $activity->description,
                'model'        => $modelName,
                'subject_id'   => $activity->subject_id,
                'causer'       => $activity->causer ? [
                    'id'   => $activity->causer->id,
                    'name' => $activity->causer->name,
                ] : null,
                'old'          => $old,
                'new'          => $new,
                'ip_address'   => $activity->ip_address,
                'user_agent'   => $activity->user_agent,
                'created_at'   => $activity->created_at->toISOString(),
            ];
        });

        $stats = [
            'total'   => Activity::count(),
            'created' => Activity::where('description', 'created')->count(),
            'updated' => Activity::where('description', 'updated')->count(),
            'deleted' => Activity::where('description', 'deleted')->count(),
        ];

        return Inertia::render('Admin/AuditTrail/Index', [
            'activities' => $activities,
            'stats'      => $stats,
            'filters'    => $request->only(['search', 'event', 'from', 'to']),
        ]);
    }
}
