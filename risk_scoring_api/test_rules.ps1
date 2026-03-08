$url = "http://localhost:5000/risk-score"

Write-Host "--- Test 1: Bulk Purchase > `$5000 from New Device ---"
$payload1 = @{
    user_id = 1
    amount = 5500.0
    login_time = "2026-03-07T03:00:00+00:00"
    ip_change_count = 0
    device_is_new = $true
    historical_avg_amount = 100.0
} | ConvertTo-Json
Invoke-RestMethod -Uri $url -Method Post -Body $payload1 -ContentType "application/json" | ConvertTo-Json

Write-Host "`n--- Test 2: Bulk Purchase > `$5000 from Trusted Device ---"
$payload2 = @{
    user_id = 1
    amount = 5500.0
    login_time = "2026-03-07T03:00:00+00:00"
    ip_change_count = 0
    device_is_new = $false
    historical_avg_amount = 100.0
} | ConvertTo-Json
Invoke-RestMethod -Uri $url -Method Post -Body $payload2 -ContentType "application/json" | ConvertTo-Json

Write-Host "`n--- Test 3: Behavioral Memory (Normal Purchase matching history) ---"
$payload3 = @{
    user_id = 1
    amount = 120.0
    login_time = "2026-03-07T14:00:00+00:00"
    ip_change_count = 0
    device_is_new = $false
    historical_avg_amount = 100.0
} | ConvertTo-Json
Invoke-RestMethod -Uri $url -Method Post -Body $payload3 -ContentType "application/json" | ConvertTo-Json
