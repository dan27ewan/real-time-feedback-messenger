<?php 
include 'db.php'; 
$uID = $_GET['user_id'] ?? null; 

if (!$uID): ?>
<!DOCTYPE html>
<html>
<head>
    <title>Messenger Login</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f2f5; margin: 0; }
        .card { background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; width: 320px; }
        input { width: 100%; padding: 12px; margin: 15px 0; border: 1px solid #ddd; border-radius: 10px; outline: none; }
        .btn { display: block; padding: 12px; margin: 10px 0; color: white; background: #075e54; border-radius: 25px; font-weight: bold; cursor: pointer; border: none; width: 100%; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="color:#075e54">Messenger</h2>
        <input type="text" id="nameInp" placeholder="Enter Your Name..." maxlength="15">
        <button onclick="join(1)" class="btn">Join as User 1</button>
        <button onclick="join(2)" class="btn" style="background:#128c7e">Join as User 2</button>
    </div>
    <script>
        function join(id) {
            const name = document.getElementById('nameInp').value.trim() || "User " + id;
            const fd = new FormData(); fd.append('display_name', name);
            fetch(`api.php?action=update_name&user_id=${id}`, {method:'POST', body:fd})
            .then(() => window.location.href = `index.php?user_id=${id}`);
        }
    </script>
</body>
</html>
<?php exit; endif; 
$tID = ($uID == 1) ? 2 : 1;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
    <style>
        :root { --bg: #dadbd3; --chat: #e5ddd5; --head: #075e54; --sent: #dcf8c6; --txt: #000; --in: #fff; }
        body.dark { --bg: #111b21; --chat: #0b141a; --head: #202c33; --sent: #005c4b; --txt: #fff; --in: #2a3942; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); height: 100vh; display: flex; justify-content: center; align-items: center; color: var(--txt); transition: 0.3s; }
        #window { width: 450px; height: 90vh; background: var(--chat); display: flex; flex-direction: column; border-radius: 10px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        #header { background: var(--head); color: white; padding: 12px 15px; display: flex; justify-content: space-between; align-items: center; }
        #msg-box { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 10px; background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-blend-mode: soft-light; }
        .bubble { padding: 8px 12px; border-radius: 8px; max-width: 80%; font-size: 15px; color: var(--txt); box-shadow: 0 1px 2px rgba(0,0,0,0.2); }
        .sent { background: var(--sent); align-self: flex-end; border-top-right-radius: 0; }
        .received { background: var(--in); align-self: flex-start; border-top-left-radius: 0; }
        .time { font-size: 10px; opacity: 0.5; text-align: right; margin-top: 4px; }
        .input-area { display: flex; padding: 10px; background: var(--head); gap: 10px; }
        input#msg-inp { flex: 1; padding: 12px; border-radius: 25px; border: none; outline: none; background: var(--in); color: var(--txt); }
        .btn-icon { background: none; border: none; color: white; cursor: pointer; font-size: 20px; opacity: 0.8; }
    </style>
</head>
<body>
<div id="window">
    <div id="header">
        <div style="display:flex; align-items:center; gap:10px;">
            <button class="btn-icon" onclick="location.href='index.php'">←</button>
            <strong id="target-name">Connecting...</strong>
        </div>
        <div>
            <button class="btn-icon" onclick="document.body.classList.toggle('dark')" style="margin-right:10px;">🌓</button>
            <button class="btn-icon" onclick="clearChat()">🗑</button>
        </div>
    </div>
    <div id="msg-box"></div>
    <div id="status" style="padding: 5px 20px; font-size: 12px; font-style: italic; height: 25px;"></div>
    <div class="input-area">
        <input type="text" id="msg-inp" placeholder="Type a message..." autocomplete="off">
        <button id="send-btn" class="btn-icon" style="background:#128c7e; width:45px; height:45px; border-radius:50%;">➤</button>
    </div>
</div>

<script>
const uID = <?php echo $uID; ?>, tID = <?php echo $tID; ?>;
const msgBox = document.getElementById('msg-box'), input = document.getElementById('msg-inp');
let lastCount = 0;

setInterval(() => {
    fetch(`api.php?action=fetch&user_id=${uID}&target_id=${tID}`)
    .then(r => r.json()).then(data => {
        document.getElementById('target-name').innerText = data.target_name;
        document.getElementById('status').innerText = data.is_typing ? data.target_name + ' is typing...' : '';
        msgBox.innerHTML = data.messages.map(m => `
            <div class="bubble ${m.sender_id == uID ? 'sent' : 'received'}">
                ${m.message}<div class="time">${m.time_sent}</div>
            </div>`).join('');
        if(data.messages.length !== lastCount) { msgBox.scrollTop = msgBox.scrollHeight; lastCount = data.messages.length; }
    }).catch(() => document.getElementById('target-name').innerText = "Connection Error");
}, 800);

function send() {
    if(!input.value.trim()) return;
    let fd = new FormData(); fd.append('message', input.value);
    fetch(`api.php?action=send&user_id=${uID}&target_id=${tID}`, {method:'POST', body:fd});
    input.value = '';
}
document.getElementById('send-btn').onclick = send;
input.onkeypress = (e) => { if(e.key === 'Enter') send(); };

let tTimer;
input.oninput = () => {
    fetch(`api.php?action=set_typing&user_id=${uID}`, {method:'POST', body: (()=>{let f=new FormData();f.append('status',1);return f})()});
    clearTimeout(tTimer);
    tTimer = setTimeout(() => {
        fetch(`api.php?action=set_typing&user_id=${uID}`, {method:'POST', body: (()=>{let f=new FormData();f.append('status',0);return f})()});
    }, 1500);
};

function clearChat() { if(confirm("Clear chat?")) fetch(`api.php?action=clear_chat&user_id=${uID}&target_id=${tID}`).then(()=>location.reload()); }
</script>
</body>
</html>