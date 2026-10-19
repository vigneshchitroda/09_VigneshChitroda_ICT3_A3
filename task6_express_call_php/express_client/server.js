// task6_express_call_php/express_client/server.js
const express = require('express');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT || 5001;
const PHP_API_BASE = 'http://localhost/php_assignment/task6_express_call_php/php_api/api.php';

app.use(cors());
app.use(express.urlencoded({ extended: true }));
app.use(express.json());

// Proxy endpoint returning pure JSON received from PHP API
app.get('/api/proxy-students', async (req, res) => {
  try {
    const response = await fetch(`${PHP_API_BASE}?endpoint=students`);
    if (!response.ok) {
      throw new Error(`PHP API responded with HTTP status ${response.status}`);
    }
    const data = await response.json();
    res.json({
      caller: 'Express.js Application (Port 5001)',
      target: 'PHP REST API (Apache/MySQL)',
      result: data
    });
  } catch (error) {
    res.status(500).json({
      caller: 'Express.js Application (Port 5001)',
      error: error.message,
      suggestion: 'Ensure Apache is running in XAMPP on localhost'
    });
  }
});

// POST route from Express form forwarding to PHP REST API
app.post('/add-student', async (req, res) => {
  try {
    const { roll_no, name, email, course, marks } = req.body;
    const response = await fetch(PHP_API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ roll_no, name, email, course, marks: parseInt(marks, 10) })
    });

    const result = await response.json();
    res.redirect('/?msg=' + encodeURIComponent(result.message || 'Student added via PHP API'));
  } catch (err) {
    res.redirect('/?error=' + encodeURIComponent(err.message));
  }
});

// Main HTML Dashboard rendered by Express
app.get('/', async (req, res) => {
  let students = [];
  let rawJson = '';
  let apiStatus = 'OFFLINE';
  let phpVersion = 'Unknown';
  let fetchError = null;

  try {
    // 1. Check PHP API status
    const statusRes = await fetch(`${PHP_API_BASE}?endpoint=status`);
    if (statusRes.ok) {
      const statusData = await statusRes.json();
      apiStatus = statusData.status || 'ONLINE';
      phpVersion = statusData.php_version || '8.x';
    }

    // 2. Fetch students from PHP REST API
    const studentsRes = await fetch(`${PHP_API_BASE}?endpoint=students`);
    if (studentsRes.ok) {
      const studentsData = await studentsRes.json();
      students = studentsData.data || [];
      rawJson = JSON.stringify(studentsData, null, 2);
    }
  } catch (err) {
    fetchError = err.message;
  }

  const queryMsg = req.query.msg || '';
  const queryErr = req.query.error || '';

  const studentRows = students.map(s => `
    <tr>
      <td><strong>${escapeHtml(s.roll_no)}</strong></td>
      <td>
        ${escapeHtml(s.name)}
        ${['vignesh', 'sahil', 'kais', 'harsh', 'manan'].includes(s.name.toLowerCase()) ? '<span class="badge badge-success" style="font-size: 0.7rem;">Requirement</span>' : ''}
      </td>
      <td><small>${escapeHtml(s.email || '')}</small></td>
      <td><span class="badge badge-secondary">${escapeHtml(s.course || 'MCA')}</span></td>
      <td><strong style="color: #2563eb;">${s.marks}</strong></td>
    </tr>
  `).join('');

  const html = `
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Task 6: Express Calling PHP REST API</title>
  <link rel="stylesheet" href="http://localhost/php_assignment/assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="container">
    <a class="navbar-brand" href="/">🟢 Task 6: Express &rarr; PHP REST API</a>
    <ul class="navbar-nav">
      <li><a class="nav-link" href="http://localhost/php_assignment/index.php">🏠 Assignment Portal</a></li>
      <li><a class="nav-link" href="/api/proxy-students" target="_blank">📋 Raw Proxy JSON</a></li>
      <li><a class="nav-link" href="http://localhost/php_assignment/task6_express_call_php/php_api/api.php?endpoint=students" target="_blank">🐘 Direct PHP API</a></li>
    </ul>
  </div>
</nav>

<div class="container" style="padding-top: 30px;">
  <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
      <h1 style="font-size: 1.8rem; font-weight: 800; color: #0f172a;">Task 6: Call PHP REST API in Express</h1>
      <p style="color: #64748b;">
        This page is rendered by <strong>Express.js on port ${PORT}</strong>, which invokes the <strong>PHP REST API</strong> on Apache via HTTP <code>fetch()</code>.
      </p>
    </div>
    <div>
      ${apiStatus === 'ONLINE' 
        ? `<span class="badge badge-success" style="font-size: 0.9rem; padding: 8px 14px;">🟢 PHP REST API Connected (PHP ${phpVersion})</span>` 
        : `<span class="badge badge-danger" style="font-size: 0.9rem; padding: 8px 14px;">🔴 PHP REST API Offline</span>`}
    </div>
  </div>

  ${queryMsg ? `<div class="alert alert-success">${escapeHtml(queryMsg)}</div>` : ''}
  ${queryErr || fetchError ? `<div class="alert alert-danger">${escapeHtml(queryErr || fetchError)}</div>` : ''}

  <div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
    <!-- Form sending POST from Express to PHP API -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">➕ Forward POST to PHP API</h3>
      </div>
      <form method="POST" action="/add-student">
        <div class="form-group">
          <label class="form-label" for="roll_no">Roll Number *</label>
          <input type="text" id="roll_no" name="roll_no" class="form-control" placeholder="107" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="name">Student Name *</label>
          <input type="text" id="name" name="name" class="form-control" placeholder="Student Name" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="email">Email</label>
          <input type="email" id="email" name="email" class="form-control" placeholder="student@example.com">
        </div>
        <div class="form-group">
          <label class="form-label" for="course">Course</label>
          <input type="text" id="course" name="course" class="form-control" placeholder="MCA" value="MCA">
        </div>
        <div class="form-group">
          <label class="form-label" for="marks">Marks</label>
          <input type="number" id="marks" name="marks" class="form-control" placeholder="90" value="92">
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 12px;">
          🚀 Express Sends POST to PHP API
        </button>
      </form>
    </div>

    <!-- Data Table returned from PHP API -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">📦 Data Received from PHP API (${students.length} records)</h3>
        <a href="/" class="btn btn-secondary btn-sm">🔄 Refresh</a>
      </div>

      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Roll</th>
              <th>Name</th>
              <th>Email</th>
              <th>Course</th>
              <th>Marks</th>
            </tr>
          </thead>
          <tbody>
            ${studentRows || '<tr><td colspan="5" style="text-align:center; padding: 20px;">No students returned from PHP API.</td></tr>'}
          </tbody>
        </table>
      </div>

      <!-- Raw JSON preview -->
      <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
        <h4 style="font-size: 0.95rem; color: #64748b; margin-bottom: 8px;">Raw JSON Received by Express from PHP:</h4>
        <pre style="background: #0f172a; color: #38bdf8; padding: 14px; border-radius: 6px; font-size: 0.85rem; overflow-x: auto; max-height: 220px;">${escapeHtml(rawJson || 'No data')}</pre>
      </div>
    </div>
  </div>
</div>

<footer>
  <div class="container">
    <p>&copy; ${new Date().getFullYear()} PHP Practical Assignment - Task 6: Express Calling PHP REST API</p>
  </div>
</footer>
</body>
</html>
  `;

  res.send(html);
});

function escapeHtml(text) {
  if (!text) return '';
  return String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

app.listen(PORT, () => {
  console.log(`[Express PHP Consumer] Server listening on http://localhost:${PORT}`);
});
