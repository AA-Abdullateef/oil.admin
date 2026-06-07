$docsPath = 'docs\API_FRONTEND_HANDOFF.md'
$collectionPath = 'postman\Oil_Admin_API.postman_collection.json'

$docsText = Get-Content $docsPath -Raw
$collection = Get-Content $collectionPath -Raw | ConvertFrom-Json

function Get-Section([string] $heading) {
    $pattern = "(?ms)^### $([regex]::Escape($heading))\s*(.*?)(?=^### |^## |\z)"
    $match = [regex]::Match($docsText, $pattern)
    if (-not $match.Success) {
        throw "Missing docs section: $heading"
    }

    return $match.Groups[1].Value.Trim()
}

function Get-Section-Range([string] $startHeading, [string] $endHeading) {
    $pattern = "(?ms)^### $([regex]::Escape($startHeading))\s*(.*?)(?=^### $([regex]::Escape($endHeading))\s*)"
    $match = [regex]::Match($docsText, $pattern)
    if (-not $match.Success) {
        throw "Missing docs range: $startHeading to $endHeading"
    }

    return $match.Groups[1].Value.Trim()
}

function New-JsonResponse([string] $name, [int] $code, [string] $body) {
    $status = switch ($code) {
        201 { 'Created' }
        403 { 'Forbidden' }
        422 { 'Unprocessable Entity' }
        default { 'OK' }
    }

    [pscustomobject]@{
        name = $name
        status = $status
        code = $code
        _postman_previewlanguage = 'json'
        header = @([pscustomobject]@{ key = 'Content-Type'; value = 'application/json' })
        body = $body.Trim()
    }
}

function New-TextResponse([string] $name, [int] $code, [string] $body) {
    [pscustomobject]@{
        name = $name
        status = 'OK'
        code = $code
        _postman_previewlanguage = 'text'
        header = @()
        body = $body.Trim()
    }
}

function Get-CodeBlocks([string] $section, [string] $language) {
    $pattern = '(?ms)```' + [regex]::Escape($language) + '\s*(.*?)\s*```'
    [regex]::Matches($section, $pattern) | ForEach-Object { $_.Groups[1].Value.Trim() }
}

function Get-ResponseCode([string] $section) {
    $match = [regex]::Match($section, 'Response `(\d+)`')
    if ($match.Success) {
        return [int] $match.Groups[1].Value
    }

    return 200
}

function New-Description([string] $section, [string] $extra = '') {
    $common = @'

Common frontend contract:
- Send Accept: application/json on API requests.
- Successful JSON responses use: { success, message, data }.
- Error JSON responses include success: false.
- Store auth tokens from data.access_token and send Authorization: Bearer <token>.
- Financial endpoints require verified KYC.
- Do not build proof file URLs from raw proof paths; use proof_url.
'@

    $description = $section.Trim()
    if ($extra.Trim()) {
        $description = "$description`n`n$($extra.Trim())"
    }

    return "$description$common"
}

function Build-Entry([string[]] $sections, [string] $extra = '', [string] $responseName = 'Documented response') {
    $sectionText = ($sections -join "`n`n").Trim()
    $jsonBlocks = @(Get-CodeBlocks $sectionText 'json')
    $textBlocks = @(Get-CodeBlocks $sectionText 'text')
    $responses = @()
    $code = Get-ResponseCode $sectionText

    if ($jsonBlocks.Count -gt 0) {
        $responses += New-JsonResponse $responseName $code $jsonBlocks[-1]
    } elseif ($textBlocks.Count -gt 0) {
        $responses += New-TextResponse $responseName 200 $textBlocks[-1]
    } else {
        $responses += New-JsonResponse 'Example success envelope' 200 '{ "success": true, "message": "Request successful.", "data": null }'
    }

    [pscustomobject]@{
        Description = New-Description $sectionText $extra
        Responses = @($responses)
    }
}

$commonErrors = @'
Common documented error examples:

