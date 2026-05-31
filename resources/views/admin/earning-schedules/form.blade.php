<div class="form-group">
    <label class="form-label">Asset</label>
    <select name="asset_id" class="form-control" required>
        @foreach($assets as $asset)
            <option value="{{ $asset->id }}" {{ old('asset_id', $schedule?->asset_id) === $asset->id ? 'selected' : '' }}>{{ $asset->symbol }} - {{ $asset->name }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label class="form-label">Percentage</label>
    <input type="number" name="percentage" class="form-control" value="{{ old('percentage', $schedule?->percentage) }}" step="0.0001" min="0.0001" max="100" required>
</div>
<div class="form-group">
    <label class="form-label">Frequency</label>
    <select name="frequency" class="form-control" required>
        @foreach(['daily','weekly','monthly'] as $frequency)
            <option value="{{ $frequency }}" {{ old('frequency', $schedule?->frequency ?? 'daily') === $frequency ? 'selected' : '' }}>{{ ucfirst($frequency) }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label class="form-label">Start date</label>
    <input type="datetime-local" name="start_date" class="form-control" value="{{ old('start_date', $schedule?->start_date?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" required>
</div>
@if($schedule)
<div class="form-group">
    <label class="form-label">Next run</label>
    <input type="datetime-local" name="next_run_at" class="form-control" value="{{ old('next_run_at', $schedule->next_run_at?->format('Y-m-d\TH:i')) }}">
</div>
@endif
<div class="form-group">
    <label class="form-label">Status</label>
    <select name="status" class="form-control" required>
        <option value="active" {{ old('status', $schedule?->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
        <option value="paused" {{ old('status', $schedule?->status) === 'paused' ? 'selected' : '' }}>Paused</option>
    </select>
</div>
