# SMIS - System Functionality & Module Breakdown

This document provides a detailed explanation of every core function and module within the Smart School Data Gathering & Management System (SMIS). It breaks down what each feature does and how it interacts with the different user roles.

---

## 1. User & Role Management Module
*This module handles who can access the system and what they are allowed to do.*

- **Role-Based Access Control (RBAC):** The foundational security mechanism. It assigns distinct permissions to Administrators, Officers, Teachers, and Students, ensuring users only see interfaces and data relevant to their responsibilities.
- **User Registration & Enrollment:** Allows administrators and officers to create accounts. For students, it handles the enrollment process (assigning admission numbers and initial classes). For staff, it sets up their system credentials.
- **Profile Management:** Individual users can manage their personal profiles (e.g., updating contact info or passwords), while administrators retain the ability to suspend or activate accounts globally.

## 2. Academic Structure Management
*This module defines the architectural hierarchy of the school.*

- **Grade & Class Configuration:** Facilitates the creation of academic levels (e.g., Grade 1 to 13) and individual class sections (e.g., 10-A, 10-B).
- **Subject & Stream Management:** Administrators can define the curriculum by adding subjects and organizing them into academic streams (like Science, Arts, Commerce for senior students).
- **Teacher Assignments:** A critical linking function where officers assign teachers to specific roles:
  - *Class Teacher:* Responsible for the overall administration of a specific class.
  - *Subject Teacher:* Responsible for teaching specific subjects to designated classes.

## 3. Attendance Tracking Module
*Replaces the traditional paper register with a digital tracking system.*

- **Student Session Attendance:** Allows Class Teachers or Subject Teachers to rapidly mark students as Present, Absent, or Late for the day or for a specific subject period.
- **Staff Attendance:** A simplified punch-in system to track the daily presence of teachers and officers.
- **Attendance Analytics & Alerts:** The system automatically aggregates daily data into monthly statistics. It can flag students who fall below a required attendance threshold, allowing administration to intervene early.

## 4. Timetable Management Module
*Handles the complex task of school scheduling.*

- **Grid-Based Scheduling:** Provides an interactive UI for officers to build weekly timetables, assigning subjects, teachers, and time slots (periods) to classes.
- **Conflict Detection Engine:** An automated validation check that prevents double-booking. The system ensures a teacher is never assigned to two different classes at the exact same time.
- **Personalized Views:** Once published, the timetable is filtered per user. A student sees only their class schedule, while a teacher sees only the periods they are assigned to teach across various classes.

## 5. Examination & Grading Module
*Digitizes the testing, grading, and result publication workflow.*

- **Exam Configuration:** Administrators can set up different exam cycles (e.g., First Term Test, Mid-Term, O/L Mock Exams) and specify which subjects are tested.
- **Marks Entry System:** Provides a secure, tabular interface for subject teachers to enter raw marks. It includes real-time validation to prevent invalid entries (e.g., entering 105 out of 100).
- **Automated Grading Logic:** The system instantly converts raw marks into standardized grades (A, B, C, S, F) based on predefined school or national criteria, eliminating manual calculation errors.
- **Result Publishing:** Exam results are kept hidden during the marking phase. Once finalized, an administrator "publishes" them, making the report cards visible on the student portals.

## 6. Lesson & E-Learning Module
*A digital hub for academic resources and curriculum progression.*

- **Lesson Planning & Organization:** Teachers can create structured digital lesson plans, organizing topics by subject and term.
- **Resource Repository:** Allows teachers to upload supplementary study materials, PDFs, presentations, or external links for their students.
- **Student Access Portal:** Students can asynchronously access these lessons, download study materials, and review topics discussed in class.

## 7. Reporting & Analytics Module
*Provides high-level overviews and printable documents for stakeholders.*

- **Interactive Dashboards:** Each role has a tailored dashboard with key metrics (e.g., an Admin sees total school enrollment; a Teacher sees their class's average performance).
- **Document Generation:** The system can automatically generate and format downloadable PDF or CSV files for Student Transcripts, Class Performance Summaries, and blank Attendance Sheets.
- **At-Risk Identification:** Analytical tools that combine attendance and exam data to highlight students who are struggling academically or chronically absent.

## 8. SMIS AI Agent Module
*An innovative, context-aware digital assistant powered by Generative AI (Google Gemini).*

- **Natural Language Processing (NLP):** Users can interact with the system using plain text instead of navigating menus. (e.g., "Show me the top 3 students in 10-A for Mathematics").
- **Role-Scoped Execution:** The AI is tightly integrated with the RBAC system. If a student asks the AI to change their marks, the AI will refuse based on policy constraints.
- **Automated Data Retrieval:** The AI can query the database in the background to provide instant insights, summaries, and reports directly in the chat interface.

## 9. Security & Audit Logging
*Under-the-hood features ensuring data integrity and safety.*

- **Activity Audit Trail:** Automatically logs critical system changes. If a student's mark is altered after publication, the system records *who* made the change and *when*, providing full traceability.
- **Data Compartmentalization:** Strict database querying rules ensure that users cannot bypass the UI to access unauthorized data (e.g., a teacher cannot access the database records of a class they do not teach).

## 10. Technology Stack
*The core technologies driving the SMIS application.*

- **Backend Framework:** **Laravel 13 (PHP 8.3)** - Provides robust routing, MVC architecture, Eloquent ORM for database management, and built-in security features.
- **Frontend Reactive Framework:** **Livewire 4** & **Alpine.js** - Allows for dynamic, real-time, single-page-application (SPA) like behavior without writing heavy custom JavaScript.
- **UI Components & Styling:** **Flux UI** & **Tailwind CSS** - Ensures a highly responsive, modern, and accessible user interface tailored for both desktop and mobile devices.
- **Database:** **MySQL** - A reliable relational database management system for securely storing structured school data.
- **Authentication & Authorization:** **Laravel Fortify** (headless authentication backend) combined with **Spatie Laravel Permission** (for granular role and permission management).
- **AI Integration:** **Google Gemini API** - Powers the SMIS AI Agent for natural language queries and automated insights.
- **PDF Generation:** **DomPDF** - Used for generating downloadable, printable documents like report cards and attendance sheets.
