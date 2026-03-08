$url = "http://localhost:5000/risk-score"

Write-Host "--- Test 1: Bulk Purchase > `$5000 from New Device ---"
$payload1 = @{
    user_id               = 1
    amount                = 5500.0
    login_time            = "2026-03-07T03:00:00+00:00"
    ip_change_count       = 0
    device_is_new         = $true
    historical_avg_amount = 100.0
} | ConvertTo-Json
$response = Invoke-RestMethod -Uri $url -Method Post -Body $payload1 -ContentType "application/json"
$response | ConvertTo-Json

Write-Host "`n--- Test 2: Login from New Device ---"
$payload2 = @{
    user_id               = 1
    amount                = 0.0
    login_time            = "2026-03-07T03:00:00+00:00"
    ip_change_count       = 0
    device_is_new         = $true
    historical_avg_amount = 100.0
} | ConvertTo-Json
$response2 = Invoke-RestMethod -Uri $url -Method Post -Body $payload2 -ContentType "application/json"
$response2 | ConvertTo-Json
