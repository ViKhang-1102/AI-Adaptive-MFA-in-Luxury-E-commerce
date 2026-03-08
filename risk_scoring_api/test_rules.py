import requests

url = "http://localhost:5000/risk-score"

print("--- Test 1: Bulk Purchase > $5000 from New Device ---")
payload1 = {
    "user_id": 1,
    "amount": 5500.0,
    "login_time": "2026-03-07T03:00:00+00:00",
    "ip_change_count": 0,
    "device_is_new": True,
    "historical_avg_amount": 100.0
}
resp1 = requests.post(url, json=payload1)
print(resp1.json())

print("\n--- Test 2: Bulk Purchase > $5000 from Trusted Device ---")
payload2 = {
    "user_id": 1,
    "amount": 5500.0,
    "login_time": "2026-03-07T03:00:00+00:00",
    "ip_change_count": 0,
    "device_is_new": False,
    "historical_avg_amount": 100.0
}
resp2 = requests.post(url, json=payload2)
print(resp2.json())

print("\n--- Test 3: Behavioral Memory (Normal Purchase matching history) ---")
payload3 = {
    "user_id": 1,
    "amount": 120.0,
    "login_time": "2026-03-07T14:00:00+00:00",
    "ip_change_count": 0,
    "device_is_new": False,
    "historical_avg_amount": 100.0
}
resp3 = requests.post(url, json=payload3)
print(resp3.json())
