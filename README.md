```markdown
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

You can install the package via Composer directly from Packagist into your Laravel project:

```bash
composer require abdelhmed/sentinel-ai

```

---

## 🛠️ Publish Config and Migrations

After requiring the package, you need to publish the configuration file and database migrations using the following Artisan commands:

```bash
php artisan vendor:publish --tag=sentinel-config
php artisan vendor:publish --tag=sentinel-migrations

```

---

## 🗄️ Run Migrations

Once the migration file has been published, run your database migrations to create the `sentinel_logs` table in your database:

```bash
php artisan migrate

```

---

## ⚙️ Configuration

The package relies primarily on **Google Gemini AI**. Head to your project's `.env` file and add your Gemini API key:

```env
GEMINI_API_KEY=your_google_gemini_api_key_here

```

The configuration file (`config/sentinel.php`) will automatically handle the Gemini model and driver connections.

---

## 🚀 Usage

1. **Error Tracking:** The package automatically runs in the background when an exception occurs, sending it to Gemini for analysis and saving it to the database.
2. **Dashboard Access:** Once your project is running, navigate to the following URL in your browser to view errors and AI insights:
```text
[http://127.0.0.1:8000/sentinel/logs](http://127.0.0.1:8000/sentinel/logs)

```


3. **Clearing Logs:** You can clear all logs anytime directly from the dashboard using the "Clear All" button.

---

## 📄 License

This package is open-source software licensed under the [MIT license](https://www.google.com/search?q=LICENSE&utm_source=gemini).

```

```