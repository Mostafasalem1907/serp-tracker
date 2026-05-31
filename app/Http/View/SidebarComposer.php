<?php

namespace App\Http\View;

use App\Models\Client;
use App\Models\Project;
use Illuminate\View\View;

class SidebarComposer
{
    /**
     * تمرير بيانات الـ Sidebar لكل الـ views
     * العملاء والمشاريع تُجلب مرة واحدة لكل request
     */
    public function compose(View $view): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        // العملاء — للـ admin فقط
        $sidebarClients = [];
        if ($user->isAdmin()) {
            $sidebarClients = Client::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        // المشاريع — حسب الدور
        if ($user->isAdmin()) {
            $sidebarProjects = Project::where('is_active', true)
                ->with('client:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'client_id', 'domain']);
        } elseif ($user->isMember()) {
            $sidebarProjects = $user->assignedProjects()
                ->where('is_active', true)
                ->with('client:id,name')
                ->orderBy('name')
                ->get(['projects.id', 'projects.name', 'projects.client_id', 'projects.domain']);
        } else {
            $sidebarProjects = Project::where('client_id', $user->client_id)
                ->where('is_active', true)
                ->with('client:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'client_id', 'domain']);
        }

        $view->with(compact('sidebarClients', 'sidebarProjects'));
    }
}
