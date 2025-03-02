<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votery - Home</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        /* Header Style */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background: #00509e;
            border-bottom: 2px solid #ddd;
        }

        .logo img {
            max-height: 50px;
            display: block;
        }

        nav ul {
            display: flex;
            list-style: none;
        }

        nav ul li {
            margin-left: 20px;
        }

        nav ul li a {
            text-decoration: none;
            color: #fff;
            font-weight: bold;
        }

        /* Hero Section */
        .hero {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 50px 30px;
            animation: slideIn 1s ease-in-out;
        }

        .hero-text h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .buttons a {
            display: inline-block;
            padding: 10px 20px;
            margin-right: 10px;
            background: #00509e;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .buttons a:hover {
            background: #0056b3;
        }

        .hero-image img {
            max-width: 100%;
            height: auto;
        }

        /* Why Choose Votery Section */
        .work-container {
            padding: 50px 30px;
            text-align: center;
            animation: fadeIn 1.5s ease-in-out;
        }

        .work-container img {
            width: 90%;
            height: auto;
        }

        .work-container h1 {
            margin-bottom: 30px;
            color: #00509e;
        }

        .work-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .work-item {
            background-color: rgb(127, 186, 249);
            padding: 30px;
            border-radius: 20px;
            color: white;
            animation: fadeIn 1s ease-in-out;
            animation-delay: calc(var(--i) * 0.3s);
            animation-fill-mode: backwards;
        }

        /* Features Section */
        .features {
            padding: 50px 20px;
            text-align: center;
            animation: fadeIn 1.5s ease-in-out;
        }

        .features h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #00509e;
        }

        .icon-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
        }

        .icon-item {
            text-align: center;
            animation: fadeIn 1s ease-in-out;
            animation-delay: calc(var(--i) * 0.2s);
            animation-fill-mode: backwards;
        }

        .icon-item img {
            width: 150px;
            height: 150px;
        }

        .icon-label {
            font-size: 1rem;
            margin-top: 5px;
        }

        /* Footer Style */
        footer {
            text-align: center;
            padding: 20px;
            background: #333;
            color: #fff;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="votery logo.png" alt="Votery Logo">
        </div>
        <nav>
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="sign in.php">Sign Up</a></li>
            </ul>
        </nav>
    </header>

    <section id="home" class="hero">
        <div class="hero-text">
            <h1>Revolutionize <br>Your Voting Experience</h1>
            <p>Make your voting experience simple, secure, and efficient with Votery. Start now!</p>
            <div class="buttons">
                <a href="login.php">Get Started</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="votery lp.png" alt="Hero Image">
        </div>
    </section>

    <section id="features" class="work-container">
        <img src="votery db.png" alt="Dashboard">
        <h1>Why Choose Votery?</h1>
        <div class="work-grid">
            <div class="work-item" style="--i:1">
                <h3>Easy to Use</h3>
                <p>With a simple interface, you can create and participate in votes with just a few clicks.</p>
            </div>
            <div class="work-item" style="--i:2">
                <h3>Voting Flexibility</h3>
                <p>Create polls with various options and allow users to add or remove choices.</p>
            </div>
            <div class="work-item" style="--i:3">
                <h3>Real-Time Results</h3>
                <p>See your vote results instantly after participating, with unmatched transparency.</p>
            </div>
            <div class="work-item" style="--i:4">
                <h3>Guaranteed Security</h3>
                <p>With secure login systems and advanced data encryption, your privacy is our priority.</p>
            </div>
        </div>
    </section>

    <section class="features">
        <h1>How Does It Work?</h1>
        <div class="icon-container">
            <div class="icon-item" style="--i:1">
                <img src="votery_create.jpeg" alt="Create">
                <div class="icon-label">Easy to Create</div>
            </div>
            <div class="icon-item" style="--i:2">
                <img src="votery_share.jpeg" alt="Share">
                <div class="icon-label">Share with Others</div>
            </div>
            <div class="icon-item" style="--i:3">
                <img src="votery_join.jpeg" alt="Join">
                <div class="icon-label">Join the Vote</div>
            </div>
            <div class="icon-item" style="--i:4">
                <img src="votery_show result.jpeg" alt="Results">
                <div class="icon-label">View Results</div>
            </div>
            <div class="icon-item" style="--i:5">
                <img src="votery_stars.jpeg" alt="Stars">
                <div class="icon-label">Collect Your Stars</div>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 Votery. All rights reserved.</p>
    </footer>
</body>
</html>
