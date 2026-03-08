import requests

url = "http://localhost:5000/risk-score"
payload = {
    "user_id": 1,
    "amount": 5500.0,
    "login_time": "2026-03-07T03:00:00+00:00",
    "ip_change_count": 0,
    "device_is_new": True
}
response = requests.post(url, json=payload)
print(response.json())
