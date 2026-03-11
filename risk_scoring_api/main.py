import os
import random
import json
import numpy as np
import uvicorn
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field
from sklearn.ensemble import IsolationForest
from openai import OpenAI
from dotenv import load_dotenv
from typing import Any, Dict, List, Optional
from datetime import datetime

load_dotenv()

app = FastAPI(title="AI-Driven Security Guard API (English Logic)")

# ---------------------------------------------------------
# OpenAI Key Rotation Logic
# ---------------------------------------------------------
def get_openai_keys():
    keys_str = os.getenv("OPENAI_API_KEYS", "")
    return [k.strip() for k in keys_str.split(",") if k.strip()]

# ---------------------------------------------------------
# Data Models (English Only)
# ---------------------------------------------------------
class TransactionInput(BaseModel):
    user_id: int
    amount: float
    payment_method: Optional[str] = "unknown"
    login_time: str
    ip_change_count: int
    device_is_new: bool
    historical_avg_amount: float = 0.0
    ip_address: Optional[str] = "unknown"
    location: Optional[str] = "unknown"
    device_fingerprint: Optional[str] = "unknown"

class RiskScoreOutput(BaseModel):
    risk_score: float
    level: str
    suggestion: str
    explanation: Dict[str, Any]

# ---------------------------------------------------------
# Statistical Anomaly Detection (Isolation Forest)
# ---------------------------------------------------------
dummy_data = np.array([
    [50.0, 0], [120.0, 0], [30.0, 1], [400.0, 0], [15.0, 0],
    [200.0, 0], [80.0, 0], [95.0, 2], [5.0, 0], [10.0, 0],
    [60.0, 1], [35.0, 0], [250.0, 0],
    [5000.0, 5], [10000.0, 2], [8000.0, 0] # Potential outliers
])
iso_forest = IsolationForest(n_estimators=100, contamination=0.1, random_state=42)
iso_forest.fit(dummy_data)

async def get_openai_analysis(data: TransactionInput) -> Dict[str, Any]:
    """Uses OpenAI GPT-4o-mini as a Security Guard Agent for deep analysis with auto-retry on different keys."""
    keys = get_openai_keys()
    
    if not keys:
        # Fallback if no keys are provided
        mock_score = 0
        mock_reason = "OpenAI API Keys not configured. Using heuristic fallback."
        if data.amount > 5000:
            mock_score = 80
            mock_reason = "Rule-based: High-value transaction detected with no AI analysis."
        return {"score": mock_score, "reason": mock_reason}
    
    # Try up to 3 random keys if there are failures
    attempts = min(3, len(keys))
    tried_keys = []
    
    prompt = f"""
    Analyze the following e-commerce transaction for Account Takeover (ATO) or fraud.
    Context:
    - User ID: {data.user_id}
    - Current Amount: ${data.amount}
    - Payment Method: {data.payment_method}
    - Historical Average: ${data.historical_avg_amount}
    - Timestamp: {data.login_time}
    - Network IP: {data.ip_address} (IP changes: {data.ip_change_count})
    - Geolocation: {data.location}
    - Hardware State: {'NEW DEVICE' if data.device_is_new else 'KNOWN DEVICE'}
    - Hardware Fingerprint: {data.device_fingerprint}

    Requirements:
    1. Assess the risk on a scale of 0-100 based on behavioral patterns.
    2. Provide a concise explanation in ENGLISH for the admin dashboard.
    3. Detect if this looks like a typical 'Flash Fraud' or 'ATO' attack.
    4. Consider the Payment Method: COD (Cash on Delivery) for high-value orders is riskier for the platform.
    5. Return ONLY a JSON object: {{"score": float, "reason": "string in English"}}
    """

    for _ in range(attempts):
        # Filter out empty or whitespace-only keys
        available_keys = [k for k in keys if k not in tried_keys and len(k) > 10]
        if not available_keys:
            break
            
        selected_key = random.choice(available_keys)
        tried_keys.append(selected_key)
        
        try:
            client = OpenAI(api_key=selected_key)
            response = client.chat.completions.create(
                model="gpt-4o-mini",
                messages=[
                    {"role": "system", "content": "You are a Senior Fraud Prevention AI specialized in behavioral biometrics and e-commerce security."},
                    {"role": "user", "content": prompt}
                ],
                response_format={ "type": "json_object" }
            )
            return json.loads(response.choices[0].message.content)
        except Exception as e:
            print(f"Key failure: {selected_key[:10]}... Error: {str(e)}")
            continue # Try next key
            
    return {"score": 50, "reason": "OpenAI analysis failed after multiple key attempts."}

