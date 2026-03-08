# AI Risk Assessment & Scoring Model Report

This report outlines the criteria, weights, and logic used by the LuxGuard AI Security Engine to detect fraudulent transactions and Account Takeover (ATO) attempts.

## 1. Core Risk Factors & Scoring

The final risk score is a composite of three primary analysis engines:

| Engine | Weight | Description |
| :--- | :--- | :--- |
| **AI Guard Agent (OpenAI)** | **50%** | Deep behavioral analysis using LLM to detect intent and complex fraud patterns. |
| **Heuristic Rules** | **30%** | Hard-coded business logic for immediate threat detection (e.g., massive amounts). |
| **Statistical Anomaly** | **20%** | Isolation Forest model detecting deviations from standard e-commerce behavior. |

---

## 2. Heuristic Scoring Breakdown (Max 100)

| Trigger Condition | Risk Addition | Rationale |
| :--- | :--- | :--- |
| **Amount > $10,000** | **+60 Points** | Extremely high value transaction, high financial impact if fraudulent. |
| **Amount > $5,000** | **+50 Points** | High value transaction requiring elevated verification. |
| **Amount > 3x History** | **+45 Points** | Significant deviation from the user's typical spending habits. |
| **Amount > $1,000** | **+30 Points** | Medium-high value transaction. |
| **New Device Login** | **+45 Points** | Login from a previously unseen browser or hardware fingerprint. |
| **Multiple IP Changes** | **+35 Points** | Session hopping across different networks/locations (VPN or proxy usage). |

---

## 3. Adaptive MFA Response Thresholds

Based on the **Final Risk Score (0-100)**, the system dynamically selects the friction level:

| Score Range | Level | Action Taken |
| :--- | :--- | :--- |
| **0 - 29** | **Low** | **Allow**: No interruption. Seamless checkout. |
| **30 - 64** | **Medium** | **OTP**: Requires 6-digit email verification. |
| **65 - 79** | **High** | **FaceID**: Mandatory 3D liveness scan required. |
| **80 - 100** | **Critical** | **Block**: Transaction denied. Account temporarily restricted. |

**FaceID Matching Thresholds:** FaceID uses a local LBPH matcher tuned for demo environments. A match is accepted when the raw LBPH distance is **<= 65.0** and the computed match confidence is **>= 0.45**.

---

## 4. AI Guard Agent Logic (OpenAI Integration)

The Guard Agent analyzes the following context:
- **Velocity**: How fast is the user transacting?
- **Geolocation**: Is the IP location consistent with previous behavior?
- **Fingerprinting**: Does the hardware signature match the profile?
- **Time Analysis**: Is the transaction occurring at an unusual hour for this user?

*The OpenAI engine provides a "Reasoning" string back to the Admin Dashboard for transparent audit logs.*
