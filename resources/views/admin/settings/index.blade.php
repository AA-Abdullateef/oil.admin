@extends('layouts.admin')
@section('title', 'Platform settings')
@section('breadcrumb')
    System / <strong>Settings</strong>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf

    @foreach($groups as $group => $settings)
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <span class="card-title">{{ ucfirst($group) }}</span>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            @foreach($settings as $setting)
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label" for="{{ $setting->key }}">
                    {{ $setting->label }}
                    @if($setting->is_public)
                        <span style="color:var(--green);font-size:9px;font-family:var(--font-mono);
                                     margin-left:4px;letter-spacing:.06em;">PUBLIC</span>
                    @endif
                </label>

                @if($setting->type === 'boolean')
                    <select name="{{ $setting->key }}" id="{{ $setting->key }}"
                            class="form-control">
                        <option value="true"  {{ $setting->value === 'true'  ? 'selected' : '' }}>Enabled</option>
                        <option value="false" {{ $setting->value === 'false' ? 'selected' : '' }}>Disabled</option>
                    </select>
                @else
                    <input type="{{ $setting->type === 'integer' ? 'number' : 'text' }}"
                           name="{{ $setting->key }}"
                           id="{{ $setting->key }}"
                           class="form-control"
                           value="{{ old($setting->key, $setting->value) }}"
                           placeholder="{{ $setting->description ?? '' }}">
                @endif

                @if($setting->description)
                <div style="font-size:11px;color:var(--text-faint);margin-top:3px;">
                    {{ $setting->description }}
                </div>
                @endif
            </div>
            @endforeach
            </div>
        </div>
    </div>
    @endforeach

    <div style="position:sticky;bottom:0;background:var(--bg-base);
                padding:16px 0;border-top:1px solid var(--border);
                display:flex;gap:12px;align-items:center;">
        <button type="submit" class="btn btn-primary">Save all settings</button>
        <span style="font-size:12px;color:var(--text-muted);">
            Changes take effect immediately. Cache is cleared on save.
        </span>
    </div>
</form>
@endsection