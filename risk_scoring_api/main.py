import os
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field
import numpy as np
from sklearn.ensemble import IsolationForest
import uvicorn
from openai import OpenAI
from dotenv import load_dotenv
from typing import Any, Dict, List, Optional
from datetime import datetime

load_dotenv()

app = FastAPI(title="AI-Driven Security Guard API")

# Initialize OpenAI Client
client = OpenAI(api_key=os.getenv("OPENAI_API_KEY"))

# Define Input Schema with comprehensive context
class TransactionInput(BaseModel):
    user_id: int
    amount: float
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
# Isolation Forest for Anomaly Detection (Statistical)
# ---------------------------------------------------------
dummy_data = np.array([
    [50.0, 0], [120.0, 0], [30.0, 1], [400.0, 0], [15.0, 0],
    [200.0, 0], [80.0, 0], [95.0, 2], [5.0, 0], [10.0, 0],
    [60.0, 1], [35.0, 0], [250.0, 0],
    [5000.0, 5], [10000.0, 2], [8000.0, 0] # Outliers
])
iso_forest = IsolationForest(n_estimators=100, contamination=0.1, random_state=42)
iso_forest.fit(dummy_data)
AVERAGE_AMOUNT = np.mean(dummy_data[:, 0])

async def get_openai_analysis(data: TransactionInput) -> Dict[str, Any]:
    """Uses OpenAI as a Guard Agent to analyze behavioral context."""
    if not os.getenv("OPENAI_API_KEY") or os.getenv("OPENAI_API_KEY") == "your_openai_api_key_here":
        # Mock logic for testing if API key is missing
        mock_score = 0
        mock_reason = "OpenAI API Key not configured."
        if data.amount > 5000:
            mock_score = 85
            mock_reason = "Mô phỏng AI: Số tiền giao dịch rất lớn, tiềm ẩn rủi ro chiếm đoạt tài khoản cao."
        elif data.amount > 1000:
            mock_score = 50
            mock_reason = "Mô phỏng AI: Giao dịch có giá trị trung bình, cần xác thực thêm."
        return {"score": mock_score, "reason": mock_reason}
    
    prompt = f"""
    Hãy phân tích giao dịch thương mại điện tử sau đây để phát hiện rủi ro chiếm đoạt tài khoản (Account Takeover) hoặc hành vi gian lận.
    Ngữ cảnh:
    - User ID: {data.user_id}
    - Số tiền: ${data.amount}
    - Trung bình lịch sử: ${data.historical_avg_amount}
    - Thời gian: {data.login_time}
    - Địa chỉ IP: {data.ip_address}
    - Số lần thay đổi IP: {data.ip_change_count}
    - Vị trí: {data.location}
    - Thiết bị mới: {'Có' if data.device_is_new else 'Không'}
    - Dấu vân tay thiết bị: {data.device_fingerprint}

    Yêu cầu:
    1. Đánh giá mức độ rủi ro trên thang điểm 0-100.
    2. Cung cấp lời giải thích ngắn gọn bằng tiếng Việt cho điểm số này.
    3. Trả về kết quả dưới định dạng JSON: {{"score": float, "reason": "chuỗi giải thích bằng tiếng Việt"}}
    """

    try:
        response = client.chat.completions.create(
            model="gpt-4o-mini",
            messages=[
                {"role": "system", "content": "You are a Senior Security AI Agent specialized in detecting e-commerce fraud and Account Takeover."},
                {"role": "user", "content": prompt}
            ],
            response_format={ "type": "json_object" }
        )
        import json
        result = json.loads(response.choices[0].message.content)
        return result
    except Exception as e:
        return {"score": 50, "reason": f"OpenAI Analysis failed: {str(e)}"}

@app.post("/risk-score", response_model=RiskScoreOutput)
async def calculate_risk_score(data: TransactionInput):
    try:
        # 1. Statistical Anomaly Detection (Isolation Forest)
        features = np.array([[data.amount, data.ip_change_count]])
        if_score_raw = iso_forest.score_samples(features)[0]
        # Normalize Isolation Forest score to 0-100 (more negative = more anomalous)
        statistical_risk = max(0, min(100, (0.5 - if_score_raw) * 100))
        
        # 2. Heuristic Rules (Business Logic)
        heuristic_risk = 0
        reasons = []
        
        if data.amount > 5000:
            heuristic_risk += 50
            reasons.append(f"Số tiền giao dịch rất cao (${data.amount})")
        elif data.amount > (data.historical_avg_amount * 3) and data.historical_avg_amount > 0:
            heuristic_risk += 45
            reasons.append("Số tiền vượt xa mức trung bình lịch sử")
        elif data.amount > 1000:
            heuristic_risk += 30
            reasons.append("Giao dịch giá trị trung bình cao")
            
        if data.device_is_new:
            heuristic_risk += 40
            reasons.append("Đăng nhập từ thiết bị hoàn toàn mới")
            
        if data.ip_change_count >= 2:
            heuristic_risk += 30
            reasons.append(f"Phát hiện nhiều thay đổi địa chỉ IP ({data.ip_change_count})")

        # 3. AI Guard Agent Analysis (Deep Context)
        ai_analysis = await get_openai_analysis(data)
        ai_risk = ai_analysis.get("score", 0)
        ai_reason = ai_analysis.get("reason", "Không có phân tích AI")

        # 4. Composite Risk Scoring (Weighted Average)
        # Weights: AI (50%), Heuristics (30%), Statistical (20%)
        final_score = (ai_risk * 0.5) + (heuristic_risk * 0.3) + (statistical_risk * 0.2)
        final_score = max(0.0, min(100.0, final_score))

        # Determine Level and Suggestion
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

        # Construct Detailed Breakdown
        breakdown = []
        if ai_risk > 0:
            breakdown.append(f"Phân tích OpenAI (+{round(ai_risk * 0.5, 1)}): {ai_reason}")
        if statistical_risk > 0:
            breakdown.append(f"Bất thường thống kê (+{round(statistical_risk * 0.2, 1)}): Mô hình phát hiện các mẫu hành vi không bình thường.")
        if heuristic_risk > 0:
            for r in reasons:
                breakdown.append(f"Quy tắc nghiệp vụ: {r}")

        return RiskScoreOutput(
            risk_score=round(final_score, 2),
            level=level,
            suggestion=suggestion,
            explanation={
                "score_breakdown": breakdown,
                "ai_reasoning": ai_reason,
                "input": {
                    "total_amount": data.amount,
                    "ip": data.ip_address,
                    "location": data.location,
                    "device": data.device_fingerprint,
                    "is_new_device": data.device_is_new,
                    "historical_avg": data.historical_avg_amount,
                    "login_time": data.login_time
                }
            }
        )
        
    except Exception as e:
        # Robust error handling to always return a valid JSON structure
        return RiskScoreOutput(
            risk_score=50.0,
            level="medium",
            suggestion="otp",
            explanation={
                "error": str(e),
                "score_breakdown": ["Fallback to default due to internal API error."],
                "input": {"amount": data.amount}
            }
        )

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=5000)
