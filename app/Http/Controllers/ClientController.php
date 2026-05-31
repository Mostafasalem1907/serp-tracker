<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * قائمة العملاء
     */
    public function index()
    {
        $clients = Client::withCount('projects')
            ->orderBy('name')
            ->paginate(15);

        return view('clients.index', compact('clients'));
    }

    /**
     * نموذج إضافة عميل جديد
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * حفظ عميل جديد
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $client = Client::create($data);

        ActivityLog::record('client.created', $client, [], $client->toArray(), 'إضافة عميل جديد');

        return redirect()->route('clients.show', $client)->with('success', 'تم إضافة العميل بنجاح');
    }

    /**
     * عرض تفاصيل العميل مع مشاريعه وسجل أنشطته
     */
    public function show(Client $client)
    {
        $client->load('projects.country');

        $activityLogs = \App\Models\ActivityLog::where('model_type', 'Client')
            ->where('model_id', $client->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('clients.show', compact('client', 'activityLogs'));
    }

    /**
     * نموذج تعديل العميل
     */
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * تحديث بيانات العميل
     */
    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'nullable|email|max:255',
            'phone'     => 'nullable|string|max:50',
            'notes'     => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $oldValues = $client->toArray();
        $client->update($data);

        ActivityLog::record('client.updated', $client, $oldValues, $client->fresh()->toArray());

        return redirect()->route('clients.show', $client)->with('success', 'تم تحديث بيانات العميل');
    }

    /**
     * حذف العميل (soft delete)
     */
    public function destroy(Client $client)
    {
        ActivityLog::record('client.deleted', $client, $client->toArray(), [], 'حذف العميل');
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'تم حذف العميل');
    }
}
