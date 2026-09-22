<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {margin:0; padding:0; box-sizing:border-box;}
    body {
      height:100vh;
      display:flex;
      justify-content:center;
      align-items:center;
      background:#0d0d0d;
      color:#fff;
      font-family: 'Segoe UI', Tahoma, sans-serif;
      overflow:hidden;
    }
    #bg {
      position:fixed;
      top:0; left:0;
      width:100%; height:100%;
      z-index:-1;
    }
    .login-box {
      background:rgba(0,0,0,0.7);
      padding:40px;
      border-radius:15px;
      box-shadow:0 0 25px rgba(0,255,200,0.4);
      width:350px;
      text-align:center;
    }
    .login-box h2 {
      margin-bottom:20px;
      color:#00ffc3;
      letter-spacing:1px;
    }
    .login-box input {
      width:100%;
      padding:12px;
      margin:10px 0;
      border:none;
      outline:none;
      border-radius:8px;
      background:#111;
      color:#fff;
      font-size:15px;
      box-shadow:inset 0 0 10px rgba(0,255,200,0.2);
    }
    .login-box button {
      width:100%;
      padding:12px;
      border:none;
      border-radius:8px;
      background:#00ffc3;
      color:#000;
      font-weight:bold;
      font-size:16px;
      cursor:pointer;
      transition:0.3s;
    }
    .login-box button:hover {
      background:#00b894;
    }
    .error, .success {
      margin-top:15px;
      font-size:14px;
      display:none;
    }
    .error {color:#ff4d4d;}
    .success {color:#4dff88;}
  </style>
</head>
<body>

<canvas id="bg"></canvas>

<div class="login-box">
  <h2>FRUtopia Login</h2>
  <form id="loginForm">
    <input type="email" id="email" name="email" placeholder="Email" required>
    <input type="password" id="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
    <p class="error" id="errorMsg"></p>
    <p class="success" id="successMsg"></p>
  </form>
</div>

<script>
  // Particle Background
  const canvas = document.getElementById("bg");
  const ctx = canvas.getContext("2d");
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;

  const particles = [];
  const total = 100;
  for(let i=0;i<total;i++){
    particles.push({
      x:Math.random()*canvas.width,
      y:Math.random()*canvas.height,
      r:Math.random()*2+1,
      dx:(Math.random()-0.5)*1,
      dy:(Math.random()-0.5)*1
    });
  }
  function animate(){
    ctx.clearRect(0,0,canvas.width,canvas.height);
    for(let p of particles){
      ctx.beginPath();
      ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
      ctx.fillStyle="#00ffc3";
      ctx.fill();
      p.x+=p.dx;
      p.y+=p.dy;
      if(p.x<0||p.x>canvas.width) p.dx*=-1;
      if(p.y<0||p.y>canvas.height) p.dy*=-1;
    }
    requestAnimationFrame(animate);
  }
  animate();

  // Handle Login
  document.getElementById("loginForm").addEventListener("submit", async function(e){
    e.preventDefault();
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();
    let errorMsg = document.getElementById("errorMsg");
    let successMsg = document.getElementById("successMsg");

    errorMsg.style.display="none";
    successMsg.style.display="none";

    try {
      let response = await fetch("login_check.php", {
        method:"POST",
        body: new URLSearchParams({
          email: email,
          password: password
        })
      });
      let result = await response.json();

      if(result.Code == 200){
        successMsg.style.display="block";
        successMsg.textContent = result.msg + " (Welcome "+result.username+")";
        setTimeout(()=>{ window.location.href="./index.php"; },1500);
      } else {
        errorMsg.style.display="block";
        errorMsg.textContent = result.msg || "Invalid login";
      }
    } catch(err){
      errorMsg.style.display="block";
      errorMsg.textContent = "Error connecting to server";
    }
  });
</script>

</body>
</html>
