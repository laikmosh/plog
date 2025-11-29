<?php

namespace Laikmosh\Plog\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Laikmosh\Plog\Models\PlogEntry;
use Illuminate\Support\Facades\Gate;

class PlogViewer extends Component
{
    use WithPagination;

    public $search = '';
    public $level = '';
    public $userFilter = '';
    public $requestIds = [];
    public $sessionIds = [];
    public $environment = '';
    public $endpoints = [];
    public $tags = [];
    public $locations = [];
    public $classMethods = [];
    public $dateFrom = '';
    public $dateTo = '';
    public $groupBy = '';
    public $showUserDropdown = false;
    public $showRequestDropdown = false;
    public $showSessionDropdown = false;
    public $showTagDropdown = false;
    public $showEndpointDropdown = false;
    public $selectedEntry = null;
    public $activeContextTab = 'request_data';
    public $contextLogs = [];
    public $contextCounts = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'level' => ['except' => ''],
        'userFilter' => ['except' => ''],
        'requestIds' => ['except' => []],
        'sessionIds' => ['except' => []],
        'environment' => ['except' => ''],
        'endpoints' => ['except' => []],
        'tags' => ['except' => []],
        'locations' => ['except' => []],
        'classMethods' => ['except' => []],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        if (!Gate::allows('viewPlog')) {
            abort(403, 'Unauthorized');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function filterByLevel($level)
    {
        $this->level = $level;
        $this->resetPage();
    }

    public function filterByUser($userId)
    {
        $this->userFilter = $userId;
        $this->resetPage();
    }

    public function filterByRequest($requestId)
    {
        $this->requestId = $requestId;
        $this->resetPage();
    }

    public function filterBySession($sessionId)
    {
        $this->sessionId = $sessionId;
        $this->resetPage();
    }

    public function filterByEnvironment($environment)
    {
        $this->environment = $environment;
        $this->resetPage();
    }

    public function filterByEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        $this->resetPage();
    }

    public function filterByTag($tag)
    {
        $this->tag = $tag;
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset([
            'search', 'level', 'userFilter', 'requestIds',
            'sessionIds', 'environment', 'endpoints', 'tags',
            'locations', 'classMethods', 'dateFrom', 'dateTo'
        ]);
        $this->resetPage();
    }

    protected $listeners = ['filterUpdated'];

    public function filterUpdated($field, $values)
    {
        switch ($field) {
            case 'request_id':
                $this->requestIds = $values;
                break;
            case 'session_id':
                $this->sessionIds = $values;
                break;
            case 'endpoint':
                $this->endpoints = $values;
                break;
            case 'tags':
                $this->tags = $values;
                break;
            case 'location':
                $this->locations = $values;
                break;
            case 'class_method':
                $this->classMethods = $values;
                break;
        }
        $this->resetPage();
    }

    public function showEntry($entryId)
    {
        // Force complete reset
        $this->reset(['contextLogs', 'contextCounts']);

        $this->selectedEntry = PlogEntry::find($entryId);
        $this->activeContextTab = 'request_data';

        if ($this->selectedEntry) {
            $this->loadAllContextCounts();
            $this->loadContextualLogs();
        }

        // Force re-render
        $this->dispatch('$refresh');
    }

    public function closeEntry()
    {
        $this->selectedEntry = null;
        $this->contextLogs = [];
        $this->contextCounts = [];
        $this->activeContextTab = 'request_data';
    }

    public function setActiveContextTab($tab)
    {
        $this->activeContextTab = $tab;
        $this->loadContextualLogs();
    }

