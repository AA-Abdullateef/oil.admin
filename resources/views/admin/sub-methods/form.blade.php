<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><span class="card-title">Sub-method details</span></div>
    <div class="card-body">
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Method</label>
                <select name="method_id" class="form-control" required>
                    @foreach($methods as $method)
                        <option value="{{ $method->id }}" {{ old('method_id', $subMethod->method_id) === $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $subMethod->name) }}" required>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group"><label class="form-label">Account name</label><input type="text" name="account_name" class="form-control" value="{{ old('account_name', $subMethod->account_name) }}"></div>
            <div class="form-group"><label class="form-label">Account number</label><input type="text" name="account_number" class="form-control" value="{{ old('account_number', $subMethod->account_number) }}"></div>
            <div class="form-group"><label class="form-label">Bank name</label><input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $subMethod->bank_name) }}"></div>
            <div class="form-group"><label class="form-label">Routing number</label><input type="text" name="routing_number" class="form-control" value="{{ old('routing_number', $subMethod->routing_number) }}"></div>
            <div class="form-group"><label class="form-label">SWIFT code</label><input type="text" name="swift_code" class="form-control" value="{{ old('swift_code', $subMethod->swift_code) }}"></div>
            <div class="form-group"><label class="form-label">IBAN</label><input type="text" name="iban" class="form-control" value="{{ old('iban', $subMethod->iban) }}"></div>
            <div class="form-group"><label class="form-label">Wallet address</label><input type="text" name="wallet_address" class="form-control" value="{{ old('wallet_address', $subMethod->wallet_address) }}"></div>
            <div class="form-group"><label class="form-label">Network</label><input type="text" name="network" class="form-control" value="{{ old('network', $subMethod->network) }}"></div>
        </div>

        <div class="form-group">
            <label class="form-label">Instructions</label>
            <textarea name="instructions" class="form-control" rows="4">{{ old('instructions', $subMethod->instructions) }}</textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-control">
                <option value="1" {{ (string) old('is_active', $subMethod->is_active ? '1' : '0') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ (string) old('is_active', $subMethod->is_active ? '1' : '0') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    </div>
</div>
