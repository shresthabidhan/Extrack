<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudySync - Semester III Pro</title>
    <style>
        :root {
            --primary: #2563eb; --primary-hover: #1e40af;
            --bg: #f3f4f6; --card: #ffffff;
            --text: #1f2937; --success: #10b981;
            --danger: #ef4444; --accent: #f59e0b;
        }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); margin: 0; line-height: 1.5; }
        .container { max-width: 1100px; margin: 0 auto; padding: 20px; }
        
        /* Auth Styles */
        .auth-card { max-width: 400px; margin: 100px auto; background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); text-align: center; }
        .auth-card h2 { margin-bottom: 25px; color: var(--primary); }
        .auth-card input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-primary { width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .btn-primary:hover { background: var(--primary-hover); }
        .toggle-link { margin-top: 15px; color: var(--primary); cursor: pointer; font-size: 0.9em; }

        /* App UI */
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .nav-tabs { display: flex; gap: 8px; margin-bottom: 25px; flex-wrap: wrap; overflow-x: auto; padding-bottom: 5px; }
        .nav-btn { padding: 10px 18px; border: none; background: white; border-radius: 10px; cursor: pointer; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.05); white-space: nowrap; }
        .nav-btn.active { background: var(--primary); color: white; }
        
        .section { display: none; background: var(--card); padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); animation: slideUp 0.3s ease-out; }
        .section.active { display: block; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

        /* Tables & Inputs */
        .schedule-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .schedule-table th, .schedule-table td { padding: 15px; border-bottom: 1px solid #edf2f7; text-align: left; }
        .schedule-table th { background: #f8fafc; color: #64748b; font-size: 0.85em; text-transform: uppercase; }
        .checkbox-group { display: flex; flex-direction: column; gap: 8px; }
        .check-item { display: flex; align-items: center; gap: 10px; font-size: 0.95em; cursor: pointer; }
        input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--success); cursor: pointer; }

        /* Dashboard Items */
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; }
        .card { background: #f8fafc; padding: 20px; border-radius: 12px; border-left: 5px solid var(--primary); }
        .progress-container { background: #e2e8f0; height: 24px; border-radius: 12px; overflow: hidden; margin: 15px 0; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #10b981, #34d399); width: 0%; transition: width 0.6s cubic-bezier(0.1, 0, 0, 1); color: white; font-size: 12px; line-height: 24px; text-align: center; font-weight: bold; }
        
        /* Leaderboard */
        .lb-row { display: flex; justify-content: space-between; padding: 12px; border-radius: 8px; margin-bottom: 8px; background: #fff; border: 1px solid #eee; }
        .lb-rank { font-weight: bold; color: var(--accent); margin-right: 10px; }
        
        #status-toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #1f2937; color: white; padding: 10px 25px; border-radius: 30px; font-size: 14px; opacity: 0; transition: 0.3s; z-index: 1000; }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['user_id'])): ?>
    <div class="auth-card">
        <h2 id="auth-title">Welcome Back</h2>
        <form id="auth-form">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn-primary">Login</button>
        </form>
        <p class="toggle-link" onclick="toggleAuth()">Don't have an account? Register</p>
    </div>
<?php else: ?>
    <div class="container">
        <header>
            <div>
                <h1 style="margin:0; color:var(--primary);">StudySync</h1>
                <p style="margin:0; font-size:0.9em; color:#666;">Logged in as <b><?php echo $_SESSION['username']; ?></b></p>
            </div>
            <a href="auth.php?action=logout" style="color:var(--danger); text-decoration:none; font-weight:600;">Logout</a>
        </header>

        <nav class="nav-tabs">
            <button class="nav-btn active" onclick="showTab('dashboard')">📊 Dashboard</button>
            <button class="nav-btn" onclick="showTab('week1')">Week 1</button>
            <button class="nav-btn" onclick="showTab('week2')">Week 2</button>
            <button class="nav-btn" onclick="showTab('week3')">Week 3</button>
            <button class="nav-btn" onclick="showTab('week4')">Week 4</button>
            <button class="nav-btn" onclick="showTab('subjects')">📚 Subject Tracker</button>
            <button class="nav-btn" onclick="showTab('leaderboard'); fetchLeaderboard();">🏆 Leaderboard</button>
        </nav>

        <div id="dashboard" class="section active">
            <h2>Your Momentum</h2>
            <div class="card">
                <h3>Overall Completion</h3>
                <div class="progress-container">
                    <div id="total-progress" class="progress-bar">0%</div>
                </div>
            </div>
            <div class="grid" style="margin-top:20px;">
                <div class="card">
                    <h3>Mock Test Scores (/60)</h3>
                    <div id="scores-container">
                        </div>
                </div>
                <div class="card">
                    <h3>My Goal & Motivation</h3>
                    <textarea id="motivation-text" style="width:100%; height:120px; border:1px solid #ddd; border-radius:8px; padding:10px;" placeholder="Why do you want to succeed?" onblur="syncMotivation(this)"></textarea>
                </div>
            </div>
        </div>

        <div id="week1" class="section"><h2>Week 1: Foundation Building</h2><table class="schedule-table"><thead><tr><th>Day</th><th>Focus</th><th>Goals</th></tr></thead><tbody id="week1-body"></tbody></table></div>
        <div id="week2" class="section"><h2>Week 2: Deep Dive</h2><table class="schedule-table"><thead><tr><th>Day</th><th>Focus</th><th>Goals</th></tr></thead><tbody id="week2-body"></tbody></table></div>
        <div id="week3" class="section"><h2>Week 3: Completion</h2><table class="schedule-table"><thead><tr><th>Day</th><th>Focus</th><th>Goals</th></tr></thead><tbody id="week3-body"></tbody></table></div>
        <div id="week4" class="section"><h2>Week 4: Revision</h2><table class="schedule-table"><thead><tr><th>Day</th><th>Focus</th><th>Goals</th></tr></thead><tbody id="week4-body"></tbody></table></div>

        <div id="subjects" class="section"><h2>Chapter Tracker</h2><div id="subject-container" class="grid"></div></div>

        <div id="leaderboard" class="section">
            <h2>Global Standings</h2>
            <p>See how you compare with other students (Percentage completion).</p>
            <div id="lb-list">Loading...</div>
        </div>

    </div>
<?php endif; ?>

<div id="status-toast">Synced</div>

<script>
    const scheduleData = {
        week1: [
            { d: "Mon", t: "Math (Fourier) + Micro (8085)", g: ["Fourier basics done", "8085 architecture"] },
            { d: "Tue", t: "Math (Fourier) + Electronics", g: ["5 Fourier problems solved", "8085 instructions"] },
            { d: "Wed", t: "Math (FT) + Control (Block)", g: ["Fourier transform done", "5 assembly programs"] },
            { d: "Thu", t: "Math (Complex) + Electronics", g: ["C-R equations mastered", "Time response math"] },
            { d: "Fri", t: "Math (Integration) + Control", g: ["Cauchy theorem applied", "PID concepts clear"] },
            { d: "Sat", t: "Micro (8086) + Math (Residue)", g: ["8086 programming started", "R-H criterion mastered"] },
            { d: "Sun", t: "Review Day", g: ["Week 1 test taken", "Weak areas listed"] }
        ],
        week2: [
            { d: "Mon", t: "Math (Series) + Micro (8086)", g: ["Taylor/Laurent series", "String operations"] },
            { d: "Tue", t: "Control (Root locus) + Math", g: ["Root locus rules", "Lagrange method"] },
            { d: "Wed", t: "Math (Z-trans) + Control", g: ["Z-transform props", "3D transformations"] },
            { d: "Thu", t: "Control (Bode) + Math", g: ["Bode plot construction", "Interrupt mechanism"] },
            { d: "Fri", t: "Control (Nyquist) + Math", g: ["Nyquist criterion", "Difference equations"] },
            { d: "Sat", t: "Micro (Adv) + Math", g: ["RISC/CISC understood", "Lead/lag compensators"] },
            { d: "Sun", t: "Practice Day", g: ["Mock test 2", "Review errors"] }
        ],
        week3: [
            { d: "Mon", t: "Graphics + Control (State)", g: ["Animation concepts", "Math Ch 1-2 revised"] },
            { d: "Tue", t: "Graphics + Electronics", g: ["AR/VR basics", "8085 fully revised"] },
            { d: "Wed", t: "Math + Control Rev", g: ["Complex vars master", "All algorithms rev"] },
            { d: "Thu", t: "Micro + Math Rev", g: ["8086 programming", "Control topics rev"] },
            { d: "Fri", t: "Electronics + Graphics", g: ["Power electronics", "20+ problems solved"] },
            { d: "Sat", t: "Mock Day 1", g: ["Math mock", "Micro mock", "Control mock"] },
            { d: "Sun", t: "Mock Day 2", g: ["Electro mock", "Graphics mock", "English mock"] }
        ],
        week4: [
            { d: "Mon-Tue", t: "Math Intensive", g: ["All formulas memorized", "30+ problems"] },
            { d: "Wed-Thu", t: "Micro + Control", g: ["Programs from memory", "Bode/Nyquist fluent"] },
            { d: "Fri", t: "Electronics + Graphics", g: ["All circuits understood", "All algorithms"] },
            { d: "Sat-Sun", t: "Final Polish", g: ["Final Mock tests", "Formula sheet ready"] }
        ]
    };

    const subjects = [
        { n: "Math", c: ["Fourier", "Complex", "PDEs", "Z-Trans"] },
        { n: "Micro", c: ["8085", "8086", "Interrupts", "Adv"] },
        { n: "Control", c: ["Modeling", "Stability", "Root Locus", "Freq Resp"] },
        { n: "Graphics", c: ["Raster", "Transforms", "Visible Surface", "Animation"] },
        { n: "Electronics", c: ["Op-Amp", "ADC/DAC", "Power Elec", "SMPS"] },
        { n: "English", c: ["Tech Writing", "Business Corresp", "Visual Aids"] }
    ];

    let isLogin = true;
    function toggleAuth() {
        isLogin = !isLogin;
        document.getElementById('auth-title').innerText = isLogin ? 'Welcome Back' : 'Join the Class';
        document.querySelector('.btn-primary').innerText = isLogin ? 'Login' : 'Create Account';
        document.querySelector('.toggle-link').innerText = isLogin ? "Don't have an account? Register" : "Already have an account? Login";
    }

    // --- Core Logic ---
    async function init() {
        if (!document.getElementById('week1-body')) return;
        renderAll();
        await loadAllData();
        updateProgressUI();
    }

    function renderAll() {
        // Render Weeks
        ['week1', 'week2', 'week3', 'week4'].forEach(w => {
            const body = document.getElementById(`${w}-body`);
            scheduleData[w].forEach((item, idx) => {
                const goals = item.g.map((goal, gIdx) => `
                    <label class="check-item">
                        <input type="checkbox" id="${w}-${idx}-${gIdx}" onchange="syncCheck(this)"> ${goal}
                    </label>
                `).join('');
                body.innerHTML += `<tr><td><b>${item.d}</b></td><td>${item.t}</td><td><div class="checkbox-group">${goals}</div></td></tr>`;
            });
        });

        // Render Subjects
        const subContainer = document.getElementById('subject-container');
        subjects.forEach((s, sIdx) => {
            const caps = s.c.map((cap, cIdx) => `
                <label class="check-item">
                    <input type="checkbox" id="cap-${sIdx}-${cIdx}" onchange="syncCheck(this)"> ${cap}
                </label>
            `).join('');
            subContainer.innerHTML += `<div class="card"><h3>${s.n}</h3><div class="checkbox-group">${caps}</div></div>`;
        });

        // Render Score Inputs
        const scoreBox = document.getElementById('scores-container');
        subjects.forEach(s => {
            scoreBox.innerHTML += `<div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span>${s.n}</span>
                <input type="number" id="score-${s.n.toLowerCase()}" class="score-input" style="width:60px;" onchange="syncScore(this)">
            </div>`;
        });
    }

    async function loadAllData() {
        const res = await fetch('api.php?action=load_all');
        const data = await res.json();
        if(data.progress) data.progress.forEach(id => { if(document.getElementById(id)) document.getElementById(id).checked = true; });
        if(data.scores) Object.entries(data.scores).forEach(([id, val]) => { if(document.getElementById(id)) document.getElementById(id).value = val; });
        if(data.motivation) document.getElementById('motivation-text').value = data.motivation;
    }

    async function fetchLeaderboard() {
        const res = await fetch('api.php?action=get_leaderboard');
        const data = await res.json();
        const lb = document.getElementById('lb-list');
        lb.innerHTML = data.map((u, i) => `
            <div class="lb-row">
                <span><span class="lb-rank">#${i+1}</span> ${u.username}</span>
                <span style="font-weight:bold; color:var(--success);">${u.progress}%</span>
            </div>
        `).join('');
    }

    // --- Sync Helpers ---
    async function syncCheck(el) {
        updateProgressUI();
        post('api.php?action=update_progress', { id: el.id, status: el.checked });
    }
    function syncScore(el) { post('api.php?action=update_score', { id: el.id, score: el.value }); }
    function syncMotivation(el) { post('api.php?action=update_motivation', { text: el.value }); }

    async function post(url, data) {
        toast("Saving...");
        await fetch(url, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(data) });
        toast("Saved");
    }

    function updateProgressUI() {
        const total = document.querySelectorAll('input[type="checkbox"]').length;
        const checked = document.querySelectorAll('input[type="checkbox"]:checked').length;
        const perc = Math.round((checked / total) * 100);
        const bar = document.getElementById('total-progress');
        bar.style.width = perc + "%";
        bar.innerText = perc + "% Done";
    }

    function showTab(id) {
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        event.target.classList.add('active');
    }

    function toast(msg) {
        const t = document.getElementById('status-toast');
        t.innerText = msg; t.style.opacity = 1;
        setTimeout(() => t.style.opacity = 0, 2000);
    }

    // Auth Submission
    if(document.getElementById('auth-form')) {
        document.getElementById('auth-form').onsubmit = async (e) => {
            e.preventDefault();
            const action = isLogin ? 'login' : 'register';
            const res = await fetch(`auth.php?action=${action}`, { method: 'POST', body: new FormData(e.target) });
            const data = await res.json();
            if(data.success) { if(isLogin) location.reload(); else { alert(data.message); toggleAuth(); } }
            else alert(data.message);
        };
    }

    init();
</script>
</body>
</html>
