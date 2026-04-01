import os
from typing import List, Dict

from flask import Flask, jsonify, request
from flask_cors import CORS
import firebase_admin
from firebase_admin import credentials, firestore
import google.generativeai as genai
from google.api_core.exceptions import FailedPrecondition

app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}})


def initialize_firestore():
    """Initialize Firebase app and return Firestore client."""
    if not firebase_admin._apps:
        key_path = "serviceAccountKey.json"
        if not os.path.exists(key_path):
            raise FileNotFoundError(
                "serviceAccountKey.json not found. Place it in the project folder."
            )
        cred = credentials.Certificate(key_path)
        firebase_admin.initialize_app(cred)
    return firestore.client()


def fetch_doctors() -> List[Dict]:
    """Fetch doctor records from Firestore collection: doctors."""
    db = initialize_firestore()
    try:
        docs = db.collection("doctors").stream()
        doctors = []
        for doc in docs:
            data = doc.to_dict() or {}
            doctors.append(
                {
                    "name": data.get("name", "N/A"),
                    "specialization": data.get("specialization", "N/A"),
                    "hospital": data.get("hospital", "N/A"),
                    "location": data.get("location", "N/A"),
                    "experience": data.get("experience", "N/A"),
                }
            )
        return doctors
    except FailedPrecondition as exc:
        # Happens when Firestore/Datastore is not initialized in the project.
        raise RuntimeError(
            "Firestore database is not initialized for this Firebase project. "
            "Open the Firebase/Google Cloud console and create a Firestore "
            "database in Native mode, then try again."
        ) from exc


def filter_doctors(doctors: List[Dict], query: str) -> List[Dict]:
    """Filter doctors by specialization or location based on user query."""
    q = (query or "").lower()
    filtered = [
        d
        for d in doctors
        if q in str(d.get("specialization", "")).lower()
        or q in str(d.get("location", "")).lower()
    ]

    if filtered:
        return filtered

    # Fallback: keyword match by words for partial queries
    words = [w for w in q.split() if len(w) > 2]
    partial = []
    for doctor in doctors:
        spec = str(doctor.get("specialization", "")).lower()
        loc = str(doctor.get("location", "")).lower()
        if any(word in spec or word in loc for word in words):
            partial.append(doctor)
    return partial if partial else doctors[:5]


def ask_gemini(query: str, filtered_doctors: List[Dict]) -> str:
    """Send filtered doctor list to Gemini and return recommendation."""
    api_key = os.getenv("GEMINI_API_KEY")
    if not api_key:
        return (
            "Gemini API key is missing. Please set GEMINI_API_KEY and try again. "
            "Here are matched doctors: " + str(filtered_doctors)
        )

    genai.configure(api_key=api_key)
    model = genai.GenerativeModel("gemini-1.5-flash")

    prompt = f"""
You are an AI medical assistant.
Based on the user query and doctor list below, suggest the best doctor.
Explain in simple language and include:
1) Doctor name
2) Hospital
3) Reason for recommendation

User query: {query}
Doctor list: {filtered_doctors}
""".strip()

    try:
        response = model.generate_content(prompt)
        if response and response.text:
            return response.text
        return "Gemini returned an empty response."
    except Exception as e:
        return f"Gemini request failed: {e}"


@app.route("/ask", methods=["GET", "POST", "OPTIONS"])
def ask():
    # Helpful for browser preflight visibility; Flask-CORS handles headers.
    if request.method == "OPTIONS":
        return jsonify({"message": "CORS preflight OK"}), 200

    if request.method == "GET":
        return "API is working", 200

    try:
        data = request.get_json(silent=True) or {}
        query = (data.get("query") or "").strip()
        if not query:
            return jsonify({"error": "Please provide a query in JSON body."}), 400

        doctors = fetch_doctors()
        if not doctors:
            return jsonify({"result": "No doctors found in Firestore collection 'doctors'."}), 200

        filtered = filter_doctors(doctors, query)
        ai_result = ask_gemini(query, filtered)

        return jsonify({
            "query": query,
            "matched_count": len(filtered),
            "matched_doctors": filtered,
            "result": ai_result,
        }), 200
    except RuntimeError as e:
        return jsonify(
            {
                "error": str(e),
                "next_step": (
                    "If you see 'database (default) does not exist', create it at "
                    "https://console.cloud.google.com/datastore/setup "
                    "for your project and then retry."
                ),
            }
        ), 503
    except Exception as e:
        return jsonify({"error": str(e)}), 500


if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000, debug=True)
