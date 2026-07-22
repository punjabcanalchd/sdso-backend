<?php

namespace Modules\Admin\Services;

use App\Models\User;
use App\Models\AdminPage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class AdminPagePermissionService
{
    /**
     * Get permission map for the user grouped by page slug.
     * Pages with no permissions are omitted.
     * Cached with a TTL of 3600 seconds.
     */
    public function getPermissionMapForUser(User $user): array
    {
        return Cache::remember("user_permissions_map_{$user->id}", 3600, function () use ($user) {
            $permissions = $user->getAllPermissions();
            $map = [];

            foreach ($permissions as $permission) {
                $name = $permission->name;
                $pos = strrpos($name, '.');

                if ($pos !== false) {
                    $slug = substr($name, 0, $pos);
                    $action = substr($name, $pos + 1);

                    if (!isset($map[$slug])) {
                        $map[$slug] = [];
                    }

                    if (!in_array($action, $map[$slug], true)) {
                        $map[$slug][] = $action;
                    }
                }
            }

            return $map;
        });
    }

    /**
     * Return the list of actions the user can perform on a given page slug.
     * Example return: ['read', 'update']
     */
    public function getAllowedActionsForUser(User $user, string $pageSlug): array
    {
        $map = $this->getPermissionMapForUser($user);
        return $map[$pageSlug] ?? [];
    }

    /**
     * Determine if the user can view a given admin page.
     * Checks if the user's permission map contains the 'read' action for the page.
     * Bypassed if the user has the Super Admin role.
     */
    public function userCanViewPage(User $user, AdminPage $page): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        $map = $this->getPermissionMapForUser($user);

        return isset($map[$page->slug]) && in_array('read', $map[$page->slug], true);
    }

    /**
     * Clear cached permission map for a specific user ID.
     */
    public static function clearCacheForUser(int $userId): void
    {
        Cache::forget("user_permissions_map_{$userId}");
    }

    /**
     * Clear cached permission maps for all users assigned to a given role.
     */
    public static function clearCacheForRole($role): void
    {
        if (!$role) {
            return;
        }

        // Spatie Role has a users relation (belongsToMany)
        $userIds = $role->users()->pluck('id');
        foreach ($userIds as $userId) {
            self::clearCacheForUser($userId);
        }
    }

    /**
     * Fetch, hierarchy-construct, and recursively filter the admin pages.
     */
    public function getSidebarTree(?User $user = null): array
    {
        $user = $user ?? auth('api')->user();
        if (!$user) {
            return [];
        }

        // Fetch all active pages ordered by sort_order
        $allPages = AdminPage::where('status', 1)->orderBy('sort_order')->get();

        // Group by parent_id
        $grouped = $allPages->groupBy('parent_id');

        // Build parent-child relationships in memory
        foreach ($allPages as $page) {
            $page->setRelation('children', $grouped->get($page->id, collect()));
        }

        // Roots are where parent_id is null
        $roots = $allPages->whereNull('parent_id');

        // Recursively filter the tree starting from roots
        $filteredRoots = $this->filterTree($roots, $user);

        return $filteredRoots->values()->toArray();
    }

    /**
     * Recursively filter pages collection.
     */
    private function filterTree($pages, User $user)
    {
        $filtered = collect();

        foreach ($pages as $page) {
            // Filter children first
            if ($page->children && $page->children->isNotEmpty()) {
                $filteredChildren = $this->filterTree($page->children, $user);
                $page->setRelation('children', $filteredChildren);
            }

            // Keep node if Gate::forUser($user)->allows('view-admin-page', $page) passes
            // OR at least one filtered child survived
            $hasVisibleChildren = $page->children && $page->children->isNotEmpty();
            if (Gate::forUser($user)->allows('view-admin-page', $page) || $hasVisibleChildren) {
                $filtered->push($page);
            }
        }

        return $filtered;
    }
}
