# 🛡️ Sentinel AI

**Sentinel AI** is a powerful open-source package for **Laravel** projects that automatically captures application exceptions, analyzes them deeply using **Google Gemini AI** to provide root causes and accurate fixes in both Arabic and English, and displays them in a clean, Tailwind CSS-powered Blade dashboard.

---

## ✨ Key Features

* **Automatic Error Capture:** Comprehensive monitoring of all exceptions and errors occurring in the application without manual logging code.
* **Smart AI Analysis:** Full reliance on Google's Gemini models to analyze errors, provide detailed explanations, and suggest code fixes.
* **Built-in Blade Dashboard:** A professional user interface designed with Tailwind CSS, available directly inside your project upon installation.
* **Plug & Play:** Requires no complex frontend setups (like Vue or React) and no complicated queue workers.
* **Full Log Management:** Easily browse errors, view file paths and line numbers, and clear old logs with a single click.

---

## 📦 Installation

You can install the package into your Laravel project via the following simple steps:

### 1. Add the Package to Your Project
If the package is local, add its repository path to your main project's `composer.json`:
```json
"repositories": [
    {
        "type": "path",
        "url": "packages/Abdelhmed/sentinel-ai"
    }
]
