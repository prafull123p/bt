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

        .marq {

            --bs-blue: #0d6efd;
            --bs-indigo: #6610f2;
            --bs-purple: #6f42c1;
            --bs-pink: #d63384;
            --bs-red: #dc3545;
            --bs-orange: #fd7e14;
            --bs-yellow: #ffc107;
            --bs-green: #198754;
            --bs-teal: #20c997;
            --bs-cyan: #0dcaf0;
            --bs-white: #fff;
            --bs-gray: #6c757d;
            --bs-gray-dark: #343a40;
            --bs-primary: #0d6efd;
            --bs-secondary: #6c757d;
            --bs-success: #198754;
            --bs-info: #0dcaf0;
            --bs-warning: #ffc107;
            --bs-danger: #dc3545;
            --bs-light: #f8f9fa;
            --bs-dark: #212529;
            --bs-font-sans-serif: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            --bs-font-monospace: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            --bs-gradient: linear-gradient(180deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0));
            font-weight: 400;
            line-height: 1.5;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
            font-family: 'Lato', sans-serif;
            display: inline-block;
            overflow: hidden;
            text-align: initial;
            white-space: nowrap;
            box-sizing: border-box;
            font-size: 16px;
            color: white;
            padding: 0px 8px 8px 8px;
            background: linear-gradient(87deg, rgba(18, 18, 78, 1) 0%, rgba(97, 36, 182, 1) 20%, rgba(58, 58, 154, 1) 46%, rgba(47, 88, 198, 1) 72%, rgba(25, 25, 201, 1) 99%);
            background-size: 400% 400%;
            animation: gradient 10s ease infinite;
            background-position-x: 78.6361%;
            background-position-y: 50%;
        }

        .polyg {
            .box {
                --mask:
                    radial-gradient(100.62px at 50% calc(100% - 135px), #000 99%, #0000 101%) calc(50% - 90px) 0/180px 100%,
                    radial-gradient(100.62px at 50% calc(100% + 90px), #0000 99%, #000 101%) 50% calc(100% - 45px)/180px 100% repeat-x;
                -webkit-mask: var(--mask);
                mask: var(--mask);
            }
        }
    </STYLE>
</head>

<body>
    <section class="hero">

        <div class="hero-content">
            <h1>Welcome to Batmul College</h1>
            <p>Empowering minds, enriching communities — a non-profit institution dedicated to accessible, quality
                education for all.</p>
            <a href="about.php" class="cta-btn">Discover Our Mission</a>
        </div>
    </section>

</body><!-- hero.php -->