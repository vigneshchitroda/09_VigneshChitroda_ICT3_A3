// task5_php_call_express/express_api/server.js
const express = require('express');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT || 5000;

app.use(cors());
app.use(express.json());

// In-memory data store seeded with required student records
let students = [
  { id: 1, roll_no: "101", name: "Vignesh", email: "vignesh@example.com", course: "MCA", marks: 95 },
  { id: 2, roll_no: "102", name: "Sahil",   email: "sahil@example.com",   course: "MCA", marks: 91 },
  { id: 3, roll_no: "103", name: "Kais",    email: "kais@example.com",    course: "MCA", marks: 88 },
  { id: 4, roll_no: "104", name: "Harsh",   email: "harsh@example.com",   course: "MCA", marks: 93 },
  { id: 5, roll_no: "105", name: "Manan",   email: "manan@example.com",   course: "MCA", marks: 89 }
];

let nextId = 6;

// Health check endpoint
app.get('/api/health', (req, res) => {
  res.json({
    status: 'ONLINE',
    server: 'Express REST API',
    port: PORT,
    timestamp: new Date().toISOString()
  });
});

// GET all students
app.get('/api/students', (req, res) => {
  res.json({
    status: 'success',
    count: students.length,
    data: students
  });
});

// GET single student by ID
app.get('/api/students/:id', (req, res) => {
  const id = parseInt(req.params.id, 10);
  const student = students.find(s => s.id === id);
  if (!student) {
    return res.status(404).json({ status: 'error', message: 'Student not found' });
  }
  res.json({ status: 'success', data: student });
});

// POST create new student
app.post('/api/students', (req, res) => {
  const { roll_no, name, email, course, marks } = req.body;
  if (!roll_no || !name) {
    return res.status(400).json({ status: 'error', message: 'roll_no and name are required' });
  }

  const newStudent = {
    id: nextId++,
    roll_no: String(roll_no),
    name: String(name),
    email: email || '',
    course: course || 'MCA',
    marks: parseInt(marks, 10) || 0
  };

  students.push(newStudent);
  res.status(201).json({
    status: 'success',
    message: 'Student added successfully via Express REST API',
    data: newStudent
  });
});

// DELETE student by ID
app.delete('/api/students/:id', (req, res) => {
  const id = parseInt(req.params.id, 10);
  const initialLength = students.length;
  students = students.filter(s => s.id !== id);

  if (students.length === initialLength) {
    return res.status(404).json({ status: 'error', message: 'Student not found' });
  }

  res.json({ status: 'success', message: `Student ID ${id} deleted successfully` });
});

app.listen(PORT, () => {
  console.log(`[Express REST API] Server listening on http://localhost:${PORT}`);
});
