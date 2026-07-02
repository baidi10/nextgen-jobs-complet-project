<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pageTitle = "Interview Preparation | JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-5 fw-bold mb-4">Interview Preparation</h1>
            <p class="lead text-muted">
                Master your interviews with expert strategies and preparation techniques for tech roles.
            </p>
        </div>
    </div>
    
    <!-- Resource Navigation -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body p-4">
                    <nav class="nav nav-pills nav-justified flex-column flex-md-row">
                        <a class="nav-link active rounded-pill mb-2 mb-md-0" href="#before-interview">Before the Interview</a>
                        <a class="nav-link rounded-pill mb-2 mb-md-0" href="#technical-interviews">Technical Interviews</a>
                        <a class="nav-link rounded-pill mb-2 mb-md-0" href="#behavioral-questions">Behavioral Questions</a>
                        <a class="nav-link rounded-pill mb-2 mb-md-0" href="#remote-interviews">Remote Interviews</a>
                        <a class="nav-link rounded-pill mb-2 mb-md-0" href="#after-interview">After the Interview</a>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Content Column -->
        <div class="col-lg-8">
            <!-- Before the Interview -->
            <section id="before-interview" class="mb-5">
                <h2 class="h3 fw-bold mb-4">Preparing Before Your Interview</h2>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">1. Research the Company</h3>
                        <p>Thorough company research demonstrates your genuine interest and helps you decide if they're right for you.</p>
                        <ul>
                            <li class="mb-2">Read the company website, focusing on their mission, values, and products</li>
                            <li class="mb-2">Review their tech stack on sites like StackShare or Engineering blogs</li>
                            <li class="mb-2">Research recent news, press releases, and funding rounds</li>
                            <li class="mb-2">Check employee reviews on Glassdoor to understand culture and potential challenges</li>
                            <li class="mb-2">Connect with current or former employees on LinkedIn for insights</li>
                        </ul>
                        <div class="alert alert-light border-start border-4 mt-4">
                            <strong>Pro Tip:</strong> Prepare 3-5 insightful questions about the company's products, technical challenges, or growth plans to ask during the interview.
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">2. Analyze the Job Description</h3>
                        <p>Understand exactly what the employer is looking for by breaking down the job description:</p>
                        <ul>
                            <li class="mb-2">Highlight required technical skills, tools, and frameworks</li>
                            <li class="mb-2">Note soft skills they've emphasized (communication, teamwork, etc.)</li>
                            <li class="mb-2">Identify key responsibilities and deliverables</li>
                            <li class="mb-2">Map your experience to each of their requirements</li>
                            <li class="mb-2">Prepare examples that demonstrate your abilities in these areas</li>
                        </ul>
                        <p class="mt-3">For each key requirement, prepare a STAR (Situation, Task, Action, Result) story from your experience.</p>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">3. Prepare Your Elevator Pitch</h3>
                        <p>Create a compelling 1-2 minute introduction that answers "Tell me about yourself":</p>
                        <div class="p-4 bg-light rounded-3 mb-3">
                            <p class="mb-0"><em>"I'm a [your role] with [X years] experience specializing in [key skills]. Most recently at [company], I [significant accomplishment with metrics]. I'm particularly passionate about [relevant interest], which is why I'm excited about this opportunity at [company name] to [how you can add value]."</em></p>
                        </div>
                        <p>Your pitch should be:</p>
                        <ul>
                            <li class="mb-2">Professional but conversational</li>
                            <li class="mb-2">Tailored to the specific role</li>
                            <li class="mb-2">Focused on relevant experience and achievements</li>
                            <li class="mb-2">Clear about why you're interested in this particular position</li>
                        </ul>
                        <div class="alert alert-light border-start border-4 mt-4">
                            <strong>Practice Tip:</strong> Record yourself delivering your pitch and listen for clarity, confidence, and areas to improve.
                        </div>
                    </div>
                </div>
            </section>

            <!-- Technical Interviews -->
            <section id="technical-interviews" class="mb-5">
                <h2 class="h3 fw-bold mb-4">Mastering Technical Interviews</h2>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">1. Coding Interview Strategies</h3>
                        <p>Approach coding problems methodically with these steps:</p>
                        <ol>
                            <li class="mb-2">
                                <strong>Clarify the problem:</strong> Ask questions about constraints, edge cases, and expected inputs/outputs.
                            </li>
                            <li class="mb-2">
                                <strong>Think aloud:</strong> Share your thought process so interviewers understand your reasoning.
                            </li>
                            <li class="mb-2">
                                <strong>Plan before coding:</strong> Outline your approach and discuss trade-offs.
                            </li>
                            <li class="mb-2">
                                <strong>Code carefully:</strong> Write clean, readable code with proper naming and structure.
                            </li>
                            <li class="mb-2">
                                <strong>Test your solution:</strong> Walk through test cases and edge cases.
                            </li>
                            <li class="mb-2">
                                <strong>Optimize:</strong> Discuss time and space complexity, then improve if possible.
                            </li>
                        </ol>
                        <div class="alert alert-light border-start border-4 mt-4">
                            <strong>Practice Resource:</strong> Use platforms like LeetCode, HackerRank, or CodeSignal to practice common interview problems in your preferred language.
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">2. System Design Interviews</h3>
                        <p>For mid to senior-level roles, prepare for system design questions:</p>
                        <ul>
                            <li class="mb-2">
                                <strong>Clarify requirements:</strong> Understand functional and non-functional requirements.
                            </li>
                            <li class="mb-2">
                                <strong>Start high-level:</strong> Outline major components before diving into details.
                            </li>
                            <li class="mb-2">
                                <strong>Consider scale:</strong> Discuss how your solution would handle growth.
                            </li>
                            <li class="mb-2">
                                <strong>Address bottlenecks:</strong> Identify potential issues and mitigation strategies.
                            </li>
                            <li class="mb-2">
                                <strong>Use diagrams:</strong> Visual representations help communicate your ideas.
                            </li>
                        </ul>
                        <p class="mt-3">Practice designing common systems like URL shorteners, chat applications, or e-commerce platforms.</p>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">3. Role-Specific Technical Questions</h3>
                        
                        <div class="accordion" id="techQuestionsAccordion">
                            <!-- Frontend Developer -->
                            <div class="accordion-item border-0 mb-3 shadow-sm">
                                <h2 class="accordion-header" id="headingFrontend">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFrontend" aria-expanded="false" aria-controls="collapseFrontend">
                                        Frontend Developer Questions
                                    </button>
                                </h2>
                                <div id="collapseFrontend" class="accordion-collapse collapse" aria-labelledby="headingFrontend">
                                    <div class="accordion-body">
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">• Explain the difference between local storage, session storage, and cookies.</li>
                                            <li class="mb-2">• How does the virtual DOM work in React?</li>
                                            <li class="mb-2">• Describe the CSS box model and its components.</li>
                                            <li class="mb-2">• What is event bubbling in JavaScript and how can you prevent it?</li>
                                            <li class="mb-2">• How would you optimize a website's performance?</li>
                                            <li>• Explain the concept of responsive design and how you implement it.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Backend Developer -->
                            <div class="accordion-item border-0 mb-3 shadow-sm">
                                <h2 class="accordion-header" id="headingBackend">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBackend" aria-expanded="false" aria-controls="collapseBackend">
                                        Backend Developer Questions
                                    </button>
                                </h2>
                                <div id="collapseBackend" class="accordion-collapse collapse" aria-labelledby="headingBackend">
                                    <div class="accordion-body">
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">• What are the differences between SQL and NoSQL databases?</li>
                                            <li class="mb-2">• Explain RESTful API design principles.</li>
                                            <li class="mb-2">• How do you handle database transactions and ensure data integrity?</li>
                                            <li class="mb-2">• Describe authentication vs. authorization in web applications.</li>
                                            <li class="mb-2">• How would you secure an API from common vulnerabilities?</li>
                                            <li>• What strategies would you use to scale a backend service?</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Full Stack Developer -->
                            <div class="accordion-item border-0 mb-3 shadow-sm">
                                <h2 class="accordion-header" id="headingFullstack">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFullstack" aria-expanded="false" aria-controls="collapseFullstack">
                                        Full Stack Developer Questions
                                    </button>
                                </h2>
                                <div id="collapseFullstack" class="accordion-collapse collapse" aria-labelledby="headingFullstack">
                                    <div class="accordion-body">
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">• How do you handle state management in modern web applications?</li>
                                            <li class="mb-2">• Explain your approach to debugging issues across frontend and backend.</li>
                                            <li class="mb-2">• How would you implement real-time features in a web application?</li>
                                            <li class="mb-2">• Describe your experience with CI/CD pipelines.</li>
                                            <li class="mb-2">• How do you ensure communication between frontend and backend is efficient?</li>
                                            <li>• What considerations would you make when deploying a full stack application?</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Data Science -->
                            <div class="accordion-item border-0 shadow-sm">
                                <h2 class="accordion-header" id="headingDataScience">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDataScience" aria-expanded="false" aria-controls="collapseDataScience">
                                        Data Science Questions
                                    </button>
                                </h2>
                                <div id="collapseDataScience" class="accordion-collapse collapse" aria-labelledby="headingDataScience">
                                    <div class="accordion-body">
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">• Explain the difference between supervised and unsupervised learning.</li>
                                            <li class="mb-2">• How do you handle missing data in a dataset?</li>
                                            <li class="mb-2">• Describe techniques for feature selection and dimensionality reduction.</li>
                                            <li class="mb-2">• How would you evaluate a classification model's performance?</li>
                                            <li class="mb-2">• Explain the concept of overfitting and how to prevent it.</li>
                                            <li>• How would you communicate complex data insights to non-technical stakeholders?</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Behavioral Questions -->
            <section id="behavioral-questions" class="mb-5">
                <h2 class="h3 fw-bold mb-4">Answering Behavioral Questions</h2>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">The STAR Method</h3>
                        <p>Structure your responses to behavioral questions using the STAR framework:</p>
                        
                        <div class="row g-4 mt-2">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <h4 class="h6 fw-bold mb-2">Situation</h4>
                                    <p class="small mb-0">Describe the context and background. Where were you working? What was your role? What was happening?</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <h4 class="h6 fw-bold mb-2">Task</h4>
                                    <p class="small mb-0">Explain the specific challenge or responsibility you faced. What needed to be done? What was your goal?</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <h4 class="h6 fw-bold mb-2">Action</h4>
                                    <p class="small mb-0">Describe the specific actions you took to address the situation. Focus on YOUR contribution, even in team settings.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <h4 class="h6 fw-bold mb-2">Result</h4>
                                    <p class="small mb-0">Share the outcomes of your actions. Use metrics when possible. What did you learn? How did it help the company?</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-light border-start border-4 mt-4">
                            <strong>Pro Tip:</strong> Prepare 5-7 versatile STAR stories that can be adapted to answer different types of behavioral questions.
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">Common Behavioral Questions</h3>
                        <p>Prepare responses for these frequently asked questions:</p>
                        
                        <div class="table-responsive mt-3">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Category</th>
                                        <th>Example Questions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Problem Solving</strong></td>
                                        <td>
                                            <ul class="mb-0">
                                                <li>Describe a complex problem you solved at work.</li>
                                                <li>Tell me about a time you had to make a decision with incomplete information.</li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Teamwork</strong></td>
                                        <td>
                                            <ul class="mb-0">
                                                <li>Describe a situation where you had to work with a difficult team member.</li>
                                                <li>Tell me about a successful team project you contributed to.</li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Leadership</strong></td>
                                        <td>
                                            <ul class="mb-0">
                                                <li>Describe a time you led a team through a challenging project.</li>
                                                <li>Tell me about a time you had to motivate others to achieve a goal.</li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Adaptability</strong></td>
                                        <td>
                                            <ul class="mb-0">
                                                <li>Describe a time when requirements changed mid-project.</li>
                                                <li>How have you adapted to unexpected challenges in your work?</li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Conflict Resolution</strong></td>
                                        <td>
                                            <ul class="mb-0">
                                                <li>Tell me about a conflict you had with a colleague and how you resolved it.</li>
                                                <li>Describe a time when you had to provide difficult feedback.</li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Failure</strong></td>
                                        <td>
                                            <ul class="mb-0">
                                                <li>Describe a project that didn't go as planned.</li>
                                                <li>Tell me about a mistake you made and what you learned from it.</li>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Remote Interviews -->
            <section id="remote-interviews" class="mb-5">
                <h2 class="h3 fw-bold mb-4">Excelling in Remote Interviews</h2>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">Technical Setup</h3>
                        <p>Prepare your environment for a smooth virtual interview:</p>
                        <ul>
                            <li class="mb-2">Test your microphone, camera, and internet connection beforehand</li>
                            <li class="mb-2">Close unnecessary applications to prevent distractions and notifications</li>
                            <li class="mb-2">Have a backup plan (phone number, alternate device) in case of technical issues</li>
                            <li class="mb-2">Position your camera at eye level and check your lighting</li>
                            <li class="mb-2">Use headphones to reduce echo and improve audio quality</li>
                            <li class="mb-2">Prepare your background to be professional and distraction-free</li>
                        </ul>
                        <div class="alert alert-light border-start border-4 mt-4">
                            <strong>Pro Tip:</strong> Do a test call with a friend on the same platform the interview will use to ensure everything works properly.
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">Virtual Communication Tips</h3>
                        <p>Master the nuances of remote interviewing:</p>
                        <ul>
                            <li class="mb-2">Look at the camera (not the screen) when speaking to create eye contact</li>
                            <li class="mb-2">Speak clearly and slightly slower than normal conversation</li>
                            <li class="mb-2">Use hand gestures deliberately but don't overdo it</li>
                            <li class="mb-2">Avoid interrupting; slight pauses help accommodate video lag</li>
                            <li class="mb-2">Have a notepad for notes but minimize typing noise</li>
                            <li class="mb-2">Express engagement through nodding and facial expressions</li>
                        </ul>
                        <p class="mt-3">For technical interviews, be prepared to share your screen and explain your code or thought process clearly and methodically.</p>
                    </div>
                </div>
            </section>

            <!-- After the Interview -->
            <section id="after-interview" class="mb-5">
                <h2 class="h3 fw-bold mb-4">After the Interview</h2>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">Following Up Professionally</h3>
                        <p>Maintain momentum after your interview with these steps:</p>
                        <ol>
                            <li class="mb-3">
                                <strong>Send thank-you emails:</strong> Within 24 hours, email each interviewer individually to express appreciation for their time.
                                <div class="p-3 bg-light rounded-3 mt-2 mb-2">
                                    <p class="small mb-0"><em>"Dear [Name], Thank you for taking the time to discuss the [Position] role at [Company] with me today. I particularly enjoyed our conversation about [specific topic from interview]. The position aligns well with my experience in [relevant skill/experience], and I'm excited about the possibility of contributing to [specific company goal or project]. Please don't hesitate to contact me if you need any additional information. I look forward to hearing from you about the next steps in the process."</em></p>
                                </div>
                            </li>
                            <li class="mb-3">
                                <strong>Self-evaluation:</strong> Reflect on what went well and what you could improve. Note any unexpected questions for future interviews.
                            </li>
                            <li class="mb-3">
                                <strong>Follow up appropriately:</strong> If you haven't heard back within the timeframe they mentioned, send a polite follow-up email expressing your continued interest.
                            </li>
                            <li>
                                <strong>Continue your job search:</strong> Don't pause your search while waiting for a response. Keep applying and interviewing until you have a formal offer.
                            </li>
                        </ol>
                        <div class="alert alert-light border-start border-4 mt-4">
                            <strong>Pro Tip:</strong> If you receive a rejection, thank them for the opportunity and ask for feedback that could help you improve for future interviews.
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Next Steps -->
            <div class="card bg-dark text-white border-0">
                <div class="card-body p-4 p-lg-5">
                    <h2 class="h4 fw-bold mb-3">Ready to practice your interview skills?</h2>
                    <p class="mb-4">
                        The best way to prepare for interviews is through deliberate practice. Browse our job listings and start applying your interview skills.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php" class="btn btn-light rounded-pill px-4">Browse Jobs</a>
                        <a href="<?= Config::BASE_URL ?>/pages/public/resources.php" class="btn btn-outline-light rounded-pill px-4">More Resources</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4 mt-5 mt-lg-0">
            <div class="sticky-lg-top" style="top: 100px;">
                <!-- Interview Checklist -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light border-0">
                        <h3 class="h5 fw-bold mb-0">Interview Checklist</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="check1">
                            <label class="form-check-label" for="check1">Research the company</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="check2">
                            <label class="form-check-label" for="check2">Review job description & requirements</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="check3">
                            <label class="form-check-label" for="check3">Prepare STAR stories for behavioral questions</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="check4">
                            <label class="form-check-label" for="check4">Practice technical questions</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="check5">
                            <label class="form-check-label" for="check5">Prepare questions to ask interviewer</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="check6">
                            <label class="form-check-label" for="check6">Test technical setup (for remote interviews)</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="check7">
                            <label class="form-check-label" for="check7">Prepare professional attire</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="check8">
                            <label class="form-check-label" for="check8">Plan your route or setup interview space</label>
                        </div>
                    </div>
                </div>
                
                <!-- Resource Downloads -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">Interview Resources</h3>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <a href="#" class="d-flex text-decoration-none">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-4 me-2"></i>
                                    <div>
                                        <span class="d-block text-dark">Technical Interview Cheat Sheet</span>
                                        <span class="small text-muted">PDF, 5 pages</span>
                                    </div>
                                </a>
                            </li>
                            <li class="mb-3">
                                <a href="#" class="d-flex text-decoration-none">
                                    <i class="bi bi-file-earmark-word text-primary fs-4 me-2"></i>
                                    <div>
                                        <span class="d-block text-dark">STAR Method Template</span>
                                        <span class="small text-muted">Word, 2 pages</span>
                                    </div>
                                </a>
                            </li>
                            <li class="mb-3">
                                <a href="#" class="d-flex text-decoration-none">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-4 me-2"></i>
                                    <div>
                                        <span class="d-block text-dark">Questions to Ask Interviewers</span>
                                        <span class="small text-muted">PDF, 3 pages</span>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="d-flex text-decoration-none">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-4 me-2"></i>
                                    <div>
                                        <span class="d-block text-dark">Remote Interview Guide</span>
                                        <span class="small text-muted">PDF, 4 pages</span>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Related Resources -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">Related Resources</h3>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <a href="<?= Config::BASE_URL ?>/pages/public/resources-job-search.php" class="d-flex align-items-center text-decoration-none">
                                    <i class="bi bi-search text-primary fs-4 me-2"></i>
                                    <span class="text-dark">Job Search Tips</span>
                                </a>
                            </li>
                            <li class="mb-3">
                                <a href="<?= Config::BASE_URL ?>/pages/public/resources-resume.php" class="d-flex align-items-center text-decoration-none">
                                    <i class="bi bi-file-earmark-text text-primary fs-4 me-2"></i>
                                    <span class="text-dark">Resume & Cover Letter Guide</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= Config::BASE_URL ?>/pages/public/salary-guide.php" class="d-flex align-items-center text-decoration-none">
                                    <i class="bi bi-cash-stack text-primary fs-4 me-2"></i>
                                    <span class="text-dark">Salary Negotiation Guide</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Smooth scrolling for anchor links
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('.nav-link');
        
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all links
                links.forEach(item => item.classList.remove('active'));
                
                // Add active class to clicked link
                this.classList.add('active');
                
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 