    public function loadAllContextCounts()
    {
        if (!$this->selectedEntry) return;

        // User context count
        $this->contextCounts['user'] = $this->selectedEntry->user_id
            ? PlogEntry::where('user_id', $this->selectedEntry->user_id)->count()
            : PlogEntry::whereNull('user_id')->count();

        // Request context count
        if ($this->selectedEntry->request_id) {
            $this->contextCounts['request'] = PlogEntry::where('request_id', $this->selectedEntry->request_id)->count();
        }

        // Session context count
        if ($this->selectedEntry->session_id) {
            $this->contextCounts['session'] = PlogEntry::where('session_id', $this->selectedEntry->session_id)->count();
        }

        // Endpoint context count
        if ($this->selectedEntry->endpoint) {
            $this->contextCounts['endpoint'] = PlogEntry::where('endpoint', $this->selectedEntry->endpoint)->count();
        }

        // Location context count
        if ($this->selectedEntry->file) {
            $this->contextCounts['location'] = PlogEntry::where('file', $this->selectedEntry->file)
                ->where('line', $this->selectedEntry->line)
                ->count();
        }

        // Class context count
        if ($this->selectedEntry->class) {
            $this->contextCounts['class'] = PlogEntry::where('class', $this->selectedEntry->class)
                ->where('method', $this->selectedEntry->method)
                ->count();
        }
    }

    public function loadContextualLogs()
    {
        if (!$this->selectedEntry) return;

        $this->contextLogs[$this->activeContextTab] = [
            'before' => [],
            'after' => [],
            'hasMoreBefore' => false,
            'hasMoreAfter' => false
        ];

        switch ($this->activeContextTab) {
            case 'user':
                $this->loadUserContextLogs();
                break;
            case 'request':
                $this->loadRequestContextLogs();
                break;
            case 'session':
                $this->loadSessionContextLogs();
                break;
            case 'endpoint':
                $this->loadEndpointContextLogs();
                break;
            case 'location':
                $this->loadLocationContextLogs();
                break;
            case 'class':
                $this->loadClassContextLogs();
                break;
            case 'request_data':
                $this->loadRequestData();
                break;
            case 'stack_trace':
                // Stack trace is already loaded with the entry, no additional loading needed
                break;
        }
    }

    protected function loadUserContextLogs($limitBefore = 5, $limitAfter = 5)
    {
        $userQuery = $this->selectedEntry->user_id
            ? PlogEntry::where('user_id', $this->selectedEntry->user_id)
            : PlogEntry::whereNull('user_id');

        // Get logs before this entry
        $before = $userQuery->where('created_at', '<', $this->selectedEntry->created_at)
            ->orderBy('created_at', 'desc')
            ->limit($limitBefore + 1)
            ->get();

        // Get logs after this entry
        $after = $userQuery->where('created_at', '>', $this->selectedEntry->created_at)
            ->orderBy('created_at', 'asc')
            ->limit($limitAfter + 1)
            ->get();

        $this->contextLogs['user'] = [
            'before' => $before->take($limitBefore)->values(),
            'after' => $after->take($limitAfter)->reverse()->values(),
            'hasMoreBefore' => $before->count() > $limitBefore,
            'hasMoreAfter' => $after->count() > $limitAfter
        ];

        // Get total count
        $this->contextCounts['user'] = $this->selectedEntry->user_id
            ? PlogEntry::where('user_id', $this->selectedEntry->user_id)->count()
            : PlogEntry::whereNull('user_id')->count();
    }

    protected function loadRequestContextLogs($limitBefore = 5, $limitAfter = 5)
    {
        if (!$this->selectedEntry->request_id) {
            $this->contextLogs['request'] = ['before' => [], 'after' => [], 'hasMoreBefore' => false, 'hasMoreAfter' => false];
            return;
        }

        $before = PlogEntry::where('request_id', $this->selectedEntry->request_id)
            ->where('created_at', '<', $this->selectedEntry->created_at)
            ->orderBy('created_at', 'desc')
            ->limit($limitBefore + 1)
            ->get();

        $after = PlogEntry::where('request_id', $this->selectedEntry->request_id)
            ->where('created_at', '>', $this->selectedEntry->created_at)
            ->orderBy('created_at', 'asc')
            ->limit($limitAfter + 1)
            ->get();

        $this->contextLogs['request'] = [
            'before' => $before->take($limitBefore)->values(),
            'after' => $after->take($limitAfter)->reverse()->values(),
            'hasMoreBefore' => $before->count() > $limitBefore,
            'hasMoreAfter' => $after->count() > $limitAfter
        ];
    }

