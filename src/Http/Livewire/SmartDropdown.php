<?php

namespace Laikmosh\Plog\Http\Livewire;

use Livewire\Component;
use Laikmosh\Plog\Models\PlogEntry;

class SmartDropdown extends Component
{
    public $field;
    public $placeholder;
    public $label;
    public $values = [];
    public $showDropdown = false;
    public $icon = '';

    protected $listeners = ['closeOtherDropdowns'];

    public function mount($field, $placeholder, $label, $values = [], $icon = '')
    {
        $this->field = $field;
        $this->placeholder = $placeholder;
        $this->label = $label;
        $this->values = is_array($values) ? $values : ($values ? [$values] : []);
        $this->icon = $icon;
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
        if ($this->showDropdown) {
            $this->dispatch('closeOtherDropdowns', $this->field);
        }
    }

    public function closeOtherDropdowns($exceptField)
    {
        if ($exceptField !== $this->field) {
            $this->showDropdown = false;
        }
    }

    public function selectValue($selectedValue)
    {
        if (in_array($selectedValue, $this->values)) {
            // Remove if already selected
            $this->values = array_values(array_filter($this->values, function($value) use ($selectedValue) {
                return $value !== $selectedValue;
            }));
        } else {
            // Add to selection
            $this->values[] = $selectedValue;
        }

        $this->dispatch('filterUpdated', $this->field, $this->values);
    }

    public function removeValue($valueToRemove)
    {
        $this->values = array_values(array_filter($this->values, function($value) use ($valueToRemove) {
            return $value !== $valueToRemove;
        }));
        $this->dispatch('filterUpdated', $this->field, $this->values);
    }

    public function clearAllValues()
    {
        $this->values = [];
        $this->dispatch('filterUpdated', $this->field, []);
    }

    public function getDisplayTextProperty()
    {
        if (empty($this->values)) {
            return '';
        }

        if (count($this->values) === 1) {
            return $this->values[0];
        }

        return count($this->values) . ' selected';
    }

    public function getOptionsProperty()
    {
        switch ($this->field) {
            case 'request_id':
                return PlogEntry::select('request_id')
                    ->whereNotNull('request_id')
                    ->where('request_id', '!=', '')
                    ->groupBy('request_id')
                    ->orderBy('request_id')
                    ->limit(20)
                    ->get()
                    ->map(function ($entry) {
                        return [
                            'value' => $entry->request_id,
                            'label' => $entry->request_id,
                            'count' => PlogEntry::where('request_id', $entry->request_id)->count()
                        ];
                    });

            case 'session_id':
                return PlogEntry::select('session_id')
                    ->whereNotNull('session_id')
                    ->where('session_id', '!=', '')
                    ->groupBy('session_id')
                    ->orderBy('session_id')
                    ->limit(20)
                    ->get()
                    ->map(function ($entry) {
                        return [
                            'value' => $entry->session_id,
                            'label' => $entry->session_id,
                            'count' => PlogEntry::where('session_id', $entry->session_id)->count()
                        ];
                    });

            case 'endpoint':
                return PlogEntry::select('endpoint')
                    ->whereNotNull('endpoint')
                    ->where('endpoint', '!=', '')
                    ->groupBy('endpoint')
                    ->orderBy('endpoint')
                    ->limit(20)
                    ->get()
                    ->map(function ($entry) {
                        return [
                            'value' => $entry->endpoint,
                            'label' => $entry->endpoint,
                            'count' => PlogEntry::where('endpoint', $entry->endpoint)->count()
                        ];
                    });

            case 'location':
                return PlogEntry::select('file', 'line')
                    ->whereNotNull('file')
                    ->where('file', '!=', '')
                    ->groupBy('file', 'line')
                    ->orderBy('file')
                    ->orderBy('line')
                    ->limit(20)
                    ->get()
                    ->map(function ($entry) {
                        $location = $entry->file . ':' . $entry->line;
                        return [
                            'value' => $location,
                            'label' => $location,
                            'count' => PlogEntry::where('file', $entry->file)->where('line', $entry->line)->count()
                        ];
                    });

            case 'class_method':
                return PlogEntry::select('class', 'method')
                    ->whereNotNull('class')
                    ->where('class', '!=', '')
                    ->whereNotNull('method')
                    ->where('method', '!=', '')
                    ->groupBy('class', 'method')
                    ->orderBy('class')
                    ->orderBy('method')
                    ->limit(20)
                    ->get()
                    ->map(function ($entry) {
                        $classMethod = $entry->class . '::' . $entry->method;
                        return [
                            'value' => $classMethod,
                            'label' => $classMethod,
                            'count' => PlogEntry::where('class', $entry->class)->where('method', $entry->method)->count()
                        ];
                    });

            case 'tags':
                return PlogEntry::whereNotNull('tags')
                    ->get()
                    ->pluck('tags')
                    ->flatten()
                    ->unique()
                    ->sort()
                    ->values()
                    ->take(20)
                    ->map(function ($tag) {
                        return [
                            'value' => $tag,
                            'label' => $tag,
                            'count' => PlogEntry::whereJsonContains('tags', $tag)->count()
                        ];
                    });

            default:
                return collect();
        }
    }

    public function render()
    {
        return view('plog::livewire.smart-dropdown');
    }
}