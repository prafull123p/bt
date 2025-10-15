<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Batmul College - Home</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link href="css\style.css" rel="stylesheet">
    <style>
        .founder-section {
            background-color: #f8f9fa;
            padding: 80px 0;
        }

        .founder-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            background: white;
        }

        .founder-card:hover {
            transform: translateY(-10px);
        }

        .founder-img {
            height: 300px;
            object-fit: cover;
            width: 100%;
        }

        .social-icons a {
            color: #6c757d;
            margin: 0 10px;
            font-size: 1.2rem;
            transition: color 0.3s;
        }

        .social-icons a:hover {
            color: #0d6efd;
        }

        .section-title {
            position: relative;
            margin-bottom: 60px;
        }

        .section-title:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 3px;
            background: #0d6efd;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
        }

        .logoset {
            padding-top: 8px;
            background-color: white;
            border-radius: 50%;
            height: 20%;
            width: 15%;
        }
    </style>
</head>

<body>
    <!-- Navigation will be added here -->
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-primary bg-white sticky-top" style="shadow: 5px solid black;">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php" style="position: relative; ">
                <img src="img\IMG-20250330-WA0003-removebg-preview.png" alt="College Logo" class="logoset"
                    style="z-index: -1; shadow:  5px solid red"> <b> Batmul College</b>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse " id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="academicsDropdown" role="button"
                            data-bs-toggle="dropdown">
                            Academics
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="academics.php">Programs</a></li>
                            <li><a class="dropdown-item" href="#">Departments</a></li>
                             <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                            <li><a class="dropdown-item" href="#">Research</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admission.php">Admissions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="campus_life.php">Campus Life</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user_login.php">Login</a>
                    </li>
                </ul>
                <a href="admission.php" class="btn btn-outline-primary ms-lg-3">Apply Now</a>
            </div>
        </div>
    </nav>