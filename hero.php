<!DOCTYPE html>
<html>
<head>
<title>

</title>
    <style>
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.84), rgba(0, 0, 0, 0.5)), url('YOUR-BACKGROUND.JPG') center/cover no-repeat;
            /* background-image: url('vasily-koloda-8CqDvPuo_kI-unsplash.jpg');  */
        
            height: 100vh;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .hero-content {
            max-width: 800px;
            animation: fadein 2s ease-in-out;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .hero p {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .cta-btn {
            padding: 12px 24px;
            background-color: #ff4b2b;
            color: white;
            border: none;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .cta-btn:hover {
            background-color: #e84320;
        }

        @keyframes fadein {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    
    </STYLE>
</head>

<body>
   <section class="hero">
  <div class="hero-content">
    <h1>Welcome to Batmul College</h1>
    <p>Empowering minds, enriching communities — a non-profit institution dedicated to accessible, quality education for all.</p>
    <a href="about.php" class="cta-btn">Discover Our Mission</a>
  </div>
</section>

</body><!-- hero.php -->