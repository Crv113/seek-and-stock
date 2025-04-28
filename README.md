# Seek-And-Stock

📅 Q1 2025

👋 Hey ! **Seek-And-Stock** is the Laravel backend for my motocross timing project [mxbtiming.com](https://mxbtiming.com).  
It handles race result processing, data storage, and provides a RESTful API consumed by the frontend.

---

## 🏁 What It Does

- Receive and process live race data from a Node.js UDP client.
- Validates whether the race corresponds to an active event.
- Saves race data (laps, players, times, etc.) into a MySQL database.
- Exposes a **REST API** for the frontend to fetch event-related data.

---

## 🛠️ Tech Stack

- **PHP 8+** / **Laravel**
- **MySQL**
- **Docker** (for local and production environments)
- **Laravel Sanctum** (for authentication)
- **Scheduled Jobs** (for automatic XML processing)
- **REST API** (consumed by a React frontend)