KYC blocked:
```json
{
  "success": false,
  "message": "Your identity has not been verified yet. Please submit your KYC documents.",
  "error": "KYC_NOT_VERIFIED",
  "kyc_url": "/api/v1/kyc/submit"
}
```

Insufficient balance:
```json
{
  "success": false,
  "message": "Insufficient USD balance.",
  "error": "INSUFFICIENT_BALANCE"
}
```

Validation:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```
'@

$map = @{
    'Public / Settings' = Build-Entry @((Get-Section 'Settings')) '' 'Example 200 - Settings retrieved'
    'Public / Send Contact Message' = Build-Entry @((Get-Section 'Contact')) '' 'Example 201 - Contact message received'
    'Public / Countries' = Build-Entry @((Get-Section 'Countries')) '' 'Example 200 - Countries retrieved'
    'Public / States By Country Slug' = Build-Entry @((Get-Section 'States By Country Slug')) '' 'Example 200 - States by slug retrieved'
    'Public / States By Country ID' = Build-Entry @((Get-Section 'States By Country ID')) '' 'Example 200 - States retrieved'

    'Auth / Register' = Build-Entry @((Get-Section 'Register')) '' 'Example 201 - Registration successful'
    'Auth / Login - Test User' = Build-Entry @((Get-Section 'Login')) '' 'Example 200 - Login successful'
    'Auth / Me' = Build-Entry @((Get-Section 'Me')) '' 'Example 200 - Authenticated user retrieved'
    'Auth / Logout' = Build-Entry @((Get-Section 'Logout')) '' 'Example 200 - Logged out'
    'Auth / Dashboard' = Build-Entry @((Get-Section 'Dashboard')) '' 'Example 200 - Dashboard retrieved'
    'Auth / Forgot Password' = Build-Entry @((Get-Section 'Forgot Password')) '' 'Example 200 - Reset link sent'
    'Auth / Reset Password' = Build-Entry @((Get-Section 'Reset Password')) '' 'Example 200 - Password reset'

    'Profile And KYC / Profile' = Build-Entry @((Get-Section 'Profile')) '' 'Example 200 - Profile retrieved'
    'Profile And KYC / Update Profile' = Build-Entry @((Get-Section 'Update Profile')) '' 'Example 200 - Profile updated'
    'Profile And KYC / KYC Status' = Build-Entry @((Get-Section 'KYC Status')) '' 'Example 200 - KYC status retrieved'
    'Profile And KYC / Submit KYC Documents' = Build-Entry @((Get-Section 'Submit KYC')) '' 'Example 200 - Documents submitted'

    'Catalog / Assets' = Build-Entry @((Get-Section 'Assets')) '' 'Example 200 - Assets retrieved'
    'Catalog / Assets Filtered By Type' = Build-Entry @((Get-Section 'Assets')) 'This collection request applies the documented optional query: type=crypto.' 'Example 200 - Filtered assets retrieved'
    'Catalog / Asset Detail' = Build-Entry @((Get-Section 'Asset Detail')) '' 'Example 200 - Asset retrieved'
    'Catalog / Payment Methods' = Build-Entry @((Get-Section 'Methods')) '' 'Example 200 - Payment methods retrieved'
    'Catalog / Bank Sub Methods' = Build-Entry @((Get-Section 'Sub-Methods By Method')) 'This collection request uses the Bank Transfer method id.' 'Example 200 - Bank sub-methods retrieved'
    'Catalog / Crypto Sub Methods' = Build-Entry @((Get-Section 'Sub-Methods By Method')) 'This collection request uses the Cryptocurrency method id.' 'Example 200 - Crypto sub-methods retrieved'
    'Catalog / Sub Method Detail' = Build-Entry @((Get-Section 'Sub-Method Detail')) '' 'Example 200 - Payment sub-method retrieved'

    'Balances Holdings Trades / Balances' = Build-Entry @((Get-Section 'Balances')) $commonErrors 'Example 200 - Balances retrieved'
    'Balances Holdings Trades / Balance Transactions' = Build-Entry @((Get-Section 'Balance Transactions')) '' 'Example 200 - Balance transactions retrieved'
    'Balances Holdings Trades / Holdings' = Build-Entry @((Get-Section 'Holdings')) '' 'Example 200 - Holdings retrieved'
    'Balances Holdings Trades / Holding Detail' = Build-Entry @((Get-Section 'Holding Detail')) '' 'Example 200 - Holding retrieved'
    'Balances Holdings Trades / Holding Trades' = Build-Entry @((Get-Section 'Holding Trades')) '' 'Example 200 - Holding trades retrieved'
    'Balances Holdings Trades / Buy Asset' = Build-Entry @((Get-Section 'Buy Asset')) $commonErrors 'Example 201 - Trade recorded'
    'Balances Holdings Trades / Sell Asset' = Build-Entry @((Get-Section 'Sell Asset')) $commonErrors 'Example 201 - Trade recorded'

    'Deposits / List Deposits' = Build-Entry @((Get-Section 'List Deposits')) '' 'Example 200 - Deposits retrieved'
    'Deposits / Create Deposit With Proof' = Build-Entry @((Get-Section 'Create Deposit')) '' 'Example 201 - Deposit submitted'
    'Deposits / Deposit Detail' = Build-Entry @((Get-Section 'Deposit Detail')) '' 'Example 200 - Deposit retrieved'
    'Deposits / View Deposit Proof' = Build-Entry @((Get-Section 'View Deposit Proof')) '' 'Example 200 - Binary deposit proof'

    'Withdrawals / List Withdrawals' = Build-Entry @((Get-Section 'List Withdrawals')) '' 'Example 200 - Withdrawals retrieved'
    'Withdrawals / Create Bank Withdrawal' = Build-Entry @((Get-Section-Range 'Create Bank Withdrawal' 'Withdrawal Detail')) '' 'Example 201 - Withdrawal submitted'
    'Withdrawals / Create Crypto Withdrawal' = Build-Entry @((Get-Section-Range 'Create Crypto Withdrawal' 'Withdrawal Detail')) '' 'Example 201 - Withdrawal submitted'
    'Withdrawals / Withdrawal Detail' = Build-Entry @((Get-Section 'Withdrawal Detail')) '' 'Example 200 - Withdrawal retrieved'
    'Withdrawals / View Withdrawal Proof' = Build-Entry @((Get-Section 'View Withdrawal Proof')) '' 'Example 200 - Binary withdrawal proof'

    'Transactions / List Transactions' = Build-Entry @((Get-Section 'List Transactions')) '' 'Example 200 - Transactions retrieved'
    'Transactions / Filter Transactions' = Build-Entry @((Get-Section 'List Transactions')) 'This collection request applies the documented optional filters: type=deposit, direction=credit, status=completed.' 'Example 200 - Filtered transactions retrieved'
    'Transactions / Transaction Detail' = Build-Entry @((Get-Section 'Transaction Detail')) '' 'Example 200 - Transaction retrieved'

    'Notifications / List Notifications' = Build-Entry @((Get-Section 'List Notifications')) '' 'Example 200 - Notifications retrieved'
    'Notifications / Mark Notification Read' = Build-Entry @((Get-Section 'Mark Notification Read')) '' 'Example 200 - Notification marked read'
    'Notifications / Mark All Notifications Read' = Build-Entry @((Get-Section 'Mark All Notifications Read')) '' 'Example 200 - All notifications marked read'
}

$missing = @()
foreach ($folder in $collection.item) {
    foreach ($item in $folder.item) {
        $key = "$($folder.name) / $($item.name)"
        if (-not $map.ContainsKey($key)) {
            $missing += $key
            continue
        }

        $item.request.description = $map[$key].Description
        $item.response = @($map[$key].Responses)
    }
}

if ($missing.Count -gt 0) {
    Write-Output 'Missing mappings:'
    $missing | ForEach-Object { Write-Output $_ }
    exit 1
}

$collection | ConvertTo-Json -Depth 100 | Set-Content -Path $collectionPath -Encoding UTF8
Write-Output "Updated Postman descriptions and responses for $($map.Count) requests."