    protected function loadSessionContextLogs($limitBefore = 5, $limitAfter = 5)
    {
        if (!$this->selectedEntry->session_id) {
            $this->contextLogs['session'] = ['before' => [], 'after' => [], 'hasMoreBefore' => false, 'hasMoreAfter' => false];
            return;
        }

        $before = PlogEntry::where('session_id', $this->selectedEntry->session_id)
            ->where('created_at', '<', $this->selectedEntry->created_at)
            ->orderBy('created_at', 'desc')
            ->limit($limitBefore + 1)
            ->get();

        $after = PlogEntry::where('session_id', $this->selectedEntry->session_id)
            ->where('created_at', '>', $this->selectedEntry->created_at)
            ->orderBy('created_at', 'asc')
            ->limit($limitAfter + 1)
            ->get();

        $this->contextLogs['session'] = [
            'before' => $before->take($limitBefore)->values(),
            'after' => $after->take($limitAfter)->reverse()->values(),
            'hasMoreBefore' => $before->count() > $limitBefore,
            'hasMoreAfter' => $after->count() > $limitAfter
        ];

    }

    protected function loadEndpointContextLogs($limitBefore = 5, $limitAfter = 5)
    {
        if (!$this->selectedEntry->endpoint) {
            $this->contextLogs['endpoint'] = ['before' => [], 'after' => [], 'hasMoreBefore' => false, 'hasMoreAfter' => false];
            return;
        }

        $before = PlogEntry::where('endpoint', $this->selectedEntry->endpoint)
            ->where('created_at', '<', $this->selectedEntry->created_at)
            ->orderBy('created_at', 'desc')
            ->limit($limitBefore + 1)
            ->get();

        $after = PlogEntry::where('endpoint', $this->selectedEntry->endpoint)
            ->where('created_at', '>', $this->selectedEntry->created_at)
            ->orderBy('created_at', 'asc')
            ->limit($limitAfter + 1)
            ->get();

        $this->contextLogs['endpoint'] = [
            'before' => $before->take($limitBefore)->values(),
            'after' => $after->take($limitAfter)->reverse()->values(),
            'hasMoreBefore' => $before->count() > $limitBefore,
            'hasMoreAfter' => $after->count() > $limitAfter
        ];

    }

    protected function loadLocationContextLogs($limitBefore = 5, $limitAfter = 5)
    {
        $file = $this->selectedEntry->file ?? 'unknown';
        $line = $this->selectedEntry->line;

        if (!$file || $file === 'unknown') {
            $this->contextLogs['location'] = ['before' => [], 'after' => [], 'hasMoreBefore' => false, 'hasMoreAfter' => false];
            $this->contextCounts['location'] = 0;
            return;
        }

        $before = PlogEntry::where('file', $file)
            ->where('line', $line)
            ->where('created_at', '<', $this->selectedEntry->created_at)
            ->orderBy('created_at', 'desc')
            ->limit($limitBefore + 1)
            ->get();

        $after = PlogEntry::where('file', $file)
            ->where('line', $line)
            ->where('created_at', '>', $this->selectedEntry->created_at)
            ->orderBy('created_at', 'asc')
            ->limit($limitAfter + 1)
            ->get();

        $this->contextLogs['location'] = [
            'before' => $before->take($limitBefore)->values(),
            'after' => $after->take($limitAfter)->reverse()->values(),
            'hasMoreBefore' => $before->count() > $limitBefore,
            'hasMoreAfter' => $after->count() > $limitAfter
        ];

        // Get total count
        $this->contextCounts['location'] = PlogEntry::where('file', $file)
            ->where('line', $line)
            ->count();
    }

    protected function loadClassContextLogs($limitBefore = 5, $limitAfter = 5)
    {
        $class = $this->selectedEntry->class ?? 'root';
        $method = $this->selectedEntry->method ?? 'main';

        $before = PlogEntry::where('class', $class)
            ->where('method', $method)
            ->where('created_at', '<', $this->selectedEntry->created_at)
            ->orderBy('created_at', 'desc')
            ->limit($limitBefore + 1)
            ->get();

        $after = PlogEntry::where('class', $class)
            ->where('method', $method)
            ->where('created_at', '>', $this->selectedEntry->created_at)
            ->orderBy('created_at', 'asc')
            ->limit($limitAfter + 1)
            ->get();

        $this->contextLogs['class'] = [
            'before' => $before->take($limitBefore)->values(),
            'after' => $after->take($limitAfter)->reverse()->values(),
            'hasMoreBefore' => $before->count() > $limitBefore,
            'hasMoreAfter' => $after->count() > $limitAfter
        ];

        // Get total count
        $this->contextCounts['class'] = PlogEntry::where('class', $class)
            ->where('method', $method)
            ->count();
    }

