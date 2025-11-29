<div class="flex h-full">
    <!-- Fixed Sidebar -->
    <div class="w-80 bg-white border-r border-gray-200 flex flex-col flex-shrink-0">
        <!-- Sidebar Header -->
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
        </div>

        <!-- Filters Content -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4">

            <!-- User Filter (M - Medium) -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                <div class="relative">
                    <input type="text"
                           value="{{ $this->user_filter_display }}"
                           wire:click="toggleUserDropdown"
                           placeholder="👤 Filter by User"
                           autocomplete="off"
                           readonly
                           class="w-full px-3 py-2 pr-8 border border-gray-300 rounded-lg bg-white cursor-pointer focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @if($userFilter && $userFilter !== '')
                        <button type="button" wire:click="selectUser('')" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xl leading-none">
                            ×
                        </button>
                    @endif
                </div>

                @if($showUserDropdown)
                    <div class="absolute z-50 w-72 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        @foreach($this->recentUsers as $user)
                            <div wire:click.stop="selectUser('{{ $user['id'] === null ? 'null' : $user['id'] }}')"
                                 class="flex justify-between items-center px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                <span class="font-medium text-gray-700">{{ $user['label'] }}</span>
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $user['count'] }} logs</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Session & Request Row (S - Small) -->
            <div class="flex gap-3 mb-3">
                <div class="flex-1">
                    <div class="text-xs font-medium text-gray-600 mb-1">Session</div>
                    @livewire('plog-smart-dropdown', [
                        'field' => 'session_id',
                        'placeholder' => 'Sessions',
                        'label' => '',
                        'values' => $sessionIds,
                        'icon' => '📱'
                    ], key('session-dropdown-compact'))
                </div>
                <div class="flex-1">
                    <div class="text-xs font-medium text-gray-600 mb-1">Request</div>
                    @livewire('plog-smart-dropdown', [
                        'field' => 'request_id',
                        'placeholder' => 'Requests',
                        'label' => '',
                        'values' => $requestIds,
                        'icon' => '🔗'
                    ], key('request-dropdown-compact'))
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-4"></div>

            <!-- Tags Filter (M - Medium) -->
            <div class="mb-4">
                @livewire('plog-smart-dropdown', [
                    'field' => 'tags',
                    'placeholder' => 'Select Tags',
                    'label' => 'Tags',
                    'values' => $tags,
                    'icon' => '🏷️'
                ], key('tags-dropdown'))
            </div>

            <!-- Endpoints, Location, Class/Method Row (S - Small) -->
            <div class="space-y-2 mb-3">
                <div>
                    <div class="text-xs font-medium text-gray-600 mb-1">Endpoints</div>
                    @livewire('plog-smart-dropdown', [
                        'field' => 'endpoint',
                        'placeholder' => 'Endpoints',
                        'label' => '',
                        'values' => $endpoints,
                        'icon' => '📍'
                    ], key('endpoint-dropdown-compact'))
                </div>
                <div>
                    <div class="text-xs font-medium text-gray-600 mb-1">Location</div>
                    @livewire('plog-smart-dropdown', [
                        'field' => 'location',
                        'placeholder' => 'Locations',
                        'label' => '',
                        'values' => $locations,
                        'icon' => '📂'
                    ], key('location-dropdown-compact'))
                </div>
                <div>
                    <div class="text-xs font-medium text-gray-600 mb-1">Class/Method</div>
                    @livewire('plog-smart-dropdown', [
                        'field' => 'class_method',
                        'placeholder' => 'Class/Methods',
                        'label' => '',
                        'values' => $classMethods,
                        'icon' => '⚙️'
                    ], key('class-method-dropdown-compact'))
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-4"></div>

            <!-- Date Range Row (S - Small) -->
            <div class="flex gap-3 mb-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date From</label>
                    <input type="datetime-local" wire:model.live="dateFrom"
                           class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date To</label>
                    <input type="datetime-local" wire:model.live="dateTo"
                           class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-4"></div>

            <!-- Level & Environment Row (S - Small) -->
            <div class="flex gap-3 mb-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Level</label>
                    <select wire:model.live="level" class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">All Levels</option>
                        <option value="debug">Debug</option>
                        <option value="info">Info</option>
                        <option value="notice">Notice</option>
                        <option value="warning">Warning</option>
                        <option value="error">Error</option>
                        <option value="critical">Critical</option>
                        <option value="alert">Alert</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Environment</label>
                    <select wire:model.live="environment" class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">All Environments</option>
                        <option value="http">HTTP</option>
                        <option value="cli">CLI</option>
                        <option value="queue">Queue</option>
                        <option value="testing">Testing</option>
                    </select>
                </div>
            </div>

            <!-- Active Filters -->
            @if($search || $level || $userFilter || !empty($requestIds) || !empty($sessionIds) || $environment || !empty($endpoints) || !empty($tags) || !empty($locations) || !empty($classMethods) || $dateFrom || $dateTo)
                <div class="pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Active Filters</h3>
                    <div class="space-y-2">
                        @if($search)
                            <div class="flex items-center justify-between px-3 py-1 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                                <span>Search: {{ Str::limit($search, 20) }}</span>
                                <button wire:click="$set('search', '')" class="text-blue-600 hover:text-blue-800 ml-2">×</button>
                            </div>
                        @endif
                        @if($level)
                            <div class="flex items-center justify-between px-3 py-1 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                                <span>Level: {{ $level }}</span>
                                <button wire:click="$set('level', '')" class="text-blue-600 hover:text-blue-800 ml-2">×</button>
                            </div>
                        @endif
                        @if($userFilter)
                            <div class="flex items-center justify-between px-3 py-1 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                                <span>User: {{ $userFilter }}</span>
                                <button wire:click="$set('userFilter', '')" class="text-blue-600 hover:text-blue-800 ml-2">×</button>
                            </div>
                        @endif
                        @if(!empty($requestIds))
                            <div class="flex items-center justify-between px-3 py-1 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                                <span>Request IDs: {{ count($requestIds) }} selected</span>
                                <button wire:click="$set('requestIds', [])" class="text-blue-600 hover:text-blue-800 ml-2">×</button>
                            </div>
                        @endif
                        @if(!empty($sessionIds))
                            <div class="flex items-center justify-between px-3 py-1 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                                <span>Session IDs: {{ count($sessionIds) }} selected</span>
                                <button wire:click="$set('sessionIds', [])" class="text-blue-600 hover:text-blue-800 ml-2">×</button>
                            </div>
                        @endif
                        @if($environment)
                            <div class="flex items-center justify-between px-3 py-1 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                                <span>Env: {{ $environment }}</span>
                                <button wire:click="$set('environment', '')" class="text-blue-600 hover:text-blue-800 ml-2">×</button>
                            </div>
                        @endif
                        @if(!empty($endpoints))
                            <div class="flex items-center justify-between px-3 py-1 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                                <span>Endpoints: {{ count($endpoints) }} selected</span>
                                <button wire:click="$set('endpoints', [])" class="text-blue-600 hover:text-blue-800 ml-2">×</button>
                            </div>
                        @endif
                        @if(!empty($tags))
                            <div class="flex items-center justify-between px-3 py-1 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                                <span>Tags: {{ count($tags) }} selected</span>
                                <button wire:click="$set('tags', [])" class="text-blue-600 hover:text-blue-800 ml-2">×</button>
                            </div>
                        @endif
                        @if(!empty($locations))
                            <div class="flex items-center justify-between px-3 py-1 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                                <span>Locations: {{ count($locations) }} selected</span>
                                <button wire:click="$set('locations', [])" class="text-blue-600 hover:text-blue-800 ml-2">×</button>
                            </div>
                        @endif
                        @if(!empty($classMethods))
                            <div class="flex items-center justify-between px-3 py-1 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                                <span>Class/Methods: {{ count($classMethods) }} selected</span>
                                <button wire:click="$set('classMethods', [])" class="text-blue-600 hover:text-blue-800 ml-2">×</button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Search Bar Above Table -->
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-lg font-semibold text-gray-800 mb-2">Search</label>
                    <input type="text"
                           wire:model.live.debounce.100ms="search"
                           placeholder="🔍 Search logs, messages, context..."
                           class="w-full px-4 py-3 text-base border-2 border-blue-500 rounded-lg bg-white focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20 shadow-sm">
                </div>
                <div>
                    <button wire:click="clearFilters" class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                        Clear All Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Content -->
        <div class="flex-1 overflow-y-auto p-4">
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b-2 border-gray-200">
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Time</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Level</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">User</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Message</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tags</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($entries as $entry)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-3 py-2 text-sm text-gray-900 whitespace-nowrap">{{ $entry->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-3 py-2 text-sm whitespace-nowrap">
                                <button wire:click="filterByLevel('{{ $entry->level }}')"
                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold uppercase transition-colors duration-150 hover:opacity-80
                                        @if(in_array($entry->level, ['emergency', 'alert', 'critical', 'error']))
                                            bg-red-100 text-red-800 hover:bg-red-200
                                        @elseif($entry->level === 'warning')
                                            bg-orange-100 text-orange-800 hover:bg-orange-200
                                        @elseif(in_array($entry->level, ['notice', 'info']))
                                            bg-blue-100 text-blue-800 hover:bg-blue-200
                                        @else
                                            bg-gray-100 text-gray-800 hover:bg-gray-200
                                        @endif">
                                    {{ ucfirst($entry->level) }}
                                </button>
                            </td>
                            <td class="px-3 py-2 text-sm whitespace-nowrap">
                                @if($entry->user_id)
                                    <button wire:click="filterByUser('{{ $entry->user_id }}')" class="text-blue-600 hover:text-blue-800 hover:underline transition-colors duration-150">
                                        #{{ $entry->user_id }}
                                    </button>
                                @elseif($entry->environment === 'cli' || $entry->environment === 'queue')
                                    <button wire:click="filterByUser('system')" class="text-purple-600 hover:text-purple-800 hover:underline transition-colors duration-150">
                                        System
                                    </button>
                                @else
                                    <button wire:click="filterByUser('guest')" class="text-gray-600 hover:text-gray-800 hover:underline transition-colors duration-150">
                                        Guest
                                    </button>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-sm text-gray-900">{{ Str::limit($entry->message, 100) }}</td>
                            <td class="px-3 py-2 text-sm">
                                @if($entry->tags)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($entry->tags as $tag)
                                            <button wire:click="filterByTag('{{ $tag }}')" class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 border border-green-200 hover:bg-green-200 transition-colors duration-150">
                                                {{ $tag }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-sm whitespace-nowrap">
                                <button wire:click="showEntry({{ $entry->id }})" class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition-colors duration-150">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-10 text-center text-gray-500">No log entries found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $entries->links() }}
                </div>
            </div>
        </div>
    </div>

    @if($selectedEntry)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" wire:click.self="closeEntry">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-7xl h-[90vh] flex flex-col">
                <!-- Modal Header -->
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Log Entry Details</h2>
                    <button wire:click="closeEntry" class="text-gray-400 hover:text-gray-600 text-2xl font-bold leading-none">×</button>
                </div>

                <!-- Main Entry Details -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Time</label>
                            <span class="text-sm text-gray-900 font-medium">{{ $selectedEntry->created_at->format('Y-m-d H:i:s.u') }}</span>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Level</label>
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold uppercase
                                @if(in_array($selectedEntry->level, ['emergency', 'alert', 'critical', 'error']))
                                    bg-red-100 text-red-800
                                @elseif($selectedEntry->level === 'warning')
                                    bg-orange-100 text-orange-800
                                @elseif(in_array($selectedEntry->level, ['notice', 'info']))
                                    bg-blue-100 text-blue-800
                                @else
                                    bg-gray-100 text-gray-800
                                @endif">{{ ucfirst($selectedEntry->level) }}</span>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Environment</label>
                            <span class="text-sm text-gray-900">{{ $selectedEntry->environment }}</span>
                        </div>
                        @if($selectedEntry->tags)
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Tags</label>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($selectedEntry->tags as $tag)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-800">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Message</label>
                        <div class="bg-white p-3 rounded border border-gray-200 text-sm text-gray-900 font-mono break-words">{{ $selectedEntry->message }}</div>
                    </div>

                    @if($selectedEntry->context)
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Context</label>
                            <pre class="bg-gray-900 text-gray-100 p-3 rounded text-xs font-mono overflow-x-auto max-h-40">{{ json_encode($selectedEntry->context, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif
                </div>

                <!-- Log Context Section -->
                <div class="flex flex-1 min-h-0">
                    <!-- Context Tabs Sidebar -->
                    <div class="w-48 bg-gray-50 border-r border-gray-200 flex flex-col">
                        <div class="p-3 border-b border-gray-200 bg-white">
                            <h3 class="font-semibold text-gray-800">Log Context</h3>
                            <div class="text-xs text-gray-500">Active: {{ $activeContextTab }}</div>
                        </div>
                        <div class="flex-1 p-2 space-y-1">
                            <button wire:click="setActiveContextTab('request_data')"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                    {{ $activeContextTab === 'request_data' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                                📡 Request Data
                            </button>
                            <button wire:click="setActiveContextTab('stack_trace')"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                    {{ $activeContextTab === 'stack_trace' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                                🔍 Stack Trace
                            </button>
                            <button wire:click="setActiveContextTab('user')"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                    {{ $activeContextTab === 'user' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                                <div class="flex justify-between items-center">
                                    <span>👤 User</span>
                                    @if(isset($contextCounts['user']))
                                        <span class="text-xs bg-gray-200 px-2 py-0.5 rounded-full">{{ $contextCounts['user'] }}</span>
                                    @endif
                                </div>
                            </button>
                            @if($selectedEntry->request_id)
                                <button wire:click="setActiveContextTab('request')"
                                        class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                        {{ $activeContextTab === 'request' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                                    <div class="flex justify-between items-center">
                                        <span>🔗 Request</span>
                                        @if(isset($contextCounts['request']))
                                            <span class="text-xs bg-gray-200 px-2 py-0.5 rounded-full">{{ $contextCounts['request'] }}</span>
                                        @endif
                                    </div>
                                </button>
                            @endif
                            @if($selectedEntry->session_id)
                                <button wire:click="setActiveContextTab('session')"
                                        class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                        {{ $activeContextTab === 'session' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                                    <div class="flex justify-between items-center">
                                        <span>📱 Session</span>
                                        @if(isset($contextCounts['session']))
                                            <span class="text-xs bg-gray-200 px-2 py-0.5 rounded-full">{{ $contextCounts['session'] }}</span>
                                        @endif
                                    </div>
                                </button>
                            @endif
                            @if($selectedEntry->endpoint)
                                <button wire:click="setActiveContextTab('endpoint')"
                                        class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                        {{ $activeContextTab === 'endpoint' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                                    <div class="flex justify-between items-center">
                                        <span>📍 Endpoint</span>
                                        @if(isset($contextCounts['endpoint']))
                                            <span class="text-xs bg-gray-200 px-2 py-0.5 rounded-full">{{ $contextCounts['endpoint'] }}</span>
                                        @endif
                                    </div>
                                </button>
                            @endif
                            <button wire:click="setActiveContextTab('location')"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                    {{ $activeContextTab === 'location' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                                <div class="flex justify-between items-center">
                                    <span>📂 Location</span>
                                    @if(isset($contextCounts['location']))
                                        <span class="text-xs bg-gray-200 px-2 py-0.5 rounded-full">{{ $contextCounts['location'] }}</span>
                                    @endif
                                </div>
                            </button>
                            <button wire:click="setActiveContextTab('class')"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                    {{ $activeContextTab === 'class' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                                <div class="flex justify-between items-center">
                                    <span>⚙️ Class</span>
                                    @if(isset($contextCounts['class']))
                                        <span class="text-xs bg-gray-200 px-2 py-0.5 rounded-full">{{ $contextCounts['class'] }}</span>
                                    @endif
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Context Content -->
                    <div class="flex-1 overflow-y-auto bg-white">
                    @if($activeContextTab === 'request_data')
                        <div class="p-4">
                            <div class="mb-4 text-sm text-gray-600">
                                Request Data for: <span class="font-semibold">{{ $selectedEntry->request_id }}</span>
                            </div>

                            @if($selectedEntry->request)
                                <div class="space-y-4">
                                    <!-- Request Overview -->
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <h4 class="font-semibold text-gray-800 mb-2">Request Overview</h4>
                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div><span class="font-medium">Method:</span> {{ $selectedEntry->request->method }}</div>
                                            <div><span class="font-medium">IP:</span> {{ $selectedEntry->request->ip_address }}</div>
                                            <div class="col-span-2"><span class="font-medium">URL:</span> <code class="bg-gray-200 px-1 rounded">{{ $selectedEntry->request->url }}</code></div>
                                            <div class="col-span-2"><span class="font-medium">User Agent:</span> {{ Str::limit($selectedEntry->request->user_agent, 100) }}</div>
                                        </div>
                                    </div>

                                    <!-- Headers -->
                                    @if($selectedEntry->request->headers)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <h4 class="font-semibold text-gray-800 mb-2">Headers</h4>
                                            <pre class="text-xs bg-white p-3 rounded border overflow-x-auto">{{ json_encode($selectedEntry->request->headers, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    @endif

                                    <!-- Query Parameters -->
                                    @if($selectedEntry->request->query_params)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <h4 class="font-semibold text-gray-800 mb-2">Query Parameters</h4>
                                            <pre class="text-xs bg-white p-3 rounded border overflow-x-auto">{{ json_encode($selectedEntry->request->query_params, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    @endif

                                    <!-- Request Body -->
                                    @if($selectedEntry->request->body)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <h4 class="font-semibold text-gray-800 mb-2">Request Body</h4>
                                            <pre class="text-xs bg-white p-3 rounded border overflow-x-auto">{{ is_array($selectedEntry->request->body) ? json_encode($selectedEntry->request->body, JSON_PRETTY_PRINT) : $selectedEntry->request->body }}</pre>
                                        </div>
                                    @endif

                                    <!-- Cookies -->
                                    @if($selectedEntry->request->cookies)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <h4 class="font-semibold text-gray-800 mb-2">Cookies</h4>
                                            <pre class="text-xs bg-white p-3 rounded border overflow-x-auto">{{ json_encode($selectedEntry->request->cookies, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500">
                                    No request data available for this log entry
                                </div>
                            @endif
                        </div>
                    @elseif($activeContextTab === 'stack_trace')
                        <div class="p-4">
                            <div class="mb-4 text-sm text-gray-600">
                                Stack trace for this log entry (showing only your application code)
                            </div>

                            @if($selectedEntry->stack_trace)
                                <div class="space-y-2">
                                    @foreach($selectedEntry->stack_trace as $index => $frame)
                                        <div class="bg-gray-50 rounded-lg p-3 hover:bg-gray-100 transition-colors">
                                            <div class="flex items-start gap-3">
                                                <div class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-mono font-semibold min-w-0 flex-shrink-0">
                                                    #{{ $index + 1 }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    @if($frame['class'])
                                                        <div class="font-semibold text-gray-800">{{ $frame['class'] }}::{{ $frame['method'] ?? 'unknown' }}</div>
                                                    @elseif($frame['method'])
                                                        <div class="font-semibold text-gray-800">{{ $frame['method'] }}()</div>
                                                    @endif
                                                    <div class="text-sm text-gray-600 font-mono">
                                                        {{ $frame['file'] }}@if($frame['line']):{{ $frame['line'] }}@endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500">
                                    No stack trace available for this log entry
                                </div>
                            @endif
                        </div>
                    @elseif(isset($contextLogs[$activeContextTab]))
                        <div class="p-4">
                            <!-- Context Info -->
                            <div class="mb-4 text-sm text-gray-600">
                                @if($activeContextTab === 'user')
                                    Showing logs for: <span class="font-semibold">{{ $selectedEntry->user_id ? "User ID #{$selectedEntry->user_id}" : 'Guest Users' }}</span>
                                @elseif($activeContextTab === 'request')
                                    Showing logs for Request ID: <span class="font-semibold">{{ $selectedEntry->request_id }}</span>
                                @elseif($activeContextTab === 'session')
                                    Showing logs for Session ID: <span class="font-semibold">{{ $selectedEntry->session_id }}</span>
                                @elseif($activeContextTab === 'endpoint')
                                    Showing logs for Endpoint: <span class="font-semibold">{{ $selectedEntry->endpoint }}</span>
                                @elseif($activeContextTab === 'location')
                                    Showing logs from: <span class="font-semibold">{{ $selectedEntry->file }}:{{ $selectedEntry->line }}</span>
                                @elseif($activeContextTab === 'class')
                                    Showing logs from: <span class="font-semibold">{{ $selectedEntry->class }}::{{ $selectedEntry->method }}</span>
                                @endif
                            </div>

                            <!-- Contextual Logs Timeline -->
                            <div class="space-y-2">
                                <!-- Load More Later Button -->
                                @if($contextLogs[$activeContextTab]['hasMoreAfter'])
                                    <button wire:click="loadMoreContextLogs('after')" class="w-full px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm rounded-lg transition-colors">
                                        ↑ Load 5 More Later Logs
                                    </button>
                                @endif

                                <!-- After Logs (newest first, oldest last) -->
                                @foreach($contextLogs[$activeContextTab]['after'] as $log)
                                    <div class="flex gap-4 bg-gray-50 rounded-lg p-3 hover:bg-gray-100 transition-colors">
                                        <div class="text-right min-w-0 w-24 flex-shrink-0">
                                            <div class="text-xs font-semibold text-gray-600">{{ $log->created_at->format('H:i:s') }}</div>
                                            <div class="text-xs text-gray-400">{{ $log->created_at->format('m/d') }}</div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex justify-between items-start mb-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                                    @if(in_array($log->level, ['emergency', 'alert', 'critical', 'error']))
                                                        bg-red-100 text-red-800
                                                    @elseif($log->level === 'warning')
                                                        bg-orange-100 text-orange-800
                                                    @elseif(in_array($log->level, ['notice', 'info']))
                                                        bg-blue-100 text-blue-800
                                                    @else
                                                        bg-gray-100 text-gray-800
                                                    @endif">{{ ucfirst($log->level) }}</span>
                                            </div>
                                            <p class="text-sm text-gray-800 font-mono">{{ Str::limit($log->message, 200) }}</p>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Current Entry -->
                                <div class="flex gap-4 bg-blue-50 border-2 border-blue-300 rounded-lg p-3 relative">
                                    <div class="absolute -right-2 top-1/2 transform -translate-y-1/2">
                                        <span class="bg-blue-500 text-white px-2 py-1 rounded-full text-xs font-semibold">CURRENT</span>
                                    </div>
                                    <div class="text-right min-w-0 w-24 flex-shrink-0">
                                        <div class="text-xs font-semibold text-blue-700">{{ $selectedEntry->created_at->format('H:i:s') }}</div>
                                        <div class="text-xs text-blue-500">{{ $selectedEntry->created_at->format('m/d') }}</div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start mb-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                                @if(in_array($selectedEntry->level, ['emergency', 'alert', 'critical', 'error']))
                                                    bg-red-100 text-red-800
                                                @elseif($selectedEntry->level === 'warning')
                                                    bg-orange-100 text-orange-800
                                                @elseif(in_array($selectedEntry->level, ['notice', 'info']))
                                                    bg-blue-100 text-blue-800
                                                @else
                                                    bg-gray-100 text-gray-800
                                                @endif">{{ ucfirst($selectedEntry->level) }}</span>
                                        </div>
                                        <p class="text-sm text-blue-900 font-mono font-medium">{{ Str::limit($selectedEntry->message, 200) }}</p>
                                    </div>
                                </div>

                                <!-- Before Logs (newest first, oldest last) -->
                                @foreach($contextLogs[$activeContextTab]['before'] as $log)
                                    <div class="flex gap-4 bg-gray-50 rounded-lg p-3 hover:bg-gray-100 transition-colors">
                                        <div class="text-right min-w-0 w-24 flex-shrink-0">
                                            <div class="text-xs font-semibold text-gray-600">{{ $log->created_at->format('H:i:s') }}</div>
                                            <div class="text-xs text-gray-400">{{ $log->created_at->format('m/d') }}</div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex justify-between items-start mb-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                                    @if(in_array($log->level, ['emergency', 'alert', 'critical', 'error']))
                                                        bg-red-100 text-red-800
                                                    @elseif($log->level === 'warning')
                                                        bg-orange-100 text-orange-800
                                                    @elseif(in_array($log->level, ['notice', 'info']))
                                                        bg-blue-100 text-blue-800
                                                    @else
                                                        bg-gray-100 text-gray-800
                                                    @endif">{{ ucfirst($log->level) }}</span>
                                            </div>
                                            <p class="text-sm text-gray-800 font-mono">{{ Str::limit($log->message, 200) }}</p>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Load More Earlier Button -->
                                @if($contextLogs[$activeContextTab]['hasMoreBefore'])
                                    <button wire:click="loadMoreContextLogs('before')" class="w-full px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm rounded-lg transition-colors">
                                        ↓ Load 5 More Earlier Logs
                                    </button>
                                @endif
                            </div>

                            @if(count($contextLogs[$activeContextTab]['before']) == 0 && count($contextLogs[$activeContextTab]['after']) == 0)
                                <div class="text-center py-8 text-gray-500">
                                    No other logs found in this context
                                </div>
                            @endif
                        </div>
                    @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>