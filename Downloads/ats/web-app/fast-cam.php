<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fast Attendance</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; text-align: center; }
        .box { background: white; padding: 30px; border-radius: 10px; max-width: 700px; margin: 0 auto; }
        video { width: 100%; max-width: 640px; border: 2px solid #3498db; border-radius: 8px; }
        button { padding: 15px 30px; font-size: 16px; margin: 10px; border: none; border-radius: 5px; cursor: pointer; color: white; }
        .green { background: #27ae60; }
        .blue { background: #3498db; }
        .red { background: #e74c3c; }
        input { padding: 10px; font-size: 16px; width: 200px; margin: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .msg { padding: 15px; margin: 15px 0; border-radius: 5px; font-weight: bold; }
        .ok { background: #d4edda; color: #155724; }
        .info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="box">
        <h1>📸 Fast Attendance</h1>
        <video id="v" autoplay></video>
        <div id="msg" class="msg info" style="display:none">Ready</div>
        <input type="text" id="empId" placeholder="Employee ID (0001)">
        <br>
        <button class="green" onclick="start()">Start Camera</button>
        <button class="blue" onclick="mark()" id="markBtn" style="display:none">Mark Attendance</button>
        <button class="red" onclick="stop()" id="stopBtn" style="display:none">Stop</button>
    </div>
    
    <script>
        let s = null;
        async function start() {
            try {
                show('Starting...', 'info');
                s = await navigator.mediaDevices.getUserMedia({video: true});
                document.getElementById('v').srcObject = s;
                document.getElementById('markBtn').style.display = 'inline-block';
                document.getElementById('stopBtn').style.display = 'inline-block';
                show('Camera ready!', 'ok');
            } catch(e) {
                show('Error: ' + e.message, 'info');
            }
        }
        
        async function mark() {
            const id = document.getElementById('empId').value.trim();
            if (!id) { alert('Enter Employee ID!'); return; }
            
            show('Marking...', 'info');
            const fd = new FormData();
            fd.append('employee_id', id);
            fd.append('action', 'mark_attendance');
            
            try {
                const r = await fetch('process-attendance.php', {method: 'POST', body: fd});
                const j = await r.json();
                show(j.success ? '✅ ' + j.message : '❌ ' + j.message, j.success ? 'ok' : 'info');
                if (j.success) document.getElementById('empId').value = '';
            } catch(e) {
                show('Error: ' + e.message, 'info');
            }
        }
        
        function stop() {
            if (s) {
                s.getTracks().forEach(t => t.stop());
                document.getElementById('v').srcObject = null;
            }
            document.getElementById('markBtn').style.display = 'none';
            document.getElementById('stopBtn').style.display = 'none';
            show('Stopped', 'info');
        }
        
        function show(m, t) {
            const msg = document.getElementById('msg');
            msg.textContent = m;
            msg.className = 'msg ' + t;
            msg.style.display = 'block';
        }
    </script>
</body>
</html>