    protected function loadRequestData()
    {
        if (!$this->selectedEntry->request_id) {
            return;
        }

        // Load the request relationship if not already loaded
        if (!$this->selectedEntry->relationLoaded('request')) {
            $this->selectedEntry->load('request');
        }
    }

    public function loadMoreContextLogs($direction = 'after', $limit = 5)
    {
        if (!$this->selectedEntry) return;

        $currentContext = $this->contextLogs[$this->activeContextTab] ?? null;
        if (!$currentContext) return;

        $currentCount = $direction === 'before'
            ? count($currentContext['before'])
            : count($currentContext['after']);

        $newLimit = $currentCount + $limit;

        switch ($this->activeContextTab) {
            case 'user':
                $this->loadUserContextLogs(
                    $direction === 'before' ? $newLimit : 5,
                    $direction === 'after' ? $newLimit : 5
                );
                break;
            case 'request':
                $this->loadRequestContextLogs(
                    $direction === 'before' ? $newLimit : 5,
                    $direction === 'after' ? $newLimit : 5
                );
                break;
            case 'session':
                $this->loadSessionContextLogs(
                    $direction === 'before' ? $newLimit : 5,
                    $direction === 'after' ? $newLimit : 5
                );
                break;
            case 'endpoint':
                $this->loadEndpointContextLogs(
                    $direction === 'before' ? $newLimit : 5,
                    $direction === 'after' ? $newLimit : 5
                );
                break;
            case 'location':
                $this->loadLocationContextLogs(
                    $direction === 'before' ? $newLimit : 5,
                    $direction === 'after' ? $newLimit : 5
                );
                break;
            case 'class':
                $this->loadClassContextLogs(
                    $direction === 'before' ? $newLimit : 5,
                    $direction === 'after' ? $newLimit : 5
                );
                break;
        }
    }

    public function toggleUserDropdown()
    {
        $this->showUserDropdown = !$this->showUserDropdown;
        $this->hideOtherDropdowns('user');
    }

    public function selectUser($userId)
    {
        if ($userId === 'null' || $userId === null) {
            $this->userFilter = 'guest';
        } elseif ($userId === 'guest' || $userId === 'system') {
            $this->userFilter = $userId;
        } else {
            $this->userFilter = $userId;
        }
        $this->showUserDropdown = false;
        $this->resetPage();
    }

    public function hideOtherDropdowns($except = null)
    {
        if ($except !== 'user') $this->showUserDropdown = false;
        if ($except !== 'request') $this->showRequestDropdown = false;
        if ($except !== 'session') $this->showSessionDropdown = false;
        if ($except !== 'tag') $this->showTagDropdown = false;
        if ($except !== 'endpoint') $this->showEndpointDropdown = false;
    }

    public function getUserFilterDisplayProperty()
    {
        if ($this->userFilter === null || $this->userFilter === '') {
            return '';
        }
        if ($this->userFilter === 'guest') {
            return 'Guest Users';
        }
        if ($this->userFilter === 'system') {
            return 'System (CLI/Queue)';
        }
        return "User #{$this->userFilter}";
    }

    public function getRecentUsersProperty()
    {
        return PlogEntry::select('user_id')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByRaw('MAX(created_at) DESC')
            ->limit(10)
            ->pluck('user_id')
            ->map(function ($userId) {
                return [
                    'id' => $userId,
                    'label' => "User #{$userId}",
                    'count' => PlogEntry::where('user_id', $userId)->count()
                ];
            })
            ->prepend([
                'id' => 'system',
                'label' => 'System (CLI/Queue)',
                'count' => PlogEntry::whereNull('user_id')->whereIn('environment', ['cli', 'queue'])->count()
            ])
            ->prepend([
                'id' => 'guest',
                'label' => 'Guest Users',
                'count' => PlogEntry::whereNull('user_id')->whereNotIn('environment', ['cli', 'queue'])->count()
            ]);
    }

