# Cloud Training & Student Portal
 
A web application for a 4-week Cloud & DevOps training program, containerized with Docker and Docker Compose. Students can browse the curriculum, enroll in a training track, and submit feedback. All submissions are stored in a MySQL database that can be inspected through phpMyAdmin.
 
## Features
 
- Multi-page responsive website: Home, Courses, Register, Feedback
- 4-week curriculum: Linux, Cloud, Git/CI-CD, Docker
- Enrollment form with track and experience-level selection
- Feedback form with rating and comments
- Client-side validation (JavaScript) and server-side validation (PHP)
- Database tables created automatically on first connection
- phpMyAdmin included for viewing stored data
## Tech Stack
 
| Layer      | Technology                         |
|------------|------------------------------------|
| Frontend   | HTML5, CSS3, vanilla JavaScript    |
| Backend    | PHP 8.1 (Apache)                   |
| Database   | MySQL 8.0                          |
| DB Admin   | phpMyAdmin                         |
| DevOps     | Docker, Docker Compose             |
 
## Project Structure
 
```
cloudtraining&studentportal/
├── docker-compose.yml
├── README.md
├── apache-php/
│   └── Dockerfile          
└── website/
    ├── index.html          
    ├── courses.html        
    ├── register.html       
    ├── feedback.html       
    ├── style.css           
    ├── script.js           
    ├── db.php              
    └── submit.php          
```
 
## Architecture
 
```
Browser ──► web (Apache + PHP, :9000) ──► db (MySQL 8.0)
                                              ▲
Browser ──► phpmyadmin (:9001) ───────────────┘
```
 
- `web` mounts `./website` to `/var/www/html`, so file edits show up immediately without a rebuild.
- `db` stores data in the named volume `db_data`, so data survives container restarts.
- `phpmyadmin` connects to the `db` service over the Compose network.
- Database credentials are passed to the web container as environment variables and read in `db.php`.
## Prerequisites
 
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (or Docker Engine + Compose plugin)
- A free port for each service (defaults: 9000, 9001)
## Getting Started
 
1. Clone or download the project and open a terminal in the project folder:
```bash
   cd my-website-project
```
 
2. Build and start all services:
```bash
   docker compose up -d --build
```
 
3. Wait about 20-30 seconds on first run while MySQL initializes.
4. Open the app:
   | Service     | URL                    |
   |-------------|------------------------|
   | Website     | http://localhost:9000  |
   | phpMyAdmin  | http://localhost:9001  |
## Default Credentials
 
These are for local development only.
 
| Purpose              | Username      | Password        |
|----------------------|---------------|-----------------|
| Application / phpMyAdmin | `portal_user` | `portal_pass123` |
| MySQL root           | `root`        | `rootpass123`   |
 
Database name: `student_portal`
 
## Database Schema
 
Tables are created automatically by `db.php` on the first request.
 
**enrollments**
 
| Column      | Type          | Notes               |
|-------------|---------------|---------------------|
| id          | INT           | Primary key, auto   |
| full_name   | VARCHAR(100)  |                     |
| email       | VARCHAR(150)  |                     |
| phone       | VARCHAR(20)   |                     |
| track       | VARCHAR(50)   |                     |
| experience  | VARCHAR(20)   |                     |
| created_at  | TIMESTAMP     | Defaults to now     |
 
**feedback**
 
| Column        | Type          | Notes             |
|---------------|---------------|-------------------|
| id            | INT           | Primary key, auto |
| student_name  | VARCHAR(100)  |                   |
| email         | VARCHAR(150)  |                   |
| rating        | TINYINT       | 1 to 5            |
| comments      | TEXT          |                   |
| created_at    | TIMESTAMP     | Defaults to now   |
 
## How Form Handling Works
 
Both forms post to `submit.php` with a hidden `form_type` field:
 
- `register` → validates and inserts into `enrollments`
- `feedback` → validates and inserts into `feedback`
`submit.php` trims and strips tags from input, whitelists tracks and experience levels, validates emails, uses prepared statements for all queries, and escapes all output.
 
## Viewing the Data
 
**phpMyAdmin:** open http://localhost:9001, log in, select `student_portal`, then open `enrollments` or `feedback`.
 
**Command line:**
 
```bash
docker exec -it cloud_portal_db mysql -uportal_user -pportal_pass123 student_portal \
  -e "SELECT * FROM enrollments; SELECT * FROM feedback;"
```
 
## Security Notes
 
This project is configured for local development and coursework. Before any real deployment:
 
- Move credentials into a `.env` file and add it to `.gitignore`
- Use strong, unique passwords
- Do not expose phpMyAdmin or MySQL ports publicly
- Serve the site over HTTPS
## Project Context
 
Built as part of a 4-week Cloud & DevOps training.

 