@app.post("/risk-score", response_model=RiskScoreOutput)
async def calculate_risk_score(data: TransactionInput):
    try:
        # 1. Statistical Risk (Isolation Forest) - Weight 20%
        features = np.array([[data.amount, data.ip_change_count]])
        if_score_raw = iso_forest.score_samples(features)[0]
        statistical_risk = max(0, min(100, (0.5 - if_score_raw) * 100))
        
        # 2. Heuristic Rules (Business Logic) - Weight 30%
        heuristic_risk = 0
        reasons = []
        
        if data.amount > 10000:
            heuristic_risk += 60
            reasons.append(f"Critical: Extremely high transaction value (${data.amount})")
        elif data.amount > 5000:
            heuristic_risk += 50
            reasons.append(f"High: Large transaction value (${data.amount})")
        elif data.amount > (data.historical_avg_amount * 3) and data.historical_avg_amount > 0:
            heuristic_risk += 45
            reasons.append("Anomaly: Amount significantly exceeds historical average")
        elif data.amount > 1000:
            heuristic_risk += 30
            reasons.append("Warning: Above-average transaction value")
            
        # Additional risk for high-value COD orders
        if data.payment_method == 'cod' and data.amount > 2000:
            heuristic_risk += 100
            reasons.append("Security Warning: High-value Cash on Delivery order")
            
        if data.device_is_new:
            heuristic_risk += 45
            reasons.append("Security Alert: Login from a completely new device/browser")
            
        if data.ip_change_count >= 2:
            heuristic_risk += 35
            reasons.append(f"Security Alert: Multiple IP address changes detected ({data.ip_change_count})")

        if data.location == "Unknown":
            heuristic_risk += 20
            reasons.append("Warning: User location could not be determined.")

        # 3. AI Guard Agent Analysis (GPT-4o-mini) - Weight 50%
        ai_analysis = await get_openai_analysis(data)
        ai_risk = ai_analysis.get("score", 0)
        ai_reason = ai_analysis.get("reason", "No AI analysis available")

        # 4. Final Composite Risk Scoring
        final_score = (ai_risk * 0.5) + (heuristic_risk * 0.3) + (statistical_risk * 0.2)
        final_score = max(0.0, min(100.0, final_score))

        # 5. Determine Thresholds (Adaptive MFA Logic)
        if final_score >= 80.0:
            level = "critical"
            suggestion = "block"
        elif final_score >= 65.0:
            level = "high"
            suggestion = "faceid"
        elif final_score >= 30.0:
            level = "medium"
            suggestion = "otp"
        else:
            level = "low"
            suggestion = "allow"

        # 6. Construct Detailed Breakdown (English)
        breakdown = []
        if ai_risk > 0:
            breakdown.append(f"AI Analysis (+{round(ai_risk * 0.5, 1)}): {ai_reason}")
        if statistical_risk > 0:
            breakdown.append(f"Statistical (+{round(statistical_risk * 0.2, 1)}): Behavior matches fraudulent anomaly clusters.")
        if heuristic_risk > 0:
            for r in reasons:
                breakdown.append(f"Heuristic Rule: {r}")

        return RiskScoreOutput(
            risk_score=round(final_score, 2),
            level=level,
            suggestion=suggestion,
            explanation={
                "score_breakdown": breakdown,
                "ai_reasoning": ai_reason,
                "input_context": {
                    "amount": data.amount,
                    "ip": data.ip_address,
                    "location": data.location,
                    "device": data.device_fingerprint,
                    "is_new": data.device_is_new,
                    "history": data.historical_avg_amount,
                    "time": data.login_time
                }
            }
        )
        
    except Exception as e:
        return RiskScoreOutput(
            risk_score=50.0,
            level="medium",
            suggestion="otp",
            explanation={
                "error": str(e),
                "score_breakdown": ["Fallback applied due to internal AI processing error."],
                "input_context": {"amount": data.amount}
            }
        )

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=5000)