    public function getRecentRequestsProperty()
    {
        return PlogEntry::select('request_id', 'user_id', 'endpoint', 'created_at')
            ->whereNotNull('request_id')
            ->groupBy('request_id')
            ->orderByRaw('MAX(created_at) DESC')
            ->limit(15)
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->request_id,
                    'user_id' => $entry->user_id,
                    'endpoint' => $entry->endpoint,
                    'time' => $entry->created_at->format('M j, H:i'),
                    'count' => PlogEntry::where('request_id', $entry->request_id)->count()
                ];
            });
    }

    public function getRecentSessionsProperty()
    {
        return PlogEntry::select('session_id', 'user_id', 'created_at')
            ->whereNotNull('session_id')
            ->groupBy('session_id')
            ->orderByRaw('MAX(created_at) DESC')
            ->limit(15)
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->session_id,
                    'user_id' => $entry->user_id,
                    'time' => $entry->created_at->format('M j, H:i'),
                    'count' => PlogEntry::where('session_id', $entry->session_id)->count()
                ];
            });
    }

    public function getAvailableTagsProperty()
    {
        return PlogEntry::whereNotNull('tags')
            ->get()
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values();
    }

    public function getAvailableEndpointsProperty()
    {
        return PlogEntry::select('endpoint')
            ->whereNotNull('endpoint')
            ->groupBy('endpoint')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(20)
            ->get()
            ->map(function ($entry) {
                return [
                    'endpoint' => $entry->endpoint,
                    'count' => PlogEntry::where('endpoint', $entry->endpoint)->count()
                ];
            });
    }


    public function render()
    {
        $query = PlogEntry::query();

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->level) {
            $query->level($this->level);
        }

        if ($this->userFilter !== '' && $this->userFilter !== null) {
            if ($this->userFilter === 'guest') {
                $query->whereNull('user_id')->whereNotIn('environment', ['cli', 'queue']);
            } elseif ($this->userFilter === 'system') {
                $query->whereNull('user_id')->whereIn('environment', ['cli', 'queue']);
            } else {
                $query->user($this->userFilter);
            }
        }

        if (!empty($this->requestIds)) {
            $query->whereIn('request_id', $this->requestIds);
        }

        if (!empty($this->sessionIds)) {
            $query->whereIn('session_id', $this->sessionIds);
        }

        if ($this->environment) {
            $query->environment($this->environment);
        }

        if (!empty($this->endpoints)) {
            $query->whereIn('endpoint', $this->endpoints);
        }

        if (!empty($this->tags)) {
            foreach ($this->tags as $tag) {
                $query->whereJsonContains('tags', $tag);
            }
        }

        if (!empty($this->locations)) {
            $query->where(function($q) {
                foreach ($this->locations as $location) {
                    $parts = explode(':', $location);
                    if (count($parts) === 2) {
                        $q->orWhere(function($subQ) use ($parts) {
                            $subQ->where('file', $parts[0])->where('line', $parts[1]);
                        });
                    }
                }
            });
        }

        if (!empty($this->classMethods)) {
            $query->where(function($q) {
                foreach ($this->classMethods as $classMethod) {
                    $parts = explode('::', $classMethod);
                    if (count($parts) === 2) {
                        $q->orWhere(function($subQ) use ($parts) {
                            $subQ->where('class', $parts[0])->where('method', $parts[1]);
                        });
                    }
                }
            });
        }

        if ($this->dateFrom) {
            $query->where('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('created_at', '<=', $this->dateTo . ' 23:59:59');
        }

        $entries = $query->orderBy('created_at', 'desc')
                        ->paginate(config('plog.ui.per_page', 50));

        $stats = [
            'levels' => PlogEntry::select('level')
                ->selectRaw('count(*) as count')
                ->groupBy('level')
                ->pluck('count', 'level'),
            'environments' => PlogEntry::select('environment')
                ->selectRaw('count(*) as count')
                ->groupBy('environment')
                ->pluck('count', 'environment'),
        ];

        return view('plog::livewire.plog-viewer', [
            'entries' => $entries,
            'stats' => $stats,
        ]);
    }
}