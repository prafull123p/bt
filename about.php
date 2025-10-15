<?php include('header.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">

</head>

<body>

    <!-- Navigation (same as index.html) -->

    <main>
        <!-- Page Header -->
        <section class="page-header bg-primary text-white py-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 mx-auto text-center">
                        <h1 class="display-4">About Our College</h1>
                        <p class="lead">Discover our rich history, mission, and leadership</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <!-- About Section -->

</body>

</html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Batmul College</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .about-section {
            padding: 60px 20px;
            background: #f0f4f8;
            font-family: 'Segoe UI', sans-serif;
        }

        .about-container {
            max-width: 75%;
            margin: auto;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .about-container h1,
        .about-container h2 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .about-container p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
            margin-bottom: 30px;
        }
    </STYLE>
    </style>

    <head>

    <body>
        <!-- Navigation (same as index.html) -->

        <section class="section about-section">
            <div class="container about-container">
                <h1>About Batmul College</h1>
                <p>
                    <?php
                    // Set the content type to UTF-8
                include_once 'db.php';
                $about = [
                    'content' => ''
                ];  
                     $res = $conn->query("SELECT * FROM about_us LIMIT 1");
                        if ($res && $res->num_rows > 0) {
                            $about = $res->fetch_assoc();
                        }
                        echo $about['content'];
                    ?>
                    रायगढ़ जिला मुख्यालय से लगभग 12 किलोमीटर पूर्व में रायगढ़ विकास खंड के इस वनांचल ओड़िसा सीमावर्ती
                    आदिवासी , हरिजन बाहुल्य इलाके में उच्च – शिक्षा कि आवश्यक्ता महसूस कि गयी । विशेषकर पिछड़े आदिवासी
                    एवं हरिजन कन्यायों के उच्च शिक्षा को लेकर इस अंचल के विद्यानुरागियों के मन में वर्षों से स्वप्न पल
                    रहा था । आखिर इस स्वप्न को साकार करने के लिए स्व. श्री शशिधर पंडा सेवानिवृत्त प्राचार्य एवं जिला
                    शिक्षा अधिकारी की अध्यक्षता में बटमूल आश्रम शिक्षण समिति का गठन किया गया । तत्पश्चात सबके सामूहिक
                    प्रयास से ग्राम महापल्ली में मध्यप्रदेश शासन एवं गुरु घासीदास विश्वविद्यालय द्वारा बटमूल महाविद्यालय
                    को सम्बद्धता प्राप्त हुई । महाविद्यालय का शुभारम्भ प्रख्यात भूगोलविद डॉ बी पी पंडा ,प्राचार्य के कर
                    कमलों से हुई । महाविद्यालय के प्रथम प्राचार्य होने का श्रेय प्रो पी एन मेहर को मिला । सन १९९९ में
                    हिंदी , राजनीति शास्त्र एवं अर्थशास्त्र से स्नातक स्तर की कक्षाएं प्रारम्भ की गई । सन् २००० में
                    समाजशास्त्र एवं सन २००१ में भूगोल विषय प्रारम्भ किये गये । छात्रों की मांग से सन २००३-०४ राजनीति
                    शास्त्र में एम ए तथा सन २००४ – ०५ में भूगोल में स्नातकोत्तर कक्षाएं प्रारम्भ की गई । इस प्रकार सन
                    १९९९ में ४४ छात्र से प्रारम्भ कॉलेज में अब 647 छात्र – छात्राएं अध्ययनरत हैं । शिक्षा , दीक्षा ,
                    स्वावलम्बन एवं सेवा के उद्देश्य से प्रारम्भ किया गया यह महाविद्यालय अपने उद्देश्य में काफी सफल है |
                    
                </p>

                <h2>Who We Are</h2>
                <p>
                    Established with a vision to empower communities through education, Batmul College offers a diverse
                    range of programs designed to meet the evolving needs of society. Our dedicated faculty, modern
                    facilities, and student-centered approach create an environment where learning thrives.
                </p>

                <h2>What We Believe</h2>
                <p>
                    We believe education is a right, not a privilege. As a non-profit, our focus is on impact—not
                    income.
                    Every initiative we undertake is aimed at enriching lives, fostering innovation, and building a
                    better
                    tomorrow.
                </p>
            </div>
        </section>

        <!-- Footer -->
        <!-- Footer (same as index.html) -->

        <!-- Bootstrap JS Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Custom JS -->
        <?php include('footer.php'); ?>
    </body>

</